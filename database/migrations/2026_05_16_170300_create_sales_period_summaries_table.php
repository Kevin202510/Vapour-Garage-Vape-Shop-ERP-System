<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_period_summaries', function (Blueprint $table) {
            $table->id();
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->string('period_label');          // e.g. "May 15, 2026" / "May 2026" / "2026"
            $table->date('date_from');
            $table->date('date_to');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete(); // null = all branches

            // Revenue & volume
            $table->decimal('total_revenue', 14, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('total_discounts', 12, 2)->default(0);
            $table->unsignedInteger('total_transactions')->default(0);
            $table->unsignedInteger('total_items_sold')->default(0);
            $table->decimal('avg_basket', 10, 2)->default(0);

            // Voids
            $table->unsignedInteger('voided_transactions')->default(0);
            $table->decimal('voided_amount', 12, 2)->default(0);

            // Payment method breakdown (JSON)
            $table->json('payment_breakdown')->nullable();

            // Top product
            $table->string('top_product_name')->nullable();
            $table->decimal('top_product_revenue', 12, 2)->nullable();

            // Closing metadata
            $table->enum('closed_by', ['system', 'manual'])->default('system');
            $table->string('closed_by_name')->nullable(); // user name for manual closes
            $table->timestamp('closed_at')->useCurrent();

            $table->timestamps();

            $table->unique(['period_type', 'date_from', 'date_to', 'branch_id'], 'uniq_period_branch');
            $table->index(['period_type', 'date_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_period_summaries');
    }
};
