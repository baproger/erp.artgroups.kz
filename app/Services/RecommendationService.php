<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Kpi;
use App\Models\KpiFact;
use App\Models\Recommendation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public function __construct(private KpiService $kpiService) {}

    public function generateForCurrentMonth(): void
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        $this->checkMissingFacts($year, $month, $now);
        $this->checkCriticalLag($year, $month, $now);
        $this->checkDepartmentDrop($year, $month, $now);
    }

    private function checkMissingFacts(int $year, int $month, Carbon $now): void
    {
        $cutoff = $now->copy()->subDays(3)->startOfDay();
        $kpis = Kpi::with('department')->where('is_active', true)->get();

        foreach ($kpis as $kpi) {
            $lastFact = KpiFact::where('kpi_id', $kpi->id)
                ->whereYear('fact_date', $year)
                ->whereMonth('fact_date', $month)
                ->latest('fact_date')
                ->value('fact_date');

            $daysSinceFact = $lastFact
                ? Carbon::parse($lastFact)->diffInDays($now)
                : $now->day;

            if ($daysSinceFact >= 3 && $now->day >= 3) {
                $fromDate = $lastFact
                    ? Carbon::parse($lastFact)->addDay()->format('d.m')
                    : Carbon::createFromDate($year, $month, 1)->format('d.m');
                $toDate = $now->format('d.m');

                $exists = Recommendation::where('type', 'missing_fact')
                    ->where('kpi_id', $kpi->id)
                    ->where('is_dismissed', false)
                    ->whereDate('created_at', '>=', $now->copy()->startOfDay())
                    ->exists();

                if (! $exists) {
                    Recommendation::create([
                        'type'          => 'missing_fact',
                        'department_id' => $kpi->department_id,
                        'kpi_id'        => $kpi->id,
                        'message'       => "Отдел «{$kpi->department->name}» не заполнил факт по «{$kpi->name}» за {$fromDate}–{$toDate}",
                        'meta'          => ['days' => $daysSinceFact, 'from' => $fromDate, 'to' => $toDate],
                    ]);
                }
            }
        }
    }

    private function checkCriticalLag(int $year, int $month, Carbon $now): void
    {
        $kpis = Kpi::with('department')->where('is_active', true)->get();

        foreach ($kpis as $kpi) {
            $stats = $this->kpiService->getKpiStats($kpi, $year, $month, $now);

            if ($stats['status'] === 'critical' && $stats['plan_to_date'] > 0) {
                $exists = Recommendation::where('type', 'critical_lag')
                    ->where('kpi_id', $kpi->id)
                    ->where('is_dismissed', false)
                    ->whereDate('created_at', '>=', $now->copy()->startOfDay())
                    ->exists();

                if (! $exists) {
                    Recommendation::create([
                        'type'          => 'critical_lag',
                        'department_id' => $kpi->department_id,
                        'kpi_id'        => $kpi->id,
                        'message'       => "KPI «{$kpi->name}» ({$kpi->department->name}) – критическое отставание (темп {$stats['pace_pct']}%)",
                        'meta'          => ['pace_pct' => $stats['pace_pct']],
                    ]);
                }
            }
        }
    }

    private function checkDepartmentDrop(int $year, int $month, Carbon $now): void
    {
        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear  = $month === 1 ? $year - 1 : $year;

        $departments = Department::where('is_active', true)->get();

        foreach ($departments as $dept) {
            $currentStats = $this->kpiService->getDepartmentStats($dept, $year, $month, $now);
            $prevStats    = $this->kpiService->getDepartmentStats($dept, $prevYear, $prevMonth);

            if ($currentStats['effectiveness'] === null || $prevStats['effectiveness'] === null) {
                continue;
            }

            $drop = ($prevStats['effectiveness'] - $currentStats['effectiveness']) / $prevStats['effectiveness'];

            if ($drop >= 0.15) {
                $dropPct = round($drop * 100, 1);
                $exists = Recommendation::where('type', 'department_drop')
                    ->where('department_id', $dept->id)
                    ->where('is_dismissed', false)
                    ->whereDate('created_at', '>=', $now->copy()->startOfDay())
                    ->exists();

                if (! $exists) {
                    Recommendation::create([
                        'type'          => 'department_drop',
                        'department_id' => $dept->id,
                        'message'       => "Эффективность отдела «{$dept->name}» снизилась на {$dropPct}% к прошлому месяцу – проведите разбор",
                        'meta'          => ['drop_pct' => $dropPct],
                    ]);
                }
            }
        }
    }

    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Recommendation::with(['department', 'kpi'])
            ->where('is_dismissed', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function dismiss(Recommendation $rec, int $userId): void
    {
        $rec->update([
            'is_dismissed'  => true,
            'dismissed_by'  => $userId,
            'dismissed_at'  => now(),
        ]);
    }
}
