<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;
    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'employee_id',
        'branch_id',
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'admin']);
    }

    public function isBranchManager(): bool
    {
        return $this->hasRole('branch_manager');
    }

    /**
     * The branch the user is currently "operating in".
     * For admins/super-admins this is driven by the session (branch switcher in topbar).
     * For all other roles it is their permanently assigned branch.
     */
    public function activeBranchId(): ?int
    {
        if ($this->isSuperAdmin()) {
            $id = session('active_branch_id');
            return $id ? (int) $id : $this->branch_id;
        }
        return $this->branch_id;
    }

    public function activeBranch(): ?Branch
    {
        $id = $this->activeBranchId();
        return $id ? Branch::find($id) : null;
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['super_admin', 'admin']));
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
