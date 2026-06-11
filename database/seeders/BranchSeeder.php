<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Models\KpiFact;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 3 branches ──────────────────────────────────────────
        $almaty = Branch::firstOrCreate(['name' => 'Алматы'], [
            'city' => 'Алматы', 'color' => 'emerald', 'sort_order' => 1, 'is_active' => true,
        ]);
        $astana = Branch::firstOrCreate(['name' => 'Астана'], [
            'city' => 'Астана', 'color' => 'blue', 'sort_order' => 2, 'is_active' => true,
        ]);
        $shymkent = Branch::firstOrCreate(['name' => 'Шымкент'], [
            'city' => 'Шымкент', 'color' => 'violet', 'sort_order' => 3, 'is_active' => true,
        ]);

        // Assign existing departments & users to Алматы
        Department::whereIn('slug', ['finance','sales','marketing','production','surveyors'])
            ->update(['branch_id' => $almaty->id]);
        User::whereIn('role', ['finance','sales','marketing','production','surveyors'])
            ->update(['branch_id' => $almaty->id]);
        User::where('role', 'commercial_director')->update(['branch_id' => $almaty->id]);

        // ─── Астана ───────────────────────────────────────────────
        $this->seedBranch($astana, [
            ['name' => 'Финансы',      'slug' => 'finance-astana',    'sort_order' => 1, 'role' => 'finance'],
            ['name' => 'Продажи',      'slug' => 'sales-astana',      'sort_order' => 2, 'role' => 'sales'],
            ['name' => 'Маркетинг',    'slug' => 'marketing-astana',  'sort_order' => 3, 'role' => 'marketing'],
            ['name' => 'Производство', 'slug' => 'production-astana', 'sort_order' => 4, 'role' => 'production'],
            ['name' => 'Замерщики',    'slug' => 'surveyors-astana',  'sort_order' => 5, 'role' => 'surveyors'],
        ], 'astana', 0.91);

        // ─── Шымкент ─────────────────────────────────────────────
        $this->seedBranch($shymkent, [
            ['name' => 'Финансы',      'slug' => 'finance-shymkent',    'sort_order' => 1, 'role' => 'finance'],
            ['name' => 'Продажи',      'slug' => 'sales-shymkent',      'sort_order' => 2, 'role' => 'sales'],
            ['name' => 'Маркетинг',    'slug' => 'marketing-shymkent',  'sort_order' => 3, 'role' => 'marketing'],
            ['name' => 'Производство', 'slug' => 'production-shymkent', 'sort_order' => 4, 'role' => 'production'],
            ['name' => 'Замерщики',    'slug' => 'surveyors-shymkent',  'sort_order' => 5, 'role' => 'surveyors'],
        ], 'shymkent', 0.78);

        // ─── Commercial directors ─────────────────────────────────
        User::firstOrCreate(['email' => 'director.astana@artgroups.kz'], [
            'name'      => 'Коммерческий Директор (Астана)',
            'password'  => Hash::make('password123'),
            'role'      => 'commercial_director',
            'branch_id' => $astana->id,
            'is_active' => true,
        ]);
        User::firstOrCreate(['email' => 'director.shymkent@artgroups.kz'], [
            'name'      => 'Коммерческий Директор (Шымкент)',
            'password'  => Hash::make('password123'),
            'role'      => 'commercial_director',
            'branch_id' => $shymkent->id,
            'is_active' => true,
        ]);
    }

    private function kpiTemplates(): array
    {
        return [
            'finance' => [
                ['slug' => 'vyruchka-b',             'name' => 'Выручка',                   'unit' => 'тнг', 'direction' => 'up',   'weight' => 5, 'plan_almaty' => 45_000_000, 'plan_astana' => 40_000_000, 'plan_shymkent' => 35_000_000],
                ['slug' => 'valovaya-pribyl-b',       'name' => 'Валовая прибыль',            'unit' => 'тнг', 'direction' => 'up',   'weight' => 5, 'plan_almaty' => 14_000_000, 'plan_astana' => 12_000_000, 'plan_shymkent' => 10_000_000],
                ['slug' => 'pogashennaya-debitorka-b','name' => 'Погашенная дебиторка',       'unit' => 'тнг', 'direction' => 'up',   'weight' => 4, 'plan_almaty' =>  6_000_000, 'plan_astana' =>  5_000_000, 'plan_shymkent' =>  4_000_000],
                ['slug' => 'operatsionnye-rashody-b', 'name' => 'Операционные расходы',       'unit' => 'тнг', 'direction' => 'down', 'weight' => 3, 'plan_almaty' =>  9_000_000, 'plan_astana' =>  8_000_000, 'plan_shymkent' =>  7_000_000],
                ['slug' => 'chisty-denezhny-potok-b', 'name' => 'Чистый денежный поток',      'unit' => 'тнг', 'direction' => 'up',   'weight' => 4, 'plan_almaty' =>  8_000_000, 'plan_astana' =>  7_000_000, 'plan_shymkent' =>  6_000_000],
            ],
            'sales' => [
                ['slug' => 'kolichestvo-dogovorov-b', 'name' => 'Количество договоров',       'unit' => 'шт.', 'direction' => 'up', 'weight' => 5, 'plan_almaty' => 30, 'plan_astana' => 25, 'plan_shymkent' => 20],
                ['slug' => 'vyruchka-prodazh-b',      'name' => 'Выручка продаж',             'unit' => 'тнг', 'direction' => 'up', 'weight' => 5, 'plan_almaty' => 40_000_000, 'plan_astana' => 35_000_000, 'plan_shymkent' => 30_000_000],
                ['slug' => 'sredniy-chek-b',          'name' => 'Средний чек',                'unit' => 'тнг', 'direction' => 'up', 'weight' => 4, 'plan_almaty' => 1_200_000, 'plan_astana' => 1_100_000, 'plan_shymkent' => 1_000_000],
                ['slug' => 'konversiya-lid-dogovor-b','name' => 'Конверсия лид→договор',      'unit' => '%',   'direction' => 'up', 'weight' => 4, 'plan_almaty' => 30, 'plan_astana' => 28, 'plan_shymkent' => 25],
                ['slug' => 'followup-zvonki-b',       'name' => 'Follow-up звонки',           'unit' => 'шт.', 'direction' => 'up', 'weight' => 3, 'plan_almaty' => 400, 'plan_astana' => 350, 'plan_shymkent' => 300],
            ],
            'marketing' => [
                ['slug' => 'kolichestvo-lidov-b',     'name' => 'Количество лидов',           'unit' => 'шт.', 'direction' => 'up',   'weight' => 5, 'plan_almaty' => 150, 'plan_astana' => 130, 'plan_shymkent' => 100],
                ['slug' => 'stoimost-lida-b',         'name' => 'Стоимость лида',             'unit' => 'тнг', 'direction' => 'down', 'weight' => 4, 'plan_almaty' => 5000, 'plan_astana' => 5500, 'plan_shymkent' => 6000],
                ['slug' => 'konversiya-lid-formy-b',  'name' => 'Конверсия лид-формы',        'unit' => '%',   'direction' => 'up',   'weight' => 4, 'plan_almaty' => 8, 'plan_astana' => 7, 'plan_shymkent' => 6],
                ['slug' => 'stoimost-klienta-b',      'name' => 'Стоимость клиента',          'unit' => 'тнг', 'direction' => 'down', 'weight' => 4, 'plan_almaty' => 25000, 'plan_astana' => 27000, 'plan_shymkent' => 30000],
                ['slug' => 'organicheskiy-lid-b',     'name' => 'Органический лид',           'unit' => 'шт.', 'direction' => 'up',   'weight' => 3, 'plan_almaty' => 60, 'plan_astana' => 50, 'plan_shymkent' => 40],
            ],
            'production' => [
                ['slug' => 'gotovye-zakazy-b',        'name' => 'Готовые заказы',             'unit' => 'шт.', 'direction' => 'up',   'weight' => 5, 'plan_almaty' => 30, 'plan_astana' => 25, 'plan_shymkent' => 28],
                ['slug' => 'stoimost-proizvodstva-b', 'name' => 'Стоимость производства',     'unit' => 'тнг', 'direction' => 'down', 'weight' => 4, 'plan_almaty' => 7_000_000, 'plan_astana' => 6_000_000, 'plan_shymkent' => 6_500_000],
                ['slug' => 'svoevremen-sdacha-b',     'name' => 'Своевременная сдача',        'unit' => '%',   'direction' => 'up',   'weight' => 5, 'plan_almaty' => 95, 'plan_astana' => 93, 'plan_shymkent' => 92],
                ['slug' => 'dolya-braka-b',           'name' => 'Доля брака',                 'unit' => '%',   'direction' => 'down', 'weight' => 4, 'plan_almaty' => 2, 'plan_astana' => 2.5, 'plan_shymkent' => 2.5],
                ['slug' => 'sluchai-peredelki-b',     'name' => 'Случаи переделки',           'unit' => 'шт.', 'direction' => 'down', 'weight' => 3, 'plan_almaty' => 3, 'plan_astana' => 4, 'plan_shymkent' => 4],
            ],
            'surveyors' => [
                ['slug' => 'kolichestvo-zamerov-b',        'name' => 'Количество замеров',                  'unit' => 'шт.', 'direction' => 'up', 'weight' => 5, 'plan_almaty' => 90, 'plan_astana' => 80, 'plan_shymkent' => 70],
                ['slug' => 'konversiya-zamer-dogovor-b',   'name' => 'Конверсия замер→договор',             'unit' => '%',   'direction' => 'up', 'weight' => 5, 'plan_almaty' => 40, 'plan_astana' => 38, 'plan_shymkent' => 35],
                ['slug' => 'konversiya-online-dogovor-b',  'name' => 'Конверсия онлайн-замер→договор',      'unit' => '%',   'direction' => 'up', 'weight' => 4, 'plan_almaty' => 35, 'plan_astana' => 32, 'plan_shymkent' => 30],
                ['slug' => 'konversiya-offline-dogovor-b', 'name' => 'Конверсия офлайн-замер→договор',      'unit' => '%',   'direction' => 'up', 'weight' => 4, 'plan_almaty' => 45, 'plan_astana' => 42, 'plan_shymkent' => 40],
                ['slug' => 'svoevremen-zamery-b',          'name' => 'Своевременно выполненные замеры',     'unit' => '%',   'direction' => 'up', 'weight' => 3, 'plan_almaty' => 90, 'plan_astana' => 88, 'plan_shymkent' => 85],
            ],
        ];
    }

    private function seedBranch(Branch $branch, array $depts, string $suffix, float $pace): void
    {
        $templates = $this->kpiTemplates();
        $planKey   = "plan_{$suffix}";
        $now       = Carbon::now();
        $year      = $now->year;
        $month     = $now->month;

        foreach ($depts as $deptData) {
            $dept = Department::firstOrCreate(['slug' => $deptData['slug']], [
                'name'       => $deptData['name'],
                'slug'       => $deptData['slug'],
                'sort_order' => $deptData['sort_order'],
                'branch_id'  => $branch->id,
                'is_active'  => true,
            ]);

            $email = "{$deptData['role']}.{$suffix}@artgroups.kz";
            $user  = User::firstOrCreate(['email' => $email], [
                'name'          => "{$deptData['name']} ({$branch->city})",
                'password'      => Hash::make('password123'),
                'role'          => $deptData['role'],
                'department_id' => $dept->id,
                'branch_id'     => $branch->id,
                'is_active'     => true,
            ]);

            $kpiList = $templates[$deptData['role']] ?? [];
            foreach ($kpiList as $i => $tpl) {
                $kpi = Kpi::firstOrCreate(
                    ['department_id' => $dept->id, 'slug' => $tpl['slug']],
                    [
                        'name'          => $tpl['name'],
                        'unit'          => $tpl['unit'],
                        'direction'     => $tpl['direction'],
                        'weight'        => $tpl['weight'],
                        'department_id' => $dept->id,
                        'slug'          => $tpl['slug'],
                        'sort_order'    => $i + 1,
                        'is_active'     => true,
                    ]
                );

                $planValue = $tpl[$planKey] ?? $tpl['plan_almaty'];
                KpiPlan::firstOrCreate(
                    ['kpi_id' => $kpi->id, 'year' => $year, 'month' => $month],
                    ['value' => $planValue, 'updated_by' => 1]
                );

                $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;
                $dailyPlan   = $planValue / $daysInMonth;

                for ($d = max(1, $now->day - 9); $d <= $now->day; $d++) {
                    $factDate = Carbon::createFromDate($year, $month, $d)->format('Y-m-d');
                    if (! KpiFact::where('kpi_id', $kpi->id)->where('fact_date', $factDate)->exists()) {
                        KpiFact::create([
                            'kpi_id'     => $kpi->id,
                            'fact_date'  => $factDate,
                            'value'      => max(0, round($dailyPlan * $pace * (0.9 + mt_rand(0, 20) / 100), 2)),
                            'created_by' => $user->id,
                        ]);
                    }
                }
            }
        }
    }
}
