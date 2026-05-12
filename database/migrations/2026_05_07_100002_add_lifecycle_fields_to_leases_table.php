<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand status enum to include 'renewed'
        DB::statement("ALTER TABLE leases MODIFY COLUMN status ENUM('active','expired','terminated','renewed') NOT NULL DEFAULT 'active'");

        Schema::table('leases', function (Blueprint $table) {
            $table->string('termination_reason', 100)->nullable()->after('status');
            $table->text('termination_notes')->nullable()->after('termination_reason');
            $table->timestamp('terminated_at')->nullable()->after('termination_notes');
            $table->unsignedBigInteger('renewal_of_id')->nullable()->after('terminated_at');
            $table->foreign('renewal_of_id')->references('id')->on('leases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropForeign(['renewal_of_id']);
            $table->dropColumn(['termination_reason', 'termination_notes', 'terminated_at', 'renewal_of_id']);
        });
        DB::statement("ALTER TABLE leases MODIFY COLUMN status ENUM('active','expired','terminated') NOT NULL DEFAULT 'active'");
    }
};
