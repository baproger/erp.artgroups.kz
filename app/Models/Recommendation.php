<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $fillable = [
        'type', 'department_id', 'kpi_id', 'message', 'meta',
        'is_dismissed', 'dismissed_by', 'dismissed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_dismissed' => 'boolean',
        'dismissed_at' => 'datetime',
    ];

    public const TYPE_LABELS = [
        'missing_fact'    => 'Пропуск факта',
        'critical_lag'    => 'Критическое отставание',
        'department_drop' => 'Падение отдела',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function dismissedBy()
    {
        return $this->belongsTo(User::class, 'dismissed_by');
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'missing_fact'    => '📋',
            'critical_lag'    => '🔴',
            'department_drop' => '📉',
            default           => '⚠️',
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'missing_fact'    => 'bg-yellow-100 text-yellow-800',
            'critical_lag'    => 'bg-red-100 text-red-800',
            'department_drop' => 'bg-orange-100 text-orange-800',
            default           => 'bg-gray-100 text-gray-800',
        };
    }
}
