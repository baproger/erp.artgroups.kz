<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'is_active', 'branch_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function kpis()
    {
        return $this->hasMany(Kpi::class)->orderBy('sort_order');
    }

    public function activeKpis()
    {
        return $this->hasMany(Kpi::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
