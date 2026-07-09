<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeasePaymentSyncService
{
    public function syncLandlord(int $landlordId): void
    {
        Lease::where('landlord_id', $landlordId)
            ->with('payments')
            ->chunkById(100, function (Collection $leases) {
                $leases->each(fn (Lease $lease) => $this->syncLease($lease));
            });
    }

    public function syncLease(Lease $lease): ?Payment
    {
        $lease->loadMissing('payments');

        $this->syncPaymentStatuses($lease->payments);

        if (! $this->leaseCanReceivePayment($lease)) {
            return null;
        }

        $payment = $lease->payments
            ->whereIn('status', ['pending', 'overdue'])
            ->sortByDesc('due_date')
            ->first();

        $dueDate = $this->currentDueDate($lease);
        $status  = $dueDate->isPast() && ! $dueDate->isToday() ? 'overdue' : 'pending';

        if (! $payment) {
            return Payment::create([
                'lease_id'  => $lease->id,
                'tenant_id' => $lease->tenant_id,
                'amount'    => $lease->monthly_rent,
                'due_date'  => $dueDate->toDateString(),
                'status'    => $status,
                'notes'     => 'Generated from lease rent terms.',
            ]);
        }

        $updates = [
            'tenant_id' => $lease->tenant_id,
            'amount'    => $lease->monthly_rent,
            'status'    => $status,
        ];

        if (empty($payment->control_number)) {
            $updates['due_date'] = $dueDate->toDateString();
        }

        if ($this->hasChanged($payment, $updates)) {
            $payment->update($updates);
        }

        return $payment->refresh();
    }

    public function syncPaymentStatuses(Collection $payments): void
    {
        foreach ($payments as $payment) {
            $payment->loadMissing('lease');

            if ($payment->status === 'paid') {
                continue;
            }

            $status = $this->statusForPayment($payment);

            if ($payment->status !== $status) {
                $payment->update(['status' => $status]);
            }
        }
    }

    public function statusForPayment(Payment $payment): string
    {
        if ($payment->status === 'paid') {
            return 'paid';
        }

        $lease = $payment->lease;

        if (! $lease || $lease->status !== 'active' || $lease->end_date->isPast()) {
            return 'overdue';
        }

        return $payment->due_date && $payment->due_date->isPast() && ! $payment->due_date->isToday()
            ? 'overdue'
            : 'pending';
    }

    public function activeControlNumber(Payment $payment): bool
    {
        $payment->loadMissing('lease');

        return ! empty($payment->control_number)
            && $payment->status !== 'paid'
            && $this->controlNumberExpiresAt($payment)
            && ($this->controlNumberExpiresAt($payment)->isFuture() || $this->controlNumberExpiresAt($payment)->isToday())
            && $payment->lease
            && $payment->lease->status === 'active'
            && ($payment->lease->end_date->isFuture() || $payment->lease->end_date->isToday());
    }

    public function canGenerateControlNumber(Payment $payment): bool
    {
        $payment->loadMissing('lease');

        return $payment->status !== 'paid'
            && $payment->lease
            && $payment->lease->status === 'active'
            && ($payment->lease->end_date->isFuture() || $payment->lease->end_date->isToday())
            && ! $this->activeControlNumber($payment);
    }

    private function leaseCanReceivePayment(Lease $lease): bool
    {
        return ! empty($lease->tenant_id)
            && $lease->status === 'active'
            && ($lease->end_date->isFuture() || $lease->end_date->isToday());
    }

    private function currentDueDate(Lease $lease): Carbon
    {
        $today = now()->startOfDay();
        $day = $lease->payment_day ?: $lease->start_date->day;

        $dueDate = $today->copy()->day(min($day, $today->daysInMonth));

        if ($dueDate->lt($lease->start_date)) {
            $dueDate = $lease->start_date->copy()->startOfDay();
        }

        if ($dueDate->gt($lease->end_date)) {
            $dueDate = $lease->end_date->copy()->startOfDay();
        }

        return $dueDate;
    }

    private function controlNumberExpiresAt(Payment $payment): ?Carbon
    {
        if ($payment->control_number_generated_at) {
            return $payment->control_number_generated_at->copy()->addDays(7)->startOfDay();
        }

        return $payment->due_date?->copy()->startOfDay();
    }

    private function hasChanged(Payment $payment, array $updates): bool
    {
        foreach ($updates as $key => $value) {
            $current = $payment->{$key};
            if ($current instanceof Carbon) {
                $current = $current->toDateString();
            }
            if ((string) $current !== (string) $value) {
                return true;
            }
        }

        return false;
    }
}
