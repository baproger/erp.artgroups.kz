<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiFact extends Model
{
    protected $fillable = ['kpi_id', 'fact_date', 'value', 'created_by', 'note'];

    protected $casts = ['fact_date' => 'date', 'value' => 'float'];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
