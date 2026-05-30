<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('landlord', 'tenant', 'admin') NOT NULL DEFAULT 'tenant'");
    }

    public function down(): void
    {
        // Remove any admin users before rolling back
        DB::table('users')->where('role', 'admin')->update(['role' => 'tenant']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('landlord', 'tenant') NOT NULL DEFAULT 'tenant'");
    }
};
