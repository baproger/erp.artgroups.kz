<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = ['name', 'city', 'color', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class)->orderBy('sort_order');
    }

    public function activeDepartments(): HasMany
    {
        return $this->hasMany(Department::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getBgClass(): string
    {
        return match ($this->color) {
            'blue'   => 'from-blue-400 via-blue-500 to-indigo-500',
            'violet' => 'from-violet-400 via-violet-500 to-purple-500',
            'purple' => 'from-purple-400 via-pink-400 to-rose-400',
            'amber'  => 'from-amber-400 via-orange-400 to-orange-500',
            default  => 'from-emerald-400 via-emerald-500 to-teal-500',
        };
    }

    public function getChartColor(): string
    {
        return match ($this->color) {
            'blue'   => '#3b82f6',
            'violet' => '#8b5cf6',
            'purple' => '#a855f7',
            'amber'  => '#f59e0b',
            default  => '#10b981',
        };
    }

    public function getDotClass(): string
    {
        return match ($this->color) {
            'blue'   => 'bg-blue-400',
            'violet' => 'bg-violet-400',
            'purple' => 'bg-purple-400',
            'amber'  => 'bg-amber-400',
            default  => 'bg-emerald-400',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this->color) {
            'blue'   => 'bg-blue-100 text-blue-700',
            'violet' => 'bg-violet-100 text-violet-700',
            'purple' => 'bg-purple-100 text-purple-700',
            'amber'  => 'bg-amber-100 text-amber-700',
            default  => 'bg-emerald-100 text-emerald-700',
        };
    }
}
