<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registered_branch_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'loyalty_points',
        'tier',
    ];

    protected $casts = [
        'birth_date'     => 'date',
        'loyalty_points' => 'integer',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function isOfLegalAge(): bool
    {
        return $this->birth_date && $this->birth_date->age >= 18;
    }

    public function registeredBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'registered_branch_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SalesTransaction::class);
    }
}
