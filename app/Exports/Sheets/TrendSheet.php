<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TrendSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $trend, private int $year, private int $month) {}

    public function title(): string
    {
        return 'Динамика компании';
    }

    public function headings(): array
    {
        return ['День', 'Дата', 'Эффективность (%)'];
    }

    public function array(): array
    {
        return array_map(fn($t) => [$t['day'], $t['date'] . '.' . $this->year, $t['eff']], $this->trend);
    }
}
