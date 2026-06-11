<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    protected $fillable = [
        'department_id', 'name', 'slug', 'unit', 'direction', 'weight', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean', 'weight' => 'integer'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function plans()
    {
        return $this->hasMany(KpiPlan::class);
    }

    public function facts()
    {
        return $this->hasMany(KpiFact::class);
    }

    public function getPlanForMonth(int $year, int $month): float
    {
        $plan = $this->plans()->where('year', $year)->where('month', $month)->first();
        return $plan ? (float) $plan->value : 0.0;
    }

    public function getFactSumForMonth(int $year, int $month): float
    {
        return (float) $this->facts()
            ->whereYear('fact_date', $year)
            ->whereMonth('fact_date', $month)
            ->sum('value');
    }
}
