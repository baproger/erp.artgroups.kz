<?php

namespace Database\Seeders;

use App\Models\Kpi;
use App\Models\KpiFact;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KpiFactSeeder extends Seeder
{
    public function run(): void
    {
        $year  = now()->year;
        $month = now()->month;
        $today = now()->day;

        $users = User::where('is_active', true)->pluck('id')->toArray();
        $kpis  = Kpi::all();

        foreach ($kpis as $kpi) {
            $plan = KpiPlanValue($kpi, $year, $month);

            for ($day = 1; $day <= $today; $day++) {
                // Skip some days randomly to simulate missing data
                if ($day < $today - 3 && rand(0, 10) < 2) {
                    continue;
                }

                $dailyTarget = $plan / Carbon::createFromDate($year, $month, 1)->daysInMonth;
                $variance = rand(70, 140) / 100;
                $value = round($dailyTarget * $variance, 2);

                if ($kpi->direction === 'down') {
                    $value = round($dailyTarget * (rand(60, 110) / 100), 2);
                }

                KpiFact::firstOrCreate(
                    ['kpi_id' => $kpi->id, 'fact_date' => Carbon::createFromDate($year, $month, $day)->toDateString()],
                    [
                        'value'      => max(0, $value),
                        'created_by' => $users[array_rand($users)] ?? null,
                    ]
                );
            }
        }
    }
}

function KpiPlanValue(Kpi $kpi, int $year, int $month): float
{
    $plan = \App\Models\KpiPlan::where('kpi_id', $kpi->id)->where('year', $year)->where('month', $month)->first();
    return $plan ? (float) $plan->value : 100.0;
}
