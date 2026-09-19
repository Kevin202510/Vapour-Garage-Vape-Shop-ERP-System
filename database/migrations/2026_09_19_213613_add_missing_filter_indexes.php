<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds indexes for columns that are filtered on directly but were never
     * indexed, on tables that grow without bound (sales_transactions,
     * audit_logs, attendances) — see SalesTransactionRepository::buildQuery(),
     * AuditLogRepository::buildQuery(), AttendanceRepository::buildQuery().
     */
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->index('payment_method');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('action');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropIndex(['payment_method']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['action']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
