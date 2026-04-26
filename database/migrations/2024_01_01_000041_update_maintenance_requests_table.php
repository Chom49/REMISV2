<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('description');
            $table->enum('viewable_by', ['landlord_only', 'all'])->default('all')->after('due_date');
        });

        // Make tenant_id nullable without doctrine/dbal
        DB::statement('ALTER TABLE maintenance_requests MODIFY COLUMN tenant_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'viewable_by']);
        });

        DB::statement('ALTER TABLE maintenance_requests MODIFY COLUMN tenant_id BIGINT UNSIGNED NOT NULL');
    }
};
