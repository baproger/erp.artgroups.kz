<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Kpi;
use App\Models\KpiFact;
use App\Models\KpiPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:ceo');
    }

    public function users(Request $request)
    {
        $branches    = Branch::where('is_active', true)->orderBy('sort_order')->get();
        $branchId    = $request->get('branch'); // null = all
        $departments = Department::where('is_active', true)->orderBy('branch_id')->orderBy('sort_order')->get();
        $roles       = User::ROLES;

        $query = User::with('department', 'branch', 'accessibleBranches')->orderBy('is_active')->orderBy('name');

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        $users = $query->paginate(30)->withQueryString();

        $currentBranch = $branchId && $branchId !== 'all'
            ? $branches->firstWhere('id', $branchId)
            : null;

        return view('admin.users', compact('users', 'departments', 'roles', 'branches', 'currentBranch', 'branchId'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'                  => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'branch_id'             => 'nullable|exists:branches,id',
            'department_id'         => [
                'nullable',
                'exists:departments,id',
                // Department must belong to the selected branch
                function ($_attr, $value, $fail) use ($request) {
                    if ($value && $request->branch_id) {
                        $dept = Department::find($value);
                        if ($dept && (int) $dept->branch_id !== (int) $request->branch_id) {
                            $fail('Выбранный отдел не принадлежит указанному филиалу.');
                        }
                    }
                },
            ],
            'is_active'             => 'required|boolean',
            'password'              => 'nullable|min:8',
            'accessible_branches'   => 'nullable|array',
            'accessible_branches.*' => 'exists:branches,id',
        ]);

        $data = [
            'name'          => $request->name,
            'email'         => $request->email,
            'role'          => $request->role,
            'branch_id'     => $request->branch_id,
            'department_id' => $request->department_id,
            'is_active'     => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync multi-branch access (only for non-CEO)
        if ($user->role !== 'ceo') {
            $user->accessibleBranches()->sync($request->input('accessible_branches', []));
        }

        return back()->with('success', "Пользователь «{$user->name}» обновлён.");
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:8',
            'role'          => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'branch_id'     => 'nullable|exists:branches,id',
            'department_id' => [
                'nullable', 'exists:departments,id',
                function ($_attr, $value, $fail) use ($request) {
                    if ($value && $request->branch_id) {
                        $dept = Department::find($value);
                        if ($dept && (int) $dept->branch_id !== (int) $request->branch_id) {
                            $fail('Выбранный отдел не принадлежит указанному филиалу.');
                        }
                    }
                },
            ],
            'is_active'             => 'required|boolean',
            'accessible_branches'   => 'nullable|array',
            'accessible_branches.*' => 'exists:branches,id',
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => $request->role,
            'branch_id'     => $request->branch_id,
            'department_id' => $request->department_id,
            'is_active'     => $request->is_active,
        ]);

        if ($user->role !== 'ceo') {
            $user->accessibleBranches()->sync($request->input('accessible_branches', []));
        }

        return back()->with('success', "Пользователь «{$user->name}» создан.");
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Нельзя удалить свой аккаунт.');
        }

        $user->delete();
        return back()->with('success', "Пользователь удалён.");
    }

    public function storeBranch(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:branches,name',
            'city'  => 'required|string|max:100',
            'color' => 'required|in:emerald,blue,violet,purple,amber',
        ]);

        $maxOrder = Branch::max('sort_order') ?? 0;

        $branch = Branch::create([
            'name'       => $request->name,
            'city'       => $request->city,
            'color'      => $request->color,
            'sort_order' => $maxOrder + 1,
            'is_active'  => true,
        ]);

        $this->seedDefaultDepartments($branch);

        return back()->with('success', "Филиал «{$request->name}» создан. Стандартные 5 отделов и KPI добавлены автоматически.");
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:100', Rule::unique('branches', 'name')->ignore($branch->id)],
            'city'  => 'required|string|max:100',
            'color' => 'required|in:emerald,blue,violet,purple,amber',
        ]);

        $branch->update([
            'name'  => $request->name,
            'city'  => $request->city,
            'color' => $request->color,
        ]);

        return back()->with('success', "Филиал «{$branch->name}» обновлён.");
    }

    public function destroyBranch(Branch $branch)
    {
        $userCount = $branch->users()->count();
        if ($userCount > 0) {
            return back()->with('error', "Нельзя удалить филиал «{$branch->name}»: в нём {$userCount} пользователей. Сначала переназначьте или удалите их.");
        }

        $deptIds = $branch->departments()->pluck('id');
        $kpiIds  = Kpi::whereIn('department_id', $deptIds)->pluck('id');

        KpiFact::whereIn('kpi_id', $kpiIds)->delete();
        KpiPlan::whereIn('kpi_id', $kpiIds)->delete();
        Kpi::whereIn('id', $kpiIds)->delete();
        Department::where('branch_id', $branch->id)->delete();
        $branch->delete();

        return redirect()->route('dashboard')->with('success', "Филиал удалён.");
    }

    private function seedDefaultDepartments(Branch $branch): void
    {
        $suffix    = Str::slug($branch->city);
        $now       = Carbon::now();
        $year      = $now->year;
        $month     = $now->month;

        $depts = [
            ['name' => 'Финансы',      'sort' => 1, 'kpis' => [
                ['slug' => 'vyruchka-b',             'name' => 'Выручка',                 'unit' => 'тнг', 'dir' => 'up',   'w' => 5, 'plan' => 30_000_000],
                ['slug' => 'valovaya-pribyl-b',       'name' => 'Валовая прибыль',          'unit' => 'тнг', 'dir' => 'up',   'w' => 5, 'plan' => 9_000_000],
                ['slug' => 'pogashennaya-debitorka-b','name' => 'Погашенная дебиторка',     'unit' => 'тнг', 'dir' => 'up',   'w' => 4, 'plan' => 3_000_000],
                ['slug' => 'operatsionnye-rashody-b', 'name' => 'Операционные расходы',     'unit' => 'тнг', 'dir' => 'down', 'w' => 3, 'plan' => 6_000_000],
                ['slug' => 'chisty-denezhny-potok-b', 'name' => 'Чистый денежный поток',   'unit' => 'тнг', 'dir' => 'up',   'w' => 4, 'plan' => 5_000_000],
            ]],
            ['name' => 'Продажи',      'sort' => 2, 'kpis' => [
                ['slug' => 'kolichestvo-dogovorov-b', 'name' => 'Количество договоров',     'unit' => 'шт.', 'dir' => 'up', 'w' => 5, 'plan' => 15],
                ['slug' => 'vyruchka-prodazh-b',      'name' => 'Выручка продаж',           'unit' => 'тнг', 'dir' => 'up', 'w' => 5, 'plan' => 25_000_000],
                ['slug' => 'sredniy-chek-b',          'name' => 'Средний чек',              'unit' => 'тнг', 'dir' => 'up', 'w' => 4, 'plan' => 900_000],
                ['slug' => 'konversiya-lid-dogovor-b','name' => 'Конверсия лид→договор',    'unit' => '%',   'dir' => 'up', 'w' => 4, 'plan' => 22],
                ['slug' => 'followup-zvonki-b',       'name' => 'Follow-up звонки',         'unit' => 'шт.', 'dir' => 'up', 'w' => 3, 'plan' => 250],
            ]],
            ['name' => 'Маркетинг',    'sort' => 3, 'kpis' => [
                ['slug' => 'kolichestvo-lidov-b',    'name' => 'Количество лидов',       'unit' => 'шт.', 'dir' => 'up',   'w' => 5, 'plan' => 80],
                ['slug' => 'stoimost-lida-b',        'name' => 'Стоимость лида',         'unit' => 'тнг', 'dir' => 'down', 'w' => 4, 'plan' => 7000],
                ['slug' => 'konversiya-lid-formy-b', 'name' => 'Конверсия лид-формы',    'unit' => '%',   'dir' => 'up',   'w' => 4, 'plan' => 5],
                ['slug' => 'stoimost-klienta-b',     'name' => 'Стоимость клиента',      'unit' => 'тнг', 'dir' => 'down', 'w' => 4, 'plan' => 35_000],
                ['slug' => 'organicheskiy-lid-b',    'name' => 'Органический лид',       'unit' => 'шт.', 'dir' => 'up',   'w' => 3, 'plan' => 30],
            ]],
            ['name' => 'Производство', 'sort' => 4, 'kpis' => [
                ['slug' => 'gotovye-zakazy-b',        'name' => 'Готовые заказы',          'unit' => 'шт.', 'dir' => 'up',   'w' => 5, 'plan' => 20],
                ['slug' => 'stoimost-proizvodstva-b', 'name' => 'Стоимость производства',  'unit' => 'тнг', 'dir' => 'down', 'w' => 4, 'plan' => 5_000_000],
                ['slug' => 'svoevremen-sdacha-b',     'name' => 'Своевременная сдача',     'unit' => '%',   'dir' => 'up',   'w' => 5, 'plan' => 90],
                ['slug' => 'dolya-braka-b',           'name' => 'Доля брака',              'unit' => '%',   'dir' => 'down', 'w' => 4, 'plan' => 3],
                ['slug' => 'sluchai-peredelki-b',     'name' => 'Случаи переделки',        'unit' => 'шт.', 'dir' => 'down', 'w' => 3, 'plan' => 5],
            ]],
            ['name' => 'Замерщики',    'sort' => 5, 'kpis' => [
                ['slug' => 'kolichestvo-zamerov-b',        'name' => 'Количество замеров',             'unit' => 'шт.', 'dir' => 'up', 'w' => 5, 'plan' => 60],
                ['slug' => 'konversiya-zamer-dogovor-b',   'name' => 'Конверсия замер→договор',        'unit' => '%',   'dir' => 'up', 'w' => 5, 'plan' => 32],
                ['slug' => 'konversiya-online-dogovor-b',  'name' => 'Конверсия онлайн-замер→договор', 'unit' => '%',   'dir' => 'up', 'w' => 4, 'plan' => 28],
                ['slug' => 'konversiya-offline-dogovor-b', 'name' => 'Конверсия офлайн-замер→договор', 'unit' => '%',   'dir' => 'up', 'w' => 4, 'plan' => 38],
                ['slug' => 'svoevremen-zamery-b',          'name' => 'Своевременные замеры',           'unit' => '%',   'dir' => 'up', 'w' => 3, 'plan' => 82],
            ]],
        ];

        foreach ($depts as $deptData) {
            $deptSlug = $deptData['name'] === 'Финансы'      ? "finance-{$suffix}"
                      : ($deptData['name'] === 'Продажи'     ? "sales-{$suffix}"
                      : ($deptData['name'] === 'Маркетинг'   ? "marketing-{$suffix}"
                      : ($deptData['name'] === 'Производство' ? "production-{$suffix}"
                      : "surveyors-{$suffix}")));

            $dept = Department::firstOrCreate(['slug' => $deptSlug], [
                'name'       => $deptData['name'],
                'slug'       => $deptSlug,
                'sort_order' => $deptData['sort'],
                'branch_id'  => $branch->id,
                'is_active'  => true,
            ]);

            foreach ($deptData['kpis'] as $i => $kpiData) {
                $kpi = Kpi::firstOrCreate(
                    ['department_id' => $dept->id, 'slug' => $kpiData['slug']],
                    [
                        'name'          => $kpiData['name'],
                        'unit'          => $kpiData['unit'],
                        'direction'     => $kpiData['dir'],
                        'weight'        => $kpiData['w'],
                        'department_id' => $dept->id,
                        'slug'          => $kpiData['slug'],
                        'sort_order'    => $i + 1,
                        'is_active'     => true,
                    ]
                );

                KpiPlan::firstOrCreate(
                    ['kpi_id' => $kpi->id, 'year' => $year, 'month' => $month],
                    ['value' => $kpiData['plan'], 'updated_by' => auth()->id() ?? 1]
                );
            }
        }
    }
}
