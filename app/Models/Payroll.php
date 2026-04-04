<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payroll';

    protected $fillable = [
        'employee_id',
        'period_label',
        'period_start',
        'period_end',
        'base_pay',
        'overtime_pay',
        'allowances',
        'gross_pay',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'other_deductions',
        'net_pay',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'period_start'            => 'date',
        'period_end'              => 'date',
        'base_pay'                => 'decimal:2',
        'overtime_pay'            => 'decimal:2',
        'allowances'              => 'decimal:2',
        'gross_pay'               => 'decimal:2',
        'sss_contribution'        => 'decimal:2',
        'philhealth_contribution' => 'decimal:2',
        'pagibig_contribution'    => 'decimal:2',
        'withholding_tax'         => 'decimal:2',
        'other_deductions'        => 'decimal:2',
        'net_pay'                 => 'decimal:2',
        'processed_at'            => 'datetime',
    ];

    public function getTotalDeductionsAttribute(): float
    {
        return $this->sss_contribution
            + $this->philhealth_contribution
            + $this->pagibig_contribution
            + $this->withholding_tax
            + $this->other_deductions;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'processed_by');
    }
}
