<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DepartmentsSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $companyStats) {}

    public function title(): string
    {
        return 'Отделы';
    }

    public function headings(): array
    {
        return ['Отдел', 'Эффективность (%)', 'Проблемных KPI'];
    }

    public function array(): array
    {
        return collect($this->companyStats['departments'])->map(fn($d) => [
            $d['department']->name,
            $d['eff_pct'],
            $d['problematic'],
        ])->toArray();
    }
}
