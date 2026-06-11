<?php

namespace App\Exports\Sheets;

use App\Services\KpiService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AllKpisSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $companyStats, private KpiService $kpiService) {}

    public function title(): string
    {
        return 'Все KPI';
    }

    public function headings(): array
    {
        return ['Отдел', 'KPI', 'Единица', 'План (месяц)', 'План (сегодня)', 'Факт', 'Темп (%)', 'Статус'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->companyStats['departments'] as $deptStat) {
            foreach ($deptStat['kpi_stats'] as $s) {
                $rows[] = [
                    $deptStat['department']->name,
                    $s['kpi']->name,
                    $s['kpi']->unit,
                    $s['plan_month'],
                    round($s['plan_to_date'], 2),
                    $s['fact'],
                    $s['pace_pct'],
                    $this->kpiService->getStatusLabel($s['status']),
                ];
            }
        }
        return $rows;
    }
}
