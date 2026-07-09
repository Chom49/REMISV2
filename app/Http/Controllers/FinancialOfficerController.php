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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class FinancialOfficerController extends Controller
{
    // ── scope helpers ──────────────────────────────────────────────────────

    private function landlordId(): int
    {
        return Auth::user()->landlord_id;
    }

    private function propertyIds()
    {
        return Property::where('landlord_id', $this->landlordId())->pluck('id');
    }

    private function authorizePayment(Payment $payment): void
    {
        abort_if(
            ! $payment->lease || ! $this->propertyIds()->contains($payment->lease->property_id),
            403
        );
    }

    // ── Dashboard ──────────────────────────────────────────────────────────

    public function dashboard()
    {
        $propIds = $this->propertyIds();
        $sync    = app(LeasePaymentSyncService::class);
        $sync->syncLandlord($this->landlordId());

        $totalCollected   = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
                                ->where('status', 'paid')
                                ->whereMonth('paid_date', now()->month)
                                ->whereYear('paid_date',  now()->year)
                                ->sum('amount');

        $pendingCount     = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
                                ->where('status', 'pending')->count();
        $overdueCount     = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
                                ->where('status', 'overdue')->count();
        $verifiedByMe     = Payment::where('fo_verified_by', Auth::id())->count();

        $upcomingPayments = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
                                ->whereIn('status', ['pending', 'overdue'])
                                ->where('due_date', '<=', now()->addDays(7))
                                ->where('due_date', '>=', now()->subDays(1))
                                ->with(['tenant', 'lease.property', 'lease.unit'])
                                ->orderBy('due_date')
                                ->limit(8)
                                ->get();

        $recentTransactions = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
                                ->where('status', 'paid')
                                ->with(['tenant', 'lease.property'])
                                ->orderByDesc('paid_date')
                                ->limit(6)
                                ->get();

        // 6-month income chart
        $chartMonths = [];
        $chartIncome = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $chartMonths[] = $d->format('M');
            $chartIncome[] = (float) Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
                ->where('status', 'paid')
                ->whereYear('paid_date',  $d->year)
                ->whereMonth('paid_date', $d->month)
                ->sum('amount');
        }

        return view('fo.dashboard', compact(
            'totalCollected', 'pendingCount', 'overdueCount', 'verifiedByMe',
            'upcomingPayments', 'recentTransactions', 'chartMonths', 'chartIncome'
        ));
    }

    // ── Payments ───────────────────────────────────────────────────────────

    public function paymentsIndex(Request $request)
    {
        $propIds = $this->propertyIds();
        $filter  = $request->query('filter', 'all');
        $sync    = app(LeasePaymentSyncService::class);
        $sync->syncLandlord($this->landlordId());

        $landlordId = $this->landlordId();

        $tenantScope = fn($q) => $q->where('role', 'tenant')
            ->where(function ($inner) use ($propIds, $landlordId) {
                $inner->whereHas('leasesAsTenant', fn($l) => $l->whereIn('property_id', $propIds))
                      ->orWhere('landlord_id', $landlordId);
            });

        $stats = [
            'total'   => User::where('role', 'tenant')
                ->where(fn($q) => $q
                    ->whereHas('leasesAsTenant', fn($l) => $l->whereIn('property_id', $propIds))
                    ->orWhere('landlord_id', $landlordId))
                ->count(),
            'pending' => User::where('role', 'tenant')
                ->where(fn($q) => $q
                    ->whereHas('leasesAsTenant', fn($l) => $l->whereIn('property_id', $propIds))
                    ->orWhere('landlord_id', $landlordId))
                ->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propIds))
                    ->where('status', 'pending'))
                ->count(),
            'overdue' => User::where('role', 'tenant')
                ->where(fn($q) => $q
                    ->whereHas('leasesAsTenant', fn($l) => $l->whereIn('property_id', $propIds))
                    ->orWhere('landlord_id', $landlordId))
                ->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propIds))
                    ->where('status', 'overdue'))
                ->count(),
            'upcoming' => User::where('role', 'tenant')
                ->where(fn($q) => $q
                    ->whereHas('leasesAsTenant', fn($l) => $l->whereIn('property_id', $propIds))
                    ->orWhere('landlord_id', $landlordId))
                ->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propIds))
                    ->whereIn('status', ['pending', 'overdue'])
                    ->where('due_date', '<=', now()->addDays(7))
                    ->where('due_date', '>=', now()->subDays(1)))
                ->count(),
        ];

        $base = User::where('role', 'tenant')
            ->where(function ($q) use ($propIds, $landlordId) {
                $q->whereHas('leasesAsTenant', fn($l) => $l->whereIn('property_id', $propIds))
                  ->orWhere('landlord_id', $landlordId);
            });

        $query = clone $base;
        switch ($filter) {
            case 'pending':
                $query->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propIds))
                    ->where('status', 'pending'));
                break;
            case 'overdue':
                $query->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propIds))
                    ->where('status', 'overdue'));
                break;
            case 'paid':
                $query->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propIds))
                    ->where('status', 'paid'))
                ->whereDoesntHave('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propIds))
                    ->whereIn('status', ['pending','overdue']));
                break;
            case 'upcoming':
                $query->whereHas('payments', fn($q) => $q
                    ->whereHas('lease', fn($l) => $l->whereIn('property_id', $propIds))
                    ->whereIn('status', ['pending','overdue'])
                    ->where('due_date', '<=', now()->addDays(7))
                    ->where('due_date', '>=', now()->subDays(1)));
                break;
        }

        $tenants = $query
            ->with(['leasesAsTenant' => fn($q) => $q
                ->whereIn('property_id', $propIds)
                ->with('property','unit')
                ->orderByRaw("FIELD(status,'active','renewed','terminated','expired')")])
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $tenantIds  = $tenants->pluck('id');
        $paymentMap = Payment::whereIn('tenant_id', $tenantIds)
            ->whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
            ->with(['lease.property','lease.unit'])
            ->orderByRaw("FIELD(status,'overdue','pending','paid')")
            ->orderByDesc('due_date')
            ->get()
            ->groupBy('tenant_id');

        $rowData = [];
        foreach ($tenants as $tenant) {
            $payment      = $paymentMap->get($tenant->id)?->first();
            $activeLease  = $tenant->leasesAsTenant->firstWhere('status','active')
                         ?? $tenant->leasesAsTenant->first();
            $rowData[$tenant->id] = [
                'payment'        => $payment,
                'active_lease'   => $activeLease,
                'can_generate'   => $payment && $sync->canGenerateControlNumber($payment),
                'active_control' => $payment && $sync->activeControlNumber($payment),
            ];
        }

        return view('fo.payments.index', compact('tenants','stats','filter','rowData'));
    }

    public function generateControlNumber(Payment $payment)
    {
        $this->authorizePayment($payment);
        $sync    = app(LeasePaymentSyncService::class);
        $payment = $payment->fresh(['lease','tenant']);
        $sync->syncPaymentStatuses(collect([$payment]));
        $payment = $payment->fresh(['lease','tenant']);

        if (! $sync->canGenerateControlNumber($payment)) {
            return back()->with('error',
                $sync->activeControlNumber($payment)
                    ? 'A valid control number already exists: ' . $payment->control_number . '.'
                    : 'Control numbers can only be generated for unpaid payments on active leases.'
            );
        }

        $result = app(NmbPaymentService::class)->generateControlNumber($payment);

        if (isset($result['error'])) {
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

    public function sendControlNumber(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);
        $request->validate(['channel' => 'required|in:email,sms']);

        if (empty($payment->control_number)) {
            return back()->with('error', 'No control number generated yet.');
        }

        $payment = $payment->fresh(['lease.property','lease.unit','tenant']);
        $tenant  = $payment->tenant;
        if (! $tenant) return back()->with('error', 'No tenant linked to this payment.');

        $sync = app(LeasePaymentSyncService::class);
        if (! $sync->activeControlNumber($payment)) {
            return back()->with('error', 'Control number is expired. Generate a new one first.');
        }

        $result = $request->channel === 'sms'
            ? $this->sendViaSms($payment, $tenant)
            : $this->sendViaEmail($payment, $tenant);

        if (! $result['success']) {
            return back()->with('error', 'Could not send: ' . $result['error']);
        }

        $payment->update([
            'control_number_sent_at'  => now(),
            'control_number_sent_via' => $request->channel,
        ]);

        return back()->with('success', 'Control number sent via ' . ($request->channel === 'sms' ? 'SMS' : 'email') . ' to ' . $tenant->name . '.');
    }

    public function checkStatus(Payment $payment)
    {
        $this->authorizePayment($payment);

        if (empty($payment->control_number)) {
            return response()->json(['status' => 'no_control_number']);
        }
        if ($payment->status === 'paid') {
            return response()->json([
                'status'  => 'paid',
                'paid_at' => optional($payment->nmb_paid_at ?? $payment->paid_date)->format('d M Y H:i'),
                'receipt' => $payment->nmb_receipt_number,
                'payer'   => $payment->nmb_payer_name,
            ]);
        }

        $result = app(NmbPaymentService::class)->getPaymentInfo($payment->control_number);

        if (isset($result['error'])) {
            return response()->json(['status' => 'error', 'message' => $result['error']]);
        }

        if ($result['paid']) {
            $tx = $result['latest_tx'];
            $payment->update([
                'status'             => 'paid',
                'paid_date'          => now()->toDateString(),
                'nmb_paid_at'        => now(),
                'nmb_transaction_id' => $tx['transactionRef'] ?? null,
                'nmb_receipt_number' => $tx['receipt']        ?? null,
                'nmb_payer_name'     => $tx['payerName']      ?? null,
                'nmb_payer_mobile'   => $tx['payerMobile']    ?? null,
                'fo_verified_by'     => Auth::id(),
                'reference'          => $tx['transactionRef'] ?? $payment->reference,
            ]);
            $this->notifyLandlord($payment);
            return response()->json([
                'status'  => 'just_paid',
                'message' => 'Payment of TZS ' . number_format($payment->amount, 0) . ' confirmed.',
                'paid_at' => now()->format('d M Y H:i'),
                'receipt' => $tx['receipt'] ?? null,
                'payer'   => $tx['payerName'] ?? null,
                'amount'  => number_format($payment->amount, 0),
            ]);
        }

        return response()->json(['status' => 'pending', 'message' => 'Payment not yet received.']);
    }

    public function markPaid(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->status === 'paid') {
            return back()->with('error', 'Payment is already marked as paid.');
        }

        $request->validate([
            'reference' => 'nullable|string|max:100',
            'notes'     => 'nullable|string|max:500',
        ]);

        $payment->update([
            'status'         => 'paid',
            'paid_date'      => now()->toDateString(),
            'fo_verified_by' => Auth::id(),
            'reference'      => $request->reference ?: $payment->reference,
            'notes'          => $request->notes ?: $payment->notes,
        ]);

        $this->notifyLandlord($payment);

        return back()->with('success', 'Payment marked as paid and landlord notified.');
    }

    // ── Reports ────────────────────────────────────────────────────────────

    public function reportsIndex()
    {
        $propIds = $this->propertyIds();

        $monthly = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
            ->where('status','paid')
            ->selectRaw('YEAR(paid_date) as y, MONTH(paid_date) as m, SUM(amount) as total, COUNT(*) as cnt')
            ->groupByRaw('YEAR(paid_date), MONTH(paid_date)')
            ->orderByRaw('y DESC, m DESC')
            ->limit(12)
            ->get();

        $allPayments = Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propIds))
            ->with(['tenant','lease.property','lease.unit'])
            ->orderByDesc('due_date')
            ->paginate(30);

        return view('fo.reports.index', compact('monthly','allPayments'));
    }

    // ── Settings / Profile ──────────────────────────────────────────────────

    public function settingsIndex()
    {
        return view('fo.settings', ['user' => Auth::user()]);
    }

    public function settingsUpdateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);
        Auth::user()->update($validated);
        return back()->with('success', 'Profile updated.');
    }

    public function settingsUpdatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:8',
        ]);
        if (! Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password updated.');
    }

    // ── Force-password-change ───────────────────────────────────────────────

    public function changePasswordShow()
    {
        return view('fo.change-password');
    }

    public function changePasswordUpdate(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);
        Auth::user()->update([
            'password'              => Hash::make($request->password),
            'force_password_change' => false,
        ]);
        return redirect()->route('fo.dashboard')->with('success', 'Password set. Welcome!');
    }

    // ── Private helpers ─────────────────────────────────────────────────────

    private function sendViaSms(Payment $payment, User $tenant): array
    {
        if (empty($tenant->phone)) {
            return ['success' => false, 'error' => 'Tenant has no phone number.'];
        }
        $property  = optional($payment->lease->property)->name ?? 'your unit';
        $unit      = optional($payment->lease->unit)->unit_number;
        $firstName = explode(' ', $tenant->name)[0];
        $message   = "Hi {$firstName}, your rent TZS " . number_format($payment->amount, 0)
            . " for {$property}" . ($unit ? "/{$unit}" : '')
            . " due " . $payment->due_date->format('d M Y')
            . ". NMB control no: {$payment->control_number}. Pay at any NMB channel.";
        return app(BriqSmsService::class)->send($tenant->phone, $message);
    }

    private function sendViaEmail(Payment $payment, User $tenant): array
    {
        try {
            Mail::to($tenant->email)->send(new PaymentControlNumber($payment, $tenant));
        } catch (\Exception $e) {
            Log::error('FO PaymentControlNumber email failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Email could not be sent.'];
        }
        return ['success' => true];
    }

    private function notifyLandlord(Payment $payment): void
    {
        try {
            $landlord = User::find($this->landlordId());
            if (! $landlord) return;

            $payment->loadMissing(['lease.property', 'lease.unit', 'tenant']);

            Mail::to($landlord->email)->send(
                new \App\Mail\FoPaymentNotification($payment, $landlord, Auth::user()->name)
            );
        } catch (\Exception $e) {
            Log::warning('FO landlord notification failed: ' . $e->getMessage());
        }
    }
}
