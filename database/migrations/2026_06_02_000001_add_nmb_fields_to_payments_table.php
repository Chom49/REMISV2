<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('control_number')->nullable()->after('reference');
            $table->timestamp('control_number_generated_at')->nullable()->after('control_number');
            $table->timestamp('control_number_sent_at')->nullable()->after('control_number_generated_at');
            $table->string('control_number_sent_via', 10)->nullable()->after('control_number_sent_at');
            $table->string('nmb_transaction_id')->nullable()->after('control_number_sent_via');
            $table->string('nmb_receipt_number')->nullable()->after('nmb_transaction_id');
            $table->string('nmb_payer_name')->nullable()->after('nmb_receipt_number');
            $table->string('nmb_payer_mobile')->nullable()->after('nmb_payer_name');
            $table->timestamp('nmb_paid_at')->nullable()->after('nmb_payer_mobile');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'control_number', 'control_number_generated_at',
                'control_number_sent_at', 'control_number_sent_via',
                'nmb_transaction_id', 'nmb_receipt_number',
                'nmb_payer_name', 'nmb_payer_mobile', 'nmb_paid_at',
            ]);
        });
    }
};
