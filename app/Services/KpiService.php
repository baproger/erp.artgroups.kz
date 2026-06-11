<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Kpi;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class KpiService
{
    public function getKpiStats(Kpi $kpi, int $year, int $month, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? Carbon::now();
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dayOfMonth = ($asOf->year === $year && $asOf->month === $month)
            ? $asOf->day
            : ($asOf->gt(Carbon::createFromDate($year, $month, $daysInMonth)) ? $daysInMonth : 1);

        $planMonth = $kpi->getPlanForMonth($year, $month);
        $planToDate = $daysInMonth > 0 ? $planMonth * $dayOfMonth / $daysInMonth : 0;
        $fact = $kpi->getFactSumForMonth($year, $month);

        $pace = $this->calculatePace($kpi, $fact, $planToDate);
        $fulfillment = $planMonth > 0 ? $fact / $planMonth * 100 : null;
        $status = $this->getStatus($pace);

        return [
            'kpi'            => $kpi,
            'plan_month'     => $planMonth,
            'plan_to_date'   => $planToDate,
            'fact'           => $fact,
            'fulfillment'    => $fulfillment,
            'pace'           => $pace,
            'pace_pct'       => $pace !== null ? round($pace * 100, 1) : null,
            'status'         => $status,
            'day_of_month'   => $dayOfMonth,
            'days_in_month'  => $daysInMonth,
        ];
    }

    public function getDepartmentStats(Department $department, int $year, int $month, ?Carbon $asOf = null): array
    {
        $kpis = $department->activeKpis()->get();
        $kpiStats = $kpis->map(fn($kpi) => $this->getKpiStats($kpi, $year, $month, $asOf));

        $effectiveness = $this->calculateWeightedEffectiveness($kpiStats);
        $problematic = $kpiStats->filter(fn($s) => $s['status'] === 'critical')->count();

        return [
            'department'    => $department,
            'kpi_stats'     => $kpiStats,
            'effectiveness' => $effectiveness,
            'eff_pct'       => $effectiveness !== null ? round($effectiveness * 100, 1) : null,
            'problematic'   => $problematic,
            'pace'          => $effectiveness,
        ];
    }

    public function getCompanyStats(int $year, int $month, ?Carbon $asOf = null): array
    {
        $departments = Department::where('is_active', true)->orderBy('sort_order')->with('activeKpis')->get();
        $deptStats = $departments->map(fn($d) => $this->getDepartmentStats($d, $year, $month, $asOf));

        $allKpiStats = $deptStats->flatMap(fn($d) => $d['kpi_stats']);
        $effectiveness = $this->calculateWeightedEffectiveness($allKpiStats);

        return [
            'departments'   => $deptStats,
            'effectiveness' => $effectiveness,
            'eff_pct'       => $effectiveness !== null ? round($effectiveness * 100, 1) : null,
        ];
    }

    public function getBranchStats(Branch $branch, int $year, int $month, ?Carbon $asOf = null): array
    {
        $departments = Department::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with('activeKpis')
            ->get();

        $deptStats = $departments->map(fn($d) => $this->getDepartmentStats($d, $year, $month, $asOf));
        $allKpiStats = $deptStats->flatMap(fn($d) => $d['kpi_stats']);
        $effectiveness = $this->calculateWeightedEffectiveness($allKpiStats);

        return [
            'branch'        => $branch,
            'departments'   => $deptStats,
            'effectiveness' => $effectiveness,
            'eff_pct'       => $effectiveness !== null ? round($effectiveness * 100, 1) : null,
            'problematic'   => $deptStats->sum('problematic'),
            'dept_count'    => $departments->count(),
            'kpi_count'     => $allKpiStats->count(),
        ];
    }

    public function getAllBranchesStats(int $year, int $month): array
    {
        return Branch::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($b) => $this->getBranchStats($b, $year, $month))
            ->all();
    }

    public function getBranchDailyTrend(Branch $branch, int $year, int $month): array
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $today = Carbon::now();
        $maxDay = ($today->year === $year && $today->month === $month) ? $today->day : $daysInMonth;

        $trend = [];
        for ($day = 1; $day <= $maxDay; $day++) {
            $asOf  = Carbon::createFromDate($year, $month, $day)->endOfDay();
            $stats = $this->getBranchStats($branch, $year, $month, $asOf);
            $trend[] = ['day' => $day, 'date' => sprintf('%02d.%02d', $day, $month), 'eff' => $stats['eff_pct']];
        }

        return $trend;
    }

    public function getCompanyDailyTrend(int $year, int $month): array
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $today = Carbon::now();
        $maxDay = ($today->year === $year && $today->month === $month) ? $today->day : $daysInMonth;

        $trend = [];
        for ($day = 1; $day <= $maxDay; $day++) {
            $asOf = Carbon::createFromDate($year, $month, $day)->endOfDay();
            $stats = $this->getCompanyStats($year, $month, $asOf);
            $trend[] = [
                'day'  => $day,
                'date' => sprintf('%02d.%02d', $day, $month),
                'eff'  => $stats['eff_pct'],
            ];
        }

        return $trend;
    }

    private function calculatePace(Kpi $kpi, float $fact, float $planToDate): ?float
    {
        if ($planToDate == 0) {
            return null;
        }

        $rawPace = $fact / $planToDate;

        if ($kpi->direction === 'down') {
            $rawPace = $planToDate > 0 ? (2 - $rawPace) : null;
        }

        return $rawPace;
    }

    private function calculateWeightedEffectiveness(Collection $kpiStats): ?float
    {
        $total = 0.0;
        $totalWeight = 0;

        foreach ($kpiStats as $stat) {
            if ($stat['pace'] === null) {
                continue;
            }
            $weight = $stat['kpi']->weight;
            $score = min((float) $stat['pace'], 1.5);
            $total += $weight * $score;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $total / $totalWeight : null;
    }

    public function getStatus(mixed $pace): string
    {
        if ($pace === null) {
            return 'no_data';
        }

        $pct = $pace * 100;

        if ($pct < 80) {
            return 'critical';
        }
        if ($pct < 95) {
            return 'lag';
        }
        if ($pct <= 105) {
            return 'on_track';
        }

        return 'ahead';
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'critical'  => 'Критическое отставание',
            'lag'       => 'Отставание',
            'on_track'  => 'По плану',
            'ahead'     => 'Опережение',
            default     => 'Нет данных',
        };
    }

}
