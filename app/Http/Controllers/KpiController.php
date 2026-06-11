<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Kpi;
use App\Models\KpiFact;
use App\Models\KpiPlan;
use App\Models\User;
use App\Services\KpiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    public function __construct(private KpiService $kpiService) {}

    public function departmentView(Department $department, Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->canAccessDepartment($department)) {
            abort(403);
        }

        $now   = Carbon::now();
        $year  = (int) $request->get('year', $now->year);
        $month = (int) $request->get('month', $now->month);

        $deptStats = $this->kpiService->getDepartmentStats($department, $year, $month);

        return view('dashboard.department', compact('deptStats', 'year', 'month'));
    }

    public function storeFact(Request $request, Kpi $kpi)
    {
        /** @var User $user */
        $user = auth()->user();
        $maxDays = (int) config('kpi.fact_input_days', 7);

        $request->validate([
            'fact_date' => [
                'required', 'date',
                'before_or_equal:today',
                function (string $_attr, mixed $value, \Closure $fail) use ($maxDays) {
                    if (Carbon::parse($value)->lt(Carbon::now()->subDays($maxDays)->startOfDay())) {
                        $fail("Нельзя вводить данные старше {$maxDays} дней.");
                    }
                },
            ],
            'value' => 'required|numeric',
            'note'  => 'nullable|string|max:500',
        ]);

        if (! $user->canAccessDepartment($kpi->department)) {
            abort(403);
        }

        KpiFact::create([
            'kpi_id'     => $kpi->id,
            'fact_date'  => $request->fact_date,
            'value'      => $request->value,
            'created_by' => $user->id,
            'note'       => $request->note,
        ]);

        return back()->with('success', 'Факт успешно записан.');
    }

    public function factHistory(Kpi $kpi, Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->canAccessDepartment($kpi->department)) {
            abort(403);
        }

        $now   = Carbon::now();
        $year  = (int) $request->get('year', $now->year);
        $month = (int) $request->get('month', $now->month);

        $facts = KpiFact::with('author')
            ->where('kpi_id', $kpi->id)
            ->whereYear('fact_date', $year)
            ->whereMonth('fact_date', $month)
            ->orderBy('fact_date', 'desc')
            ->paginate(20);

        return view('kpi.history', compact('kpi', 'facts', 'year', 'month'));
    }

    public function updatePlan(Request $request, Kpi $kpi)
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->canManagePlans()) {
            abort(403);
        }

        $request->validate([
            'year'  => 'required|integer|min:2020|max:2099',
            'month' => 'required|integer|min:1|max:12',
            'value' => 'required|numeric|min:0',
        ]);

        KpiPlan::updateOrCreate(
            ['kpi_id' => $kpi->id, 'year' => $request->year, 'month' => $request->month],
            ['value' => $request->value, 'updated_by' => auth()->id()]
        );

        return back()->with('success', 'План обновлён.');
    }

    public function updateFact(Request $request, KpiFact $fact)
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->canManageUsers()) {
            abort(403, 'Редактировать факты может только администратор.');
        }

        $request->validate([
            'fact_date' => 'required|date',
            'value'     => 'required|numeric',
            'note'      => 'nullable|string|max:500',
        ]);

        $fact->update([
            'fact_date' => $request->fact_date,
            'value'     => $request->value,
            'note'      => $request->note,
        ]);

        return back()->with('success', 'Факт обновлён.');
    }

    public function destroyFact(KpiFact $fact)
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->canManageUsers()) {
            abort(403, 'Удалять факты может только администратор.');
        }

        $fact->delete();

        return back()->with('success', 'Запись удалена.');
    }

    public function updatePlans(Request $request, Department $department)
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->canManagePlans()) {
            abort(403);
        }

        if (! $user->canAccessDepartment($department)) {
            abort(403);
        }

        $request->validate([
            'year'     => 'required|integer|min:2020|max:2099',
            'month'    => 'required|integer|min:1|max:12',
            'plans'    => 'required|array',
            'plans.*'  => 'required|numeric|min:0',
        ]);

        foreach ($request->plans as $kpiId => $value) {
            $kpi = Kpi::where('id', $kpiId)
                ->where('department_id', $department->id)
                ->firstOrFail();

            KpiPlan::updateOrCreate(
                ['kpi_id' => $kpi->id, 'year' => $request->year, 'month' => $request->month],
                ['value' => $value, 'updated_by' => $user->id]
            );
        }

        return back()->with('success', 'Планы на ' . $request->month . '/' . $request->year . ' сохранены.');
    }
}
