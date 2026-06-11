<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiPlan extends Model
{
    protected $fillable = ['kpi_id', 'year', 'month', 'value', 'updated_by'];

    protected $casts = ['value' => 'float'];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
