<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('property_id')
                  ->constrained('units')->nullOnDelete();
            $table->text('lease_terms')->nullable()->after('lease_expiry_reminder_days');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'lease_terms']);
        });
    }
};
