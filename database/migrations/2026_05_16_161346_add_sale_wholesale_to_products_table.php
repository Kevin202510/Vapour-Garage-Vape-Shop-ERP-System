<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_new_arrival')->default(false)->after('is_active');
            $table->boolean('is_best_seller')->default(false)->after('is_new_arrival');
            $table->boolean('is_hot')->default(false)->after('is_best_seller');
            $table->boolean('is_on_sale')->default(false)->after('is_hot');
            $table->decimal('sale_price', 10, 2)->nullable()->after('is_on_sale');
            $table->boolean('is_wholesale')->default(false)->after('sale_price');
            $table->unsignedInteger('min_order_qty')->nullable()->after('is_wholesale');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_new_arrival', 'is_best_seller', 'is_hot',
                'is_on_sale', 'sale_price', 'is_wholesale', 'min_order_qty',
            ]);
        });
    }
};
