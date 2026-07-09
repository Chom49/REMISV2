<?php

namespace Tests\Unit;

use App\Models\Lease;
use App\Models\Payment;
use App\Services\LeasePaymentSyncService;
use Tests\TestCase;

class LeasePaymentSyncServiceTest extends TestCase
{
    public function test_status_reflects_due_date_and_lease_details(): void
    {
        $sync = new LeasePaymentSyncService();

        $activeLease = $this->lease(['status' => 'active', 'end_date' => now()->addMonth()]);
        $pending = $this->payment(['status' => 'pending', 'due_date' => now()]);
        $pending->setRelation('lease', $activeLease);

        $overdue = $this->payment(['status' => 'pending', 'due_date' => now()->subDay()]);
        $overdue->setRelation('lease', $activeLease);

        $expiredLeasePayment = $this->payment(['status' => 'pending', 'due_date' => now()]);
        $expiredLeasePayment->setRelation('lease', $this->lease(['status' => 'active', 'end_date' => now()->subDay()]));

        $this->assertSame('pending', $sync->statusForPayment($pending));
        $this->assertSame('overdue', $sync->statusForPayment($overdue));
        $this->assertSame('overdue', $sync->statusForPayment($expiredLeasePayment));
    }

    public function test_active_control_number_blocks_generation_until_expired(): void
    {
        $sync = new LeasePaymentSyncService();
        $lease = $this->lease(['status' => 'active', 'end_date' => now()->addMonth()]);

        $payment = $this->payment([
            'status' => 'pending',
            'due_date' => now()->subDay(),
            'control_number' => '99112233',
            'control_number_generated_at' => now(),
        ]);
        $payment->setRelation('lease', $lease);

        $expiredControl = $this->payment([
            'status' => 'overdue',
            'due_date' => now()->subDay(),
            'control_number' => '99112233',
            'control_number_generated_at' => now()->subDays(8),
        ]);
        $expiredControl->setRelation('lease', $lease);

        $this->assertTrue($sync->activeControlNumber($payment));
        $this->assertFalse($sync->canGenerateControlNumber($payment));
        $this->assertFalse($sync->activeControlNumber($expiredControl));
        $this->assertTrue($sync->canGenerateControlNumber($expiredControl));
    }

    private function lease(array $attributes = []): Lease
    {
        return new Lease(array_merge([
            'status' => 'active',
            'end_date' => now()->addMonth(),
        ], $attributes));
    }

    private function payment(array $attributes = []): Payment
    {
        return new Payment(array_merge([
            'status' => 'pending',
            'due_date' => now(),
            'amount' => 300000,
        ], $attributes));
    }
}
