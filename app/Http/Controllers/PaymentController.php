<?php

namespace App\Http\Controllers;

use App\Mail\PaymentControlNumber;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Services\BriqSmsService;
use App\Services\LeasePaymentSyncService;
use App\Services\NmbPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    // ─────────────────────── INDEX (tenant-centric) ──────────────

    public function index(Request $request)
    {
        $landlord = Auth::user();

        if ($landlord->hasActiveFinancialOfficer()) {
            return redirect()->route('landlord.fo.index')
                ->with('info', 'Payment management is handled by your Financial Officer.');
        }

        $propertyIds = $landlord->properties()->pluck('id');
        $filter      = $request->query('filter', 'all');
        $sync         = app(LeasePaymentSyncService::class);

        $sync->syncLandlord($landlord->id);

        // Base tenant query — everyone linked to this landlord's properties
        $base = User::where('role', 'tenant')
            ->where(function ($q) use ($propertyIds, $landlord) {
                $q->whereHas('leasesAsTenant', fn($q2) => $q2->whereIn('property_id', $propertyIds))
                  ->orWhere('landlord_id', $landlord->id);
            });

        // ── Stats (always computed on the full unfiltered base) ──
        $hasPendingPayment = fn($q) => $q
            ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propertyIds))
            ->where('status', 'pending');

        $hasOverduePayment = fn($q) => $q
            ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propertyIds))
            ->where('status', 'overdue');

        $stats = [
            'total'    => (clone $base)->count(),
            'pending'  => (clone $base)->whereHas('payments', $hasPendingPayment)->count(),
            'overdue'  => (clone $base)->whereHas('payments', $hasOverduePayment)->count(),
            'upcoming' => (clone $base)->whereHas('payments', fn($q) => $q
                ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propertyIds))
                ->whereIn('status', ['pending', 'overdue'])
                ->where('due_date', '<=', now()->addDays(7))
                ->where('due_date', '>=', now()->subDays(1)))->count(),
        ];

        $upcomingCount = $stats['upcoming'];

        // ── Apply selected filter ──
        $query = clone $base;

        switch ($filter) {
            case 'pending':
                $query->whereHas('payments', $hasPendingPayment);
                break;
            case 'overdue':
                $query->whereHas('payments', $hasOverduePayment);
                break;
            case 'upcoming':
                $query->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propertyIds))
                    ->whereIn('status', ['pending', 'overdue'])
                    ->where('due_date', '<=', now()->addDays(7))
                    ->where('due_date', '>=', now()->subDays(1)));
                break;
            case 'paid':
                $query->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propertyIds))
                    ->where('status', 'paid'))
                    ->whereDoesntHave('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propertyIds))
                    ->whereIn('status', ['pending', 'overdue']));
                break;
            case 'previous':
                $query->where('tenant_status', 'inactive');
                break;
        }

        $tenants = $query
            ->with([
                'leasesAsTenant' => fn($q) => $q
                    ->whereIn('property_id', $propertyIds)
                    ->with('property', 'unit')
                    ->orderByRaw("FIELD(status,'active','renewed','terminated','expired')"),
            ])
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        // ── Load most-relevant payment for each tenant (one extra query) ──
        $tenantIds  = $tenants->pluck('id');
        $paymentMap = Payment::whereIn('tenant_id', $tenantIds)
            ->whereHas('lease', fn($q) => $q->whereIn('property_id', $propertyIds))
            ->with(['lease.property', 'lease.unit'])
            ->orderByRaw("FIELD(status,'overdue','pending','paid')")
            ->orderByDesc('due_date')
            ->get()
            ->groupBy('tenant_id');

        // Build a plain-PHP data map keyed by tenant ID — avoids Eloquent __get/__set magic
        $rowData = [];
        foreach ($tenants as $tenant) {
            $payment      = $paymentMap->get($tenant->id)?->first();
            $activeLease  = $tenant->leasesAsTenant->firstWhere('status', 'active')
                         ?? $tenant->leasesAsTenant->first();
            $rowData[$tenant->id] = [
                'payment'        => $payment,
                'active_lease'   => $activeLease,
                'can_generate'   => $payment !== null && $sync->canGenerateControlNumber($payment),
                'active_control' => $payment !== null && $sync->activeControlNumber($payment),
            ];
        }

        return view('landlord.payments.index', compact('tenants', 'upcomingCount', 'filter', 'stats', 'rowData'));
    }

    // ─────────────────────── GENERATE CONTROL NUMBER ─────────────

    public function generateControlNumber(Payment $payment)
    {
        $this->authorizePayment($payment);
        $sync = app(LeasePaymentSyncService::class);
        $payment = $payment->fresh(['lease', 'tenant']);
        $sync->syncPaymentStatuses(collect([$payment]));
        $payment = $payment->fresh(['lease', 'tenant']);

        if (! $sync->canGenerateControlNumber($payment)) {
            if ($sync->activeControlNumber($payment)) {
                return back()->with('error', 'A valid control number already exists for this payment (' . $payment->control_number . ').');
            }

            return back()->with('error', 'Control numbers can only be generated for unpaid payments on active leases.');
        }

        $service = app(NmbPaymentService::class);
        $result  = $service->generateControlNumber($payment);

        if (isset($result['error'])) {
            Log::warning('NMB control number generation failed', [
                'payment_id' => $payment->id,
                'error'      => $result['error'],
            ]);
            return back()->with('error', 'Could not generate control number: ' . $result['error']);
        }

        $payment->update([
            'control_number'              => $result['control_number'],
            'control_number_generated_at' => now(),
            'control_number_sent_at'      => null,
            'control_number_sent_via'     => null,
        ]);

        return back()->with('success', 'Control number generated: ' . $result['control_number']);
    }

    // ─────────────────────── SEND CONTROL NUMBER ─────────────────

    public function sendControlNumber(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        $request->validate([
            'channel' => 'required|in:email,sms',
        ]);

        if (empty($payment->control_number)) {
            return back()->with('error', 'No control number has been generated yet. Please generate one first.');
        }

        $sync = app(LeasePaymentSyncService::class);
        $payment = $payment->fresh(['lease', 'tenant']);
        $sync->syncPaymentStatuses(collect([$payment]));
        $payment = $payment->fresh(['lease', 'tenant']);

        if (! $sync->activeControlNumber($payment)) {
            return back()->with('error', 'This control number is expired or the lease is no longer active. Generate a fresh control number first.');
        }

        $payment->loadMissing(['tenant', 'lease.property', 'lease.unit']);
        $tenant  = $payment->tenant;
        $channel = $request->channel;

        if (! $tenant) {
            return back()->with('error', 'No tenant linked to this payment.');
        }

        $result = $channel === 'sms'
            ? $this->sendViaSms($payment, $tenant)
            : $this->sendViaEmail($payment, $tenant);

        if (! $result['success']) {
            return back()->with('error', 'Could not send control number: ' . $result['error']);
        }

        $payment->update([
            'control_number_sent_at'  => now(),
            'control_number_sent_via' => $channel,
        ]);

        $label = $channel === 'sms' ? 'SMS' : 'email';
        return back()->with('success', "Control number {$payment->control_number} sent via {$label} to {$tenant->name}.");
    }

    // ─────────────────────── REAL-TIME STATUS CHECK ───────────────

    /**
     * AJAX endpoint: checks NMB for payment status and updates the DB if paid.
     * Returns JSON for the frontend poller.
     */
    public function checkStatus(Payment $payment)
    {
        $this->authorizePayment($payment);

        if (empty($payment->control_number)) {
            return response()->json(['status' => 'no_control_number', 'message' => 'No control number generated.']);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'status'      => 'paid',
                'paid_at'     => optional($payment->nmb_paid_at ?? $payment->paid_date)->format('d M Y H:i'),
                'receipt'     => $payment->nmb_receipt_number,
                'payer'       => $payment->nmb_payer_name,
                'reference'   => $payment->reference,
            ]);
        }

        $service = app(NmbPaymentService::class);
        $result  = $service->getPaymentInfo($payment->control_number);

        if (isset($result['error'])) {
            return response()->json(['status' => 'error', 'message' => $result['error']]);
        }

        if ($result['paid']) {
            $tx = $result['latest_tx'];

            $payment->update([
                'status'           => 'paid',
                'paid_date'        => now()->toDateString(),
                'nmb_paid_at'      => now(),
                'nmb_transaction_id'  => $tx['transactionRef'] ?? null,
                'nmb_receipt_number'  => $tx['receipt'] ?? null,
                'nmb_payer_name'      => $tx['payerName'] ?? null,
                'nmb_payer_mobile'    => $tx['payerMobile'] ?? null,
                'reference'           => $tx['transactionRef'] ?? $payment->reference,
            ]);

            Log::info('NMB payment confirmed', [
                'payment_id'   => $payment->id,
                'control_number' => $payment->control_number,
                'receipt'      => $tx['receipt'] ?? null,
            ]);

            return response()->json([
                'status'    => 'just_paid',
                'message'   => 'Payment of TZS ' . number_format($payment->amount, 0) . ' confirmed by NMB.',
                'paid_at'   => now()->format('d M Y H:i'),
                'receipt'   => $tx['receipt'] ?? null,
                'payer'     => $tx['payerName'] ?? null,
                'amount'    => number_format($payment->amount, 0),
            ]);
        }

        // Check NMB bill status from verify endpoint for additional detail
        $invoiceStatus = $result['data']['invoice']['status'] ?? 'pending';

        return response()->json([
            'status'         => 'pending',
            'invoice_status' => $invoiceStatus,
            'message'        => 'Payment not yet received.',
            'transactions'   => count($result['transactions']),
        ]);
    }

    // ─────────────────────── BATCH STATUS POLL ───────────────────

    /**
     * AJAX: Check all unpaid payments with control numbers for a landlord.
     * Returns JSON array of payment IDs that are now paid.
     */
    public function pollAll()
    {
        $landlord    = Auth::user();
        $propertyIds = $landlord->properties()->pluck('id');

        $unpaidWithControlNumber = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propertyIds))
            ->whereIn('status', ['pending', 'overdue'])
            ->whereNotNull('control_number')
            ->get();

        $nowPaid  = [];
        $service  = app(NmbPaymentService::class);

        foreach ($unpaidWithControlNumber as $payment) {
            $result = $service->getPaymentInfo($payment->control_number);

            if (isset($result['error'])) {
                continue;
            }

            if ($result['paid']) {
                $tx = $result['latest_tx'];

                $payment->update([
                    'status'              => 'paid',
                    'paid_date'           => now()->toDateString(),
                    'nmb_paid_at'         => now(),
                    'nmb_transaction_id'  => $tx['transactionRef'] ?? null,
                    'nmb_receipt_number'  => $tx['receipt'] ?? null,
                    'nmb_payer_name'      => $tx['payerName'] ?? null,
                    'nmb_payer_mobile'    => $tx['payerMobile'] ?? null,
                    'reference'           => $tx['transactionRef'] ?? $payment->reference,
                ]);

                $nowPaid[] = [
                    'payment_id'     => $payment->id,
                    'tenant_name'    => optional($payment->tenant)->name,
                    'amount'         => number_format($payment->amount, 0),
                    'control_number' => $payment->control_number,
                    'receipt'        => $tx['receipt'] ?? null,
                ];
            }
        }

        return response()->json([
            'checked'  => $unpaidWithControlNumber->count(),
            'now_paid' => $nowPaid,
        ]);
    }

    // ─────────────────────── PRIVATE HELPERS ─────────────────────

    private function sendViaSms(Payment $payment, User $tenant): array
    {
        if (empty($tenant->phone)) {
            return ['success' => false, 'error' => 'Tenant has no phone number on record.'];
        }

        $property  = optional($payment->lease->property)->name ?? 'your unit';
        $unit      = optional($payment->lease->unit)->unit_number;
        $firstName = explode(' ', $tenant->name)[0];
        $amount    = number_format($payment->amount, 0);
        $dueDate   = $payment->due_date->format('d M Y');
        $ctlNo     = $payment->control_number;

        $message = "Hi {$firstName}, your rent payment of TZS {$amount} for {$property}"
                 . ($unit ? " / {$unit}" : '')
                 . " is due on {$dueDate}. "
                 . "Pay via NMB using control number: {$ctlNo}. "
                 . "Visit any NMB branch, ATM, or use NMB internet/mobile banking.";

        return app(BriqSmsService::class)->send($tenant->phone, $message);
    }

    private function sendViaEmail(Payment $payment, User $tenant): array
    {
        try {
            Mail::to($tenant->email)->send(new PaymentControlNumber($payment, $tenant));
        } catch (\Exception $e) {
            Log::error('PaymentControlNumber email failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Email could not be sent. Please check your mail configuration.'];
        }

        return ['success' => true];
    }

    private function authorizePayment(Payment $payment): void
    {
        $landlordId  = Auth::id();
        $propertyIds = Property::where('landlord_id', $landlordId)->pluck('id');

        $allowed = $payment->lease
            ? $propertyIds->contains($payment->lease->property_id)
            : false;

        abort_if(! $allowed, 403);
    }

    // ─────────────────────── UPCOMING (API for dashboard badge) ──

    public function upcomingCount()
    {
        $landlord    = Auth::user();
        $propertyIds = $landlord->properties()->pluck('id');

        $count = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propertyIds))
            ->whereIn('status', ['pending', 'overdue'])
            ->where('due_date', '<=', now()->addDays(7))
            ->where('due_date', '>=', now()->subDays(1))
            ->count();

        return response()->json(['count' => $count]);
    }
}
