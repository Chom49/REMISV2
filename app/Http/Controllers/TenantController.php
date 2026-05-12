<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    // ─────────────────────── DASHBOARD ───────────────────────

    public function dashboard()
    {
        $tenant = Auth::user();

        $activeLease = Lease::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('property', 'landlord')
            ->first();

        $payments = Payment::where('tenant_id', $tenant->id)
            ->with('lease.property')
            ->orderByRaw("FIELD(status, 'overdue', 'pending', 'paid')")
            ->latest('due_date')
            ->take(10)
            ->get();

        $maintenanceRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
            ->with('property')
            ->latest()
            ->take(5)
            ->get();

        return view('tenant.dashboard', compact('tenant', 'activeLease', 'payments', 'maintenanceRequests'));
    }

    // ─────────────────────── PAYMENTS ────────────────────────

    public function paymentHistory()
    {
        $tenant = Auth::user();

        $activeLease = Lease::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('property')
            ->first();

        $payments = Payment::where('tenant_id', $tenant->id)
            ->with('lease.property')
            ->latest('due_date')
            ->get();

        return view('tenant.payments.history', compact('activeLease', 'payments'));
    }

    public function paymentCheckout(Payment $payment)
    {
        abort_if($payment->tenant_id !== Auth::id(), 403);

        return view('tenant.payments.checkout', compact('payment'));
    }

    public function paymentPay(Request $request, Payment $payment)
    {
        abort_if($payment->tenant_id !== Auth::id(), 403);
        abort_if($payment->status === 'paid', 422);

        // Mock payment processing — mark as paid immediately
        $payment->update([
            'status'    => 'paid',
            'paid_date' => now(),
            'reference' => strtoupper('PAY-' . substr(md5(uniqid()), 0, 8)),
        ]);

        return redirect()->route('tenant.payments.history')
                         ->with('success', 'Payment of Tzs ' . number_format($payment->amount, 0) . ' processed successfully.');
    }

    // ─────────────────────── PASSWORD CHANGE ─────────────────

    public function changePasswordShow()
    {
        return view('tenant.change-password');
    }

    public function changePasswordUpdate(Request $request)
    {
        $tenant = Auth::user();

        $request->validate([
            'current_password' => ['required', function ($attr, $value, $fail) use ($tenant) {
                if (! Hash::check($value, $tenant->password)) {
                    $fail('The current password you entered is incorrect.');
                }
            }],
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                'different:current_password',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'password.different'   => 'Your new password must be different from the default password.',
            'password.regex'       => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'password.min'         => 'Password must be at least 8 characters.',
            'password.confirmed'   => 'The password confirmation does not match.',
        ]);

        $tenant->update([
            'password'              => Hash::make($request->password),
            'force_password_change' => false,
            'default_password_hint' => null,
            'invitation_status'     => 'accepted',
        ]);

        return redirect()->route('tenant.dashboard')
                         ->with('success', 'Password updated successfully. Welcome to REMIS!');
    }

    // ─────────────────────── MAINTENANCE ─────────────────────

    public function maintenanceIndex()
    {
        $tenant = Auth::user();

        $base = MaintenanceRequest::where('tenant_id', $tenant->id)->with('property');

        $new        = (clone $base)->where('status', 'open')->latest()->get();
        $inProgress = (clone $base)->where('status', 'in_progress')->latest()->get();
        $completed  = (clone $base)->whereIn('status', ['resolved', 'closed'])->latest()->get();

        return view('tenant.maintenance.index', compact('new', 'inProgress', 'completed'));
    }

    public function storeMaintenance(Request $request)
    {
        $tenant = Auth::user();
        $lease  = Lease::where('tenant_id', $tenant->id)->where('status', 'active')->first();

        if (! $lease) {
            return back()->with('error', 'You must have an active lease to submit a maintenance request.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'required|in:low,medium,high,urgent',
            'due_date'    => 'nullable|date',
        ]);

        $validated['property_id'] = $lease->property_id;
        $validated['tenant_id']   = $tenant->id;
        $validated['viewable_by'] = 'all';

        MaintenanceRequest::create($validated);

        return back()->with('success', 'Maintenance request submitted successfully.');
    }
}
