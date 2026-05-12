<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tin', 50)->nullable()->after('tenant_status');
            $table->string('nida_number', 50)->nullable()->after('tin');
            $table->enum('gender', ['male', 'female'])->nullable()->after('nida_number');
            $table->string('nationality', 100)->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tin', 'nida_number', 'gender', 'nationality']);
        });
    }
};
