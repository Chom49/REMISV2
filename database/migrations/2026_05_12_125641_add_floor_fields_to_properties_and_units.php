<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('floor_layout', ['single_floor', 'multi_floor'])
                  ->nullable()
                  ->after('number_of_units');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('floor_number', 100)->nullable()->after('property_id');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('floor_layout');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('floor_number');
        });
    }
};
