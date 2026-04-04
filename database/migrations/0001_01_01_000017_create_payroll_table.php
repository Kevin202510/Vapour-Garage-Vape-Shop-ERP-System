<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->string('period_label')->comment('e.g. 2024-01 First Half');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('base_pay', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2)->default(0);
            $table->decimal('sss_contribution', 8, 2)->default(0);
            $table->decimal('philhealth_contribution', 8, 2)->default(0);
            $table->decimal('pagibig_contribution', 8, 2)->default(0);
            $table->decimal('withholding_tax', 8, 2)->default(0);
            $table->decimal('other_deductions', 8, 2)->default(0);
            $table->decimal('net_pay', 10, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'released'])->default('draft');
            $table->foreignId('processed_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period_start', 'period_end']);
            $table->index(['employee_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
