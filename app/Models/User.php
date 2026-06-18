<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'department_id', 'branch_id', 'is_active', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public const ROLES = [
        'ceo'                 => 'CEO',
        'commercial_director' => 'Коммерческий директор',
        'finance'             => 'Финансы',
        'sales'               => 'Продажи',
        'marketing'           => 'Маркетинг',
        'production'          => 'Производство',
        'surveyors'           => 'Замерщики',
    ];

    public function isCommercialDirector(): bool
    {
        return $this->role === 'commercial_director';
    }

    public function canManagePlans(): bool
    {
        return in_array($this->role, ['ceo', 'commercial_director']);
    }

    public function canSeeAllDepartments(): bool
    {
        return in_array($this->role, ['ceo', 'commercial_director']);
    }

    public function canSeeRecommendations(): bool
    {
        return in_array($this->role, ['ceo', 'commercial_director']);
    }

    public function canExportExcel(): bool
    {
        return in_array($this->role, ['ceo', 'commercial_director']);
    }

    public function canManageUsers(): bool
    {
        return $this->role === 'ceo';
    }

    public function getRoleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    public function initials(): string
    {
        $parts = array_filter(explode(' ', trim($this->name)));
        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        }
        return strtoupper(mb_substr($this->name, 0, 2));
    }

    public function canSeeAllBranches(): bool
    {
        return $this->role === 'ceo';
    }

    /**
     * Branches the user has explicit multi-branch access to (pivot).
     */
    public function accessibleBranches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    /**
     * Returns branch IDs this user can access on the dashboard.
     * - CEO: all branches
     * - pivot entries set: those specific branches
     * - default: own branch_id only
     */
    public function accessibleBranchIds(): array
    {
        if ($this->canSeeAllBranches()) {
            return Branch::where('is_active', true)->pluck('id')->toArray();
        }

        $pivotIds = $this->accessibleBranches()->pluck('branches.id')->toArray();
        if (! empty($pivotIds)) {
            return $pivotIds;
        }

        return $this->branch_id ? [$this->branch_id] : [];
    }

    public function canAccessBranch(Branch $branch): bool
    {
        if ($this->canSeeAllBranches()) {
            return true;
        }
        return in_array($branch->id, $this->accessibleBranchIds());
    }

    /**
     * Can this user view/edit facts for the given department?
     *
     * Rules (in priority order):
     *  1. CEO → yes always (all branches)
     *  2. commercial_director → only departments in their accessible branches
     *  3. Own department → yes
     *  4. Multi-branch staff: same department name in an accessible branch → yes
     */
    public function canAccessDepartment(\App\Models\Department $department): bool
    {
        if ($this->canSeeAllBranches()) {
            return true;
        }

        if ($this->isCommercialDirector()) {
            return in_array($department->branch_id, $this->accessibleBranchIds());
        }

        if ($this->department_id === $department->id) {
            return true;
        }

        // Same department name across accessible branches (multi-branch staff)
        if ($this->department && ! empty($this->accessibleBranchIds())) {
            $sameName   = $this->department->name === $department->name;
            $accessible = in_array($department->branch_id, $this->accessibleBranchIds());
            return $sameName && $accessible;
        }

        return false;
    }

    /**
     * Отделы, по которым этот сотрудник вводит факты (для напоминаний).
     * CEO и коммерческий директор не заполняют факты сами → пустой список.
     * Мультифилиальный сотрудник: одноимённый отдел во всех доступных филиалах.
     */
    public function fillableDepartments(): \Illuminate\Support\Collection
    {
        if (in_array($this->role, ['ceo', 'commercial_director']) || ! $this->department) {
            return collect();
        }

        $accessIds = $this->accessibleBranchIds();

        if (! empty($accessIds)) {
            $cross = \App\Models\Department::whereIn('branch_id', $accessIds)
                ->where('name', $this->department->name)
                ->where('is_active', true)
                ->with('branch')
                ->get();

            if ($cross->isNotEmpty()) {
                return $cross;
            }
        }

        return collect([$this->department]);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
