<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('property_category', ['single', 'multi'])->default('single')->after('status');
            $table->unsignedInteger('number_of_units')->nullable()->after('property_category');
            $table->string('image')->nullable()->after('number_of_units');
            // Make legacy fields nullable so multi-unit properties don't need them
            $table->decimal('rent_amount', 10, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['property_category', 'number_of_units', 'image']);
            $table->decimal('rent_amount', 10, 2)->nullable(false)->change();
        });
    }
};
