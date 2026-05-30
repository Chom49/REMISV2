<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('label', 120);
            $table->text('description')->nullable();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('text'); // text, number, boolean, textarea
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            ['key' => 'app_name',                   'label' => 'Application Name',              'description' => 'Displayed in browser tabs and emails.',        'value' => 'REMIS',                                  'type' => 'text',     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_description',             'label' => 'Application Description',       'description' => 'Short description of the application.',        'value' => 'Rental Management Information System',   'type' => 'text',     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'maintenance_mode',            'label' => 'Maintenance Mode',              'description' => 'Puts the site into maintenance mode.',          'value' => '0',                                      'type' => 'boolean',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_login_attempts',          'label' => 'Max Login Attempts',            'description' => 'Consecutive failed logins before lockout.',     'value' => '5',                                      'type' => 'number',   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'session_timeout_minutes',     'label' => 'Session Timeout (minutes)',     'description' => 'Idle time before the session lock triggers.',   'value' => '60',                                     'type' => 'number',   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'allow_tenant_registration',   'label' => 'Allow Tenant Self-Registration','description' => 'Let tenants register without landlord invite.',  'value' => '0',                                      'type' => 'boolean',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_email',               'label' => 'Support Email',                 'description' => 'Contact email shown to users.',                 'value' => '',                                       'type' => 'text',     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'currency_symbol',             'label' => 'Currency Symbol',               'description' => 'Symbol shown on rent and payment amounts.',     'value' => 'Tshs',                                   'type' => 'text',     'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
