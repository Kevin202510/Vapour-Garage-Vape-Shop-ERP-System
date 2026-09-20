<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Extends audit_logs.action so Purchase Order and Stock Transfer lifecycle
 * events (previously unaudited entirely) can be logged with a precise action
 * name instead of overloading 'update'.
 *
 * MySQL only: SQLite (used in testing) enforces the enum's allowed values via
 * a CHECK constraint baked into the original CREATE TABLE, which can't be
 * altered in place without rebuilding the table. Since SQLite is test-only
 * here and never the production driver, this migration is a no-op there.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE audit_logs MODIFY action ENUM('create', 'update', 'delete', 'void', 'approve', 'reject', 'receive', 'dispatch', 'cancel') NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE audit_logs MODIFY action ENUM('create', 'update', 'delete', 'void', 'approve', 'reject') NOT NULL");
    }
};
