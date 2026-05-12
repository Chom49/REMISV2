<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('force_password_change')->default(false)->after('tenant_status');
            $table->string('default_password_hint', 20)->nullable()->after('force_password_change');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['force_password_change', 'default_password_hint']);
        });
    }
};
