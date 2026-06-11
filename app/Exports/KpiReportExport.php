<?php

namespace App\Exports;

use App\Services\KpiService;
use App\Services\RecommendationService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KpiReportExport implements WithMultipleSheets
{
    public function __construct(
        private int $year,
        private int $month,
        private KpiService $kpiService,
        private RecommendationService $recService,
    ) {}

    public function sheets(): array
    {
        $companyStats = $this->kpiService->getCompanyStats($this->year, $this->month);
        $trend = $this->kpiService->getCompanyDailyTrend($this->year, $this->month);
        $recommendations = $this->recService->getActive();

        return [
            new Sheets\TrendSheet($trend, $this->year, $this->month),
            new Sheets\DepartmentsSheet($companyStats),
            new Sheets\AllKpisSheet($companyStats, $this->kpiService),
            new Sheets\CriticalKpisSheet($companyStats, $this->kpiService),
            new Sheets\RecommendationsSheet($recommendations),
        ];
    }
}
