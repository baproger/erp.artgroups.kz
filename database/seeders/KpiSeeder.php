<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Kpi;
use Illuminate\Database\Seeder;

class KpiSeeder extends Seeder
{
    public function run(): void
    {
        $kpisByDept = [
            'finance' => [
                ['slug' => 'vyruchka',             'name' => 'Выручка',                   'unit' => 'тнг',  'direction' => 'up',   'weight' => 5],
                ['slug' => 'valovaya-pribyl',       'name' => 'Валовая прибыль',            'unit' => 'тнг',  'direction' => 'up',   'weight' => 5],
                ['slug' => 'pogashennaya-debitorka','name' => 'Погашенная дебиторка',       'unit' => 'тнг',  'direction' => 'up',   'weight' => 4],
                ['slug' => 'operatsionnye-rashody', 'name' => 'Операционные расходы',       'unit' => 'тнг',  'direction' => 'down', 'weight' => 3],
                ['slug' => 'chisty-denezhny-potok', 'name' => 'Чистый денежный поток',      'unit' => 'тнг',  'direction' => 'up',   'weight' => 4],
            ],
            'sales' => [
                ['slug' => 'kolichestvo-dogovorov', 'name' => 'Количество договоров',       'unit' => 'шт.',  'direction' => 'up',   'weight' => 5],
                ['slug' => 'vyruchka-prodazh',      'name' => 'Выручка продаж',             'unit' => 'тнг',  'direction' => 'up',   'weight' => 5],
                ['slug' => 'sredniy-chek',          'name' => 'Средний чек',                'unit' => 'тнг',  'direction' => 'up',   'weight' => 4],
                ['slug' => 'konversiya-lid-dogovor','name' => 'Конверсия лид→договор',      'unit' => '%',    'direction' => 'up',   'weight' => 4],
                ['slug' => 'followup-zvonki',       'name' => 'Follow-up звонки',           'unit' => 'шт.',  'direction' => 'up',   'weight' => 3],
            ],
            'marketing' => [
                ['slug' => 'kolichestvo-lidov',     'name' => 'Количество лидов',           'unit' => 'шт.',  'direction' => 'up',   'weight' => 5],
                ['slug' => 'stoimost-lida',         'name' => 'Стоимость лида',             'unit' => 'тнг',  'direction' => 'down', 'weight' => 4],
                ['slug' => 'konversiya-lid-formy',  'name' => 'Конверсия лид-формы',        'unit' => '%',    'direction' => 'up',   'weight' => 4],
                ['slug' => 'stoimost-klienta',      'name' => 'Стоимость клиента',          'unit' => 'тнг',  'direction' => 'down', 'weight' => 4],
                ['slug' => 'organicheskiy-lid',     'name' => 'Органический лид',           'unit' => 'шт.',  'direction' => 'up',   'weight' => 3],
            ],
            'production' => [
                ['slug' => 'gotovye-zakazy',        'name' => 'Готовые заказы',             'unit' => 'шт.',  'direction' => 'up',   'weight' => 5],
                ['slug' => 'stoimost-proizvodstva', 'name' => 'Стоимость производства',     'unit' => 'тнг',  'direction' => 'down', 'weight' => 4],
                ['slug' => 'svoevremen-sdacha',     'name' => 'Своевременная сдача',        'unit' => '%',    'direction' => 'up',   'weight' => 5],
                ['slug' => 'dolya-braka',           'name' => 'Доля брака',                 'unit' => '%',    'direction' => 'down', 'weight' => 4],
                ['slug' => 'sluchai-peredelki',     'name' => 'Случаи переделки',           'unit' => 'шт.',  'direction' => 'down', 'weight' => 3],
            ],
            'surveyors' => [
                ['slug' => 'kolichestvo-zamerov',        'name' => 'Количество замеров',                   'unit' => 'шт.', 'direction' => 'up', 'weight' => 5],
                ['slug' => 'konversiya-zamer-dogovor',   'name' => 'Конверсия замер→договор',              'unit' => '%',   'direction' => 'up', 'weight' => 5],
                ['slug' => 'konversiya-online-dogovor',  'name' => 'Конверсия онлайн-замер→договор',       'unit' => '%',   'direction' => 'up', 'weight' => 4],
                ['slug' => 'konversiya-offline-dogovor', 'name' => 'Конверсия офлайн-замер→договор',       'unit' => '%',   'direction' => 'up', 'weight' => 4],
                ['slug' => 'svoevremen-zamery',          'name' => 'Своевременно выполненные замеры',      'unit' => '%',   'direction' => 'up', 'weight' => 3],
            ],
        ];

        foreach ($kpisByDept as $slug => $kpis) {
            $dept = Department::where('slug', $slug)->first();
            if (! $dept) continue;

            foreach ($kpis as $i => $kpi) {
                Kpi::updateOrCreate(
                    ['department_id' => $dept->id, 'slug' => $kpi['slug']],
                    array_merge($kpi, ['department_id' => $dept->id, 'sort_order' => $i + 1, 'is_active' => true])
                );
            }
        }
    }
}