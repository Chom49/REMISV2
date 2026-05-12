<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Speed up payment aggregate queries (SUM/COUNT by tenant + status)
        Schema::table('payments', function (Blueprint $table) {
            if (!$this->indexExists('payments', 'payments_tenant_status_index')) {
                $table->index(['tenant_id', 'status'], 'payments_tenant_status_index');
            }
            if (!$this->indexExists('payments', 'payments_due_date_index')) {
                $table->index('due_date', 'payments_due_date_index');
            }
        });

        // Speed up lease lookups by tenant + property + status
        Schema::table('leases', function (Blueprint $table) {
            if (!$this->indexExists('leases', 'leases_tenant_property_index')) {
                $table->index(['tenant_id', 'property_id'], 'leases_tenant_property_index');
            }
            if (!$this->indexExists('leases', 'leases_landlord_status_index')) {
                $table->index(['landlord_id', 'status'], 'leases_landlord_status_index');
            }
        });

        // Speed up tenant list filtering by role + landlord
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_role_landlord_index')) {
                $table->index(['role', 'landlord_id'], 'users_role_landlord_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndexIfExists('payments_tenant_status_index');
            $table->dropIndexIfExists('payments_due_date_index');
        });
        Schema::table('leases', function (Blueprint $table) {
            $table->dropIndexIfExists('leases_tenant_property_index');
            $table->dropIndexIfExists('leases_landlord_status_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('users_role_landlord_index');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(\DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($index);
    }
};
