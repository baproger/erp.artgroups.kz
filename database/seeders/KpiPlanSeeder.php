<?php

namespace Database\Seeders;

use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class KpiPlanSeeder extends Seeder
{
    public function run(): void
    {
        $ceo   = User::where('role', 'ceo')->first();
        $year  = now()->year;
        $month = now()->month;

        $plans = [
            // Finance (Алматы)
            'vyruchka'              => 60_000_000,
            'valovaya-pribyl'       => 18_000_000,
            'pogashennaya-debitorka'=> 8_000_000,
            'operatsionnye-rashody' => 15_000_000,
            'chisty-denezhny-potok' => 10_000_000,
            // Sales
            'kolichestvo-dogovorov' => 40,
            'vyruchka-prodazh'      => 50_000_000,
            'sredniy-chek'          => 1_250_000,
            'konversiya-lid-dogovor'=> 30,
            'followup-zvonki'       => 500,
            // Marketing
            'kolichestvo-lidov'     => 200,
            'stoimost-lida'         => 5_000,
            'konversiya-lid-formy'  => 8,
            'stoimost-klienta'      => 25_000,
            'organicheskiy-lid'     => 80,
            // Production
            'gotovye-zakazy'        => 35,
            'stoimost-proizvodstva' => 8_000_000,
            'svoevremen-sdacha'     => 95,
            'dolya-braka'           => 2,
            'sluchai-peredelki'     => 3,
            // Surveyors
            'kolichestvo-zamerov'        => 100,
            'konversiya-zamer-dogovor'   => 40,
            'konversiya-online-dogovor'  => 35,
            'konversiya-offline-dogovor' => 45,
            'svoevremen-zamery'          => 90,
        ];

        $kpis = Kpi::all();
        foreach ($kpis as $kpi) {
            $value = $plans[$kpi->slug] ?? 100;
            KpiPlan::updateOrCreate(
                ['kpi_id' => $kpi->id, 'year' => $year, 'month' => $month],
                ['value' => $value, 'updated_by' => $ceo?->id]
            );
        }
    }
}
