<?php

namespace App\Exports\Sheets;

use App\Models\Recommendation;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RecommendationsSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private $recommendations) {}

    public function title(): string
    {
        return 'Рекомендации';
    }

    public function headings(): array
    {
        return ['Тип', 'Отдел', 'Сообщение', 'Дата'];
    }

    public function array(): array
    {
        return $this->recommendations->map(fn($r) => [
            Recommendation::TYPE_LABELS[$r->type] ?? $r->type,
            $r->department?->name ?? '—',
            $r->message,
            $r->created_at->format('d.m.Y'),
        ])->toArray();
    }
}
