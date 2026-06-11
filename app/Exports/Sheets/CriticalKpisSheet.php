<?php

namespace App\Exports\Sheets;

use App\Services\KpiService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CriticalKpisSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $companyStats, private KpiService $kpiService) {}

    public function title(): string
    {
        return 'Критические KPI';
    }

    public function headings(): array
    {
        return ['Отдел', 'KPI', 'Факт', 'Темп (%)', 'Статус'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->companyStats['departments'] as $deptStat) {
            foreach ($deptStat['kpi_stats'] as $s) {
                if (in_array($s['status'], ['critical', 'lag'])) {
                    $rows[] = [
                        $deptStat['department']->name,
                        $s['kpi']->name,
                        $s['fact'],
                        $s['pace_pct'],
                        $this->kpiService->getStatusLabel($s['status']),
                    ];
                }
            }
        }
        return $rows;
    }
}
