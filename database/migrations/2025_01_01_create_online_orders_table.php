<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255);
            $table->string('phone', 20);
            $table->text('address');
            $table->string('city', 100);
            $table->foreignId('pickup_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->enum('payment_method', ['gcash', 'maya', 'cod', 'bank_transfer']);
            $table->text('notes')->nullable();
            $table->decimal('subtotal',    10, 2)->default(0);
            $table->decimal('tax_amount',  10, 2)->default(0);
            $table->decimal('total_amount',10, 2)->default(0);
            $table->enum('status', ['pending','confirmed','processing','ready','completed','cancelled'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->foreignId('fulfilled_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('online_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 255);
            $table->string('sku', 50);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal',   10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_order_items');
        Schema::dropIfExists('online_orders');
    }
};
