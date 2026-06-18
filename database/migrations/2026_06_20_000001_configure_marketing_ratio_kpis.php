<?php

use App\Models\Department;
use App\Models\Kpi;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Все отделы «Маркетинг» (во всех филиалах)
        $departments = Department::where('name', 'Маркетинг')->get();

        foreach ($departments as $dept) {
            $leads   = Kpi::where('department_id', $dept->id)->where('name', 'Количество лидов')->first();
            if (! $leads) {
                continue; // не маркетинговый набор — пропускаем
            }

            // Определяем slug-конвенцию отдела по слагу лидов: с '-b' или без
            $suffix = str_ends_with($leads->slug, '-b') ? '-b' : '';

            $spendSlug   = 'rashod-reklama' . $suffix;
            $clientsSlug = 'novye-klienty' . $suffix;

            // 1) Базовые метрики (вводятся вручную, суммируются)
            $spend = Kpi::firstOrCreate(
                ['department_id' => $dept->id, 'slug' => $spendSlug],
                [
                    'name'        => 'Расход на рекламу',
                    'unit'        => 'тнг',
                    'direction'   => 'down',
                    'aggregation' => 'sum',
                    'weight'      => 3,
                    'sort_order'  => 3,
                    'is_active'   => true,
                ]
            );

            $clients = Kpi::firstOrCreate(
                ['department_id' => $dept->id, 'slug' => $clientsSlug],
                [
                    'name'        => 'Новые клиенты',
                    'unit'        => 'шт.',
                    'direction'   => 'up',
                    'aggregation' => 'sum',
                    'weight'      => 5,
                    'sort_order'  => 4,
                    'is_active'   => true,
                ]
            );

            // 2) Расчётные метрики (пересчитываются из базовых, не вводятся)
            $this->makeRatio($dept->id, 'Стоимость лида',        $spendSlug,   $leads->slug, 1);   // CPL  = расход / лиды
            $this->makeRatio($dept->id, 'Конверсия лид-формы',   $clientsSlug, $leads->slug, 100); // %    = клиенты / лиды * 100
            $this->makeRatio($dept->id, 'Стоимость клиента',     $spendSlug,   $clientsSlug, 1);   // CAC  = расход / клиенты
        }
    }

    private function makeRatio(int $deptId, string $name, string $numSlug, string $denSlug, int $factor): void
    {
        Kpi::where('department_id', $deptId)->where('name', $name)->update([
            'aggregation'      => 'ratio',
            'numerator_slug'   => $numSlug,
            'denominator_slug' => $denSlug,
            'factor'           => $factor,
        ]);
    }

    public function down(): void
    {
        $departments = Department::where('name', 'Маркетинг')->pluck('id');

        // Вернуть расчётные обратно в 'sum'
        Kpi::whereIn('department_id', $departments)
            ->whereIn('name', ['Стоимость лида', 'Конверсия лид-формы', 'Стоимость клиента'])
            ->update([
                'aggregation'      => 'sum',
                'numerator_slug'   => null,
                'denominator_slug' => null,
                'factor'           => 1,
            ]);

        // Базовые метрики не удаляем — в них могут быть введённые факты.
    }
};
