<?php

namespace App\Http\Controllers;

use App\Mail\LeaseTerminationNotice;
use App\Mail\TenantInvitation;
use App\Mail\TenantWarningNotice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Models\MaintenanceRequest;
use App\Services\BriqSmsService;
use App\Services\LeasePaymentSyncService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LandlordController extends Controller
{
    // ─────────────────────── DASHBOARD ───────────────────────

    public function dashboard()
    {
        $landlord   = Auth::user();
        app(LeasePaymentSyncService::class)->syncLandlord($landlord->id);

        $properties = Property::where('landlord_id', $landlord->id)->with('activeLease.tenant', 'units')->get();

        $totalRevenue  = Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlord->id))
                                 ->where('status', 'paid')->sum('amount');

        $upcomingRent  = Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlord->id))
                                 ->where('status', 'pending')
                                 ->where('due_date', '>=', now())
                                 ->sum('amount');

        $overdueAmount = Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlord->id))
                                 ->where('status', 'overdue')->sum('amount');

        $monthStart    = now()->startOfMonth();
        $monthEnd      = now()->endOfMonth();
        $monthPayments = Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlord->id))
                                 ->whereBetween('due_date', [$monthStart, $monthEnd])
                                 ->get();
        $collectedUnits = $monthPayments->where('status', 'paid')->count();
        $pendingUnits   = $monthPayments->whereIn('status', ['pending', 'overdue'])->count();
        $totalUnits     = $collectedUnits + $pendingUnits;
        $collectionRate = $totalUnits > 0 ? round(($collectedUnits / $totalUnits) * 100, 1) : 0;

        $stats = [
            'total_properties' => $properties->count(),
            'occupied'         => $properties->where('status', 'occupied')->count(),
            'available'        => $properties->where('status', 'available')->count(),
            'total_revenue'    => $totalRevenue,
            'upcoming_rent'    => $upcomingRent,
            'overdue_amount'   => $overdueAmount,
            'collected_units'  => $collectedUnits,
            'pending_units'    => $pendingUnits,
            'collection_rate'  => $collectionRate,
        ];

        $chartData = $this->buildChartData($landlord->id);

        $maintenanceRequests = MaintenanceRequest::whereHas('property', fn($q) => $q->where('landlord_id', $landlord->id))
            ->with('property', 'tenant')
            ->where('status', '!=', 'closed')
            ->latest()
            ->take(5)
            ->get();

        return view('landlord.dashboard', compact(
            'landlord', 'properties', 'stats', 'maintenanceRequests', 'chartData'
        ));
    }

    private function buildChartData(int $landlordId): array
    {
        $months   = [];
        $income   = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date     = now()->subMonths($i);
            $start    = $date->copy()->startOfMonth();
            $end      = $date->copy()->endOfMonth();
            $months[] = $date->format('M');
            $income[] = (float) Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlordId))
                                        ->where('status', 'paid')
                                        ->whereBetween('paid_date', [$start, $end])
                                        ->sum('amount');
            $expenses[] = 0;
        }

        return compact('months', 'income', 'expenses');
    }

    // ─────────────────────── PROPERTIES ───────────────────────

    public function propertiesIndex()
    {
        $landlord   = Auth::user();
        $properties = Property::where('landlord_id', $landlord->id)
                               ->with('activeLease.tenant', 'units')
                               ->get();

        $stats = [
            'total'    => $properties->count(),
            'occupied' => $properties->filter(fn ($p) => $p->activeLease && $p->activeLease->tenant)->count(),
            'available'=> $properties->filter(fn ($p) => ! $p->activeLease || ! $p->activeLease->tenant)->count(),
        ];

        return view('landlord.properties.index', compact('properties', 'stats'));
    }

    public function propertiesCreate()
    {
        return view('landlord.properties.create');
    }

    public function storeProperty(Request $request)
    {
        $category    = $request->input('property_category');
        $floorLayout = $request->input('floor_layout');

        $rules = [
            'name'              => 'required|string|max:255',
            'address'           => 'required|string|max:255',
            'city'              => 'nullable|string|max:100',
            'county'            => 'nullable|string|max:100',
            'total_area'        => 'nullable|numeric|min:0',
            'property_category' => 'required|in:single,multi',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ];

        if ($category === 'multi') {
            $rules['floor_layout'] = 'required|in:single_floor,multi_floor';

            if ($floorLayout === 'single_floor') {
                $rules['number_of_units'] = 'required|integer|min:1|max:500';
                $rules['unit_prefix']     = 'nullable|string|max:30';
            } else {
                $rules['num_floors']              = 'required|integer|min:1|max:50';
                $rules['floor_configs']           = 'required|array|min:1';
                $rules['floor_configs.*.name']    = 'required|string|max:100';
                $rules['floor_configs.*.count']   = 'required|integer|min:1|max:200';
            }
        }

        $validated = $request->validate($rules);

        $propertyData = [
            'landlord_id'       => Auth::id(),
            'name'              => $validated['name'],
            'address'           => $validated['address'],
            'city'              => $validated['city'] ?? null,
            'county'            => $validated['county'] ?? null,
            'total_area'        => $validated['total_area'] ?? null,
            'property_category' => $category,
            'floor_layout'      => $category === 'multi' ? $floorLayout : null,
            'type'              => 'apartment',
            'bedrooms'          => 0,
            'bathrooms'         => 1,
            'status'            => 'available',
        ];

        if ($request->hasFile('image')) {
            $propertyData['image'] = $request->file('image')->store('properties', 'public');
        }

        $property = Property::create($propertyData);

        $units = [];
        $now   = now();

        if ($category === 'single') {
            $units[] = [
                'property_id'  => $property->id,
                'floor_number' => null,
                'unit_number'  => 'Unit 1',
                'status'       => 'vacant',
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        } elseif ($floorLayout === 'single_floor') {
            $count  = (int) ($validated['number_of_units'] ?? 1);
            $prefix = trim($validated['unit_prefix'] ?? '') ?: 'Unit';
            $property->update(['number_of_units' => $count]);

            for ($i = 1; $i <= $count; $i++) {
                $units[] = [
                    'property_id'  => $property->id,
                    'floor_number' => null,
                    'unit_number'  => "{$prefix} {$i}",
                    'status'       => 'vacant',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }
        } else {
            // multi_floor — auto-generate: Floor 1 → A1,A2… Floor 2 → B1,B2… etc.
            $totalUnits   = 0;
            $letterOffset = 0;
            foreach ($validated['floor_configs'] as $floor) {
                $floorName = trim($floor['name']);
                $count     = (int) $floor['count'];
                $letter    = chr(65 + $letterOffset);
                for ($i = 1; $i <= $count; $i++) {
                    $units[] = [
                        'property_id'  => $property->id,
                        'floor_number' => $floorName,
                        'unit_number'  => "{$letter}{$i}",
                        'status'       => 'vacant',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                    $totalUnits++;
                }
                $letterOffset++;
            }
            $property->update(['number_of_units' => $totalUnits]);
        }

        Unit::insert($units);

        $unitCount = count($units);
        return redirect()->route('landlord.properties.show', $property)
                         ->with('success', "Property created with {$unitCount} unit(s). Start by creating a lease on any vacant unit.");
    }

    public function propertiesShow(Property $property)
    {
        $this->authorizeProperty($property);

        $property->load([
            'units.activeLease.tenant',
            'units.leases.tenant',
            'leases.unit',
            'leases.tenant',
            'maintenanceRequests.tenant',
        ]);

        $tenants = User::where('role', 'tenant')
                       ->where(function ($q) {
                           $q->where('landlord_id', Auth::id())
                             ->orWhereHas('leasesAsTenant', fn($q2) => $q2->whereHas('property', fn($q3) => $q3->where('landlord_id', Auth::id())));
                       })
                       ->get();

        $allTenants = User::where('role', 'tenant')->get();

        $activeTab = request('tab', 'overview');

        return view('landlord.properties.show', compact('property', 'tenants', 'allTenants', 'activeTab'));
    }

    // ─────────────────────── UNITS ────────────────────────────

    public function unitLeaseCreate(Property $property, Unit $unit)
    {
        $this->authorizeProperty($property);
        abort_if($unit->property_id !== $property->id, 404);

        return view('landlord.units.lease-create', compact('property', 'unit'));
    }

    public function unitLeaseStore(Request $request, Property $property, Unit $unit)
    {
        $this->authorizeProperty($property);
        abort_if($unit->property_id !== $property->id, 404);

        $validated = $request->validate([
            'start_date'                 => 'required|date',
            'end_date'                   => 'required|date|after:start_date',
            'monthly_rent'               => 'required|numeric|min:0',
            'security_deposit'           => 'nullable|numeric|min:0',
            'payment_day'                => 'nullable|integer|min:1|max:31',
            'payment_frequency'          => 'required|in:monthly,weekly,bi-weekly,quarterly,annually',
            'lease_expiry_reminder_days' => 'nullable|integer|min:0|max:365',
            'lease_terms'                => 'nullable|string',
        ]);

        $validated['property_id']      = $property->id;
        $validated['unit_id']          = $unit->id;
        $validated['landlord_id']      = Auth::id();
        $validated['tenant_id']        = null;
        $validated['status']           = 'active';
        $validated['security_deposit'] = $validated['security_deposit'] ?? 0;

        $lease = Lease::create($validated);

        $property->update(['status' => 'occupied']);

        return redirect()
            ->route('landlord.properties.show', [$property, 'tab' => 'units'])
            ->with('success', 'Lease created for unit ' . $unit->unit_number . '. Now assign a tenant.')
            ->with('open_assign_tenant', $lease->id);
    }

    public function unitLeaseAssignTenant(Request $request, Property $property, Unit $unit, Lease $lease)
    {
        $this->authorizeProperty($property);
        abort_if($unit->property_id !== $property->id, 404);
        abort_if($lease->unit_id !== $unit->id, 404);

        $request->validate(['tenant_id' => 'required|exists:users,id']);

        $lease->update(['tenant_id' => $request->tenant_id]);
        $unit->update(['status' => 'occupied']);
        app(LeasePaymentSyncService::class)->syncLease($lease->refresh());

        return redirect()
            ->route('landlord.properties.show', [$property, 'tab' => 'units'])
            ->with('success', 'Tenant assigned to unit ' . $unit->unit_number . '.');
    }

    // ─────────────────────── LEASES ───────────────────────────

    public function leasesCreate(Property $property)
    {
        $this->authorizeProperty($property);
        return view('landlord.leases.create', compact('property'));
    }

    public function leasesStore(Request $request, Property $property)
    {
        $this->authorizeProperty($property);

        $validated = $request->validate([
            'start_date'                  => 'required|date',
            'end_date'                    => 'required|date|after:start_date',
            'monthly_rent'                => 'required|numeric|min:0',
            'security_deposit'            => 'nullable|numeric|min:0',
            'payment_day'                 => 'nullable|integer|min:1|max:31',
            'payment_frequency'           => 'required|in:monthly,weekly,bi-weekly,quarterly,annually',
            'lease_expiry_reminder_days'  => 'nullable|integer|min:0|max:365',
        ]);

        $validated['property_id']      = $property->id;
        $validated['landlord_id']      = Auth::id();
        $validated['tenant_id']        = null;
        $validated['status']           = 'active';
        $validated['security_deposit'] = $validated['security_deposit'] ?? 0;

        Lease::create($validated);
        $property->update(['status' => 'occupied']);

        return redirect()->route('landlord.properties.show', $property)
                         ->with('success', 'Lease created. Now link a tenant to complete setup.')
                         ->with('open_link_tenant', true);
    }

    public function leasesIndex()
    {
        $landlordId = Auth::id();

        $leases = Lease::where('landlord_id', $landlordId)
            ->with('property', 'unit', 'tenant', 'payments')
            ->orderByRaw("FIELD(status,'active','pending','expired','terminated')")
            ->latest()
            ->get();

        $sync = app(LeasePaymentSyncService::class);
        $leases->each(fn (Lease $lease) => $sync->syncLease($lease));
        $leases->load('payments');

        $today = now()->startOfDay();

        $stats = [
            'total'         => $leases->count(),
            'active'        => $leases->where('status', 'active')->count(),
            'expiring_soon' => $leases->filter(fn($l) =>
                $l->status === 'active' &&
                $l->end_date >= $today &&
                $l->end_date <= $today->copy()->addDays(30)
            )->count(),
            'expired'       => $leases->filter(fn($l) =>
                $l->status === 'active' && $l->end_date < $today
            )->count(),
        ];

        return view('landlord.leases.index', compact('leases', 'stats'));
    }

    public function terminateLease(Request $request, Lease $lease)
    {
        abort_if($lease->landlord_id !== Auth::id(), 403);

        $request->validate([
            'termination_reason' => 'required|string|max:100',
            'termination_notes'  => 'nullable|string|max:2000',
            'notify_tenant'      => 'nullable|in:1',
            'notice_channel'     => 'nullable|in:email,sms',
        ]);

        $lease->update([
            'status'             => 'terminated',
            'termination_reason' => $request->termination_reason,
            'termination_notes'  => $request->termination_notes,
            'terminated_at'      => now(),
        ]);

        // Free up the unit
        if ($lease->unit) {
            $lease->unit->update(['status' => 'vacant']);
        }

        // Free up the property if no other active leases remain
        $otherActive = Lease::where('property_id', $lease->property_id)
                            ->where('id', '!=', $lease->id)
                            ->where('status', 'active')
                            ->exists();
        if (!$otherActive && $lease->property) {
            $lease->property->update(['status' => 'available']);
        }

        // Set tenant inactive if no other active leases
        if ($lease->tenant_id) {
            $hasOtherActive = Lease::where('tenant_id', $lease->tenant_id)
                                   ->where('id', '!=', $lease->id)
                                   ->where('status', 'active')
                                   ->exists();
            if (!$hasOtherActive) {
                $lease->tenant->update(['tenant_status' => 'inactive']);
            }
        }

        // Send termination notice to tenant if requested
        $noticeResult = ['success' => true];
        if ($request->input('notify_tenant') === '1' && $lease->tenant) {
            $channel  = $request->input('notice_channel', 'email');
            $lease->load(['tenant', 'property', 'unit']);
            $noticeResult = $this->sendTerminationNotice($lease, $channel);
        }

        if (!$noticeResult['success']) {
            return back()->with('warning', 'Lease terminated, but notice could not be sent: ' . $noticeResult['error']);
        }

        $channel      = $request->input('notice_channel', 'email');
        $notifyLabel  = $request->input('notify_tenant') === '1'
            ? ' Notice sent via ' . ($channel === 'sms' ? 'SMS' : 'email') . '.'
            : '';

        return back()->with('success', 'Lease terminated. Unit is now vacant.' . $notifyLabel);
    }

    private function sendTerminationNotice(Lease $lease, string $channel): array
    {
        $loginUrl = rtrim(url('/'), '/') . '/#login';

        if ($channel === 'sms') {
            if (empty($lease->tenant->phone)) {
                return ['success' => false, 'error' => 'Tenant has no phone number on record.'];
            }
            $property  = $lease->property->name ?? 'your property';
            $reason    = ucfirst(str_replace('_', ' ', $lease->termination_reason));
            $date      = $lease->terminated_at->format('d M Y');
            $firstName = explode(' ', $lease->tenant->name)[0];
            $message   = "Hi {$firstName}, your lease at {$property} has been terminated effective {$date}. "
                       . "Reason: {$reason}. Login to REMIS for details: {$this->smsLoginUrl()}";
            return app(BriqSmsService::class)->send($lease->tenant->phone, $message);
        }

        try {
            Mail::to($lease->tenant->email)->send(new LeaseTerminationNotice($lease, $loginUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Termination notice email failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Email could not be sent. Please check your mail configuration.'];
        }
        return ['success' => true];
    }

    public function renewLease(Request $request, Lease $lease)
    {
        abort_if($lease->landlord_id !== Auth::id(), 403);

        $request->validate([
            'end_date'     => 'required|date|after:' . $lease->end_date->format('Y-m-d'),
            'monthly_rent' => 'nullable|numeric|min:0',
            'lease_terms'  => 'nullable|string',
        ]);

        // Mark old lease as renewed
        $lease->update(['status' => 'renewed']);

        // Create new lease as continuation
        $newLease = Lease::create([
            'property_id'                => $lease->property_id,
            'unit_id'                    => $lease->unit_id,
            'tenant_id'                  => $lease->tenant_id,
            'landlord_id'                => $lease->landlord_id,
            'start_date'                 => $lease->end_date->copy()->addDay(),
            'end_date'                   => $request->end_date,
            'monthly_rent'               => $request->monthly_rent ?? $lease->monthly_rent,
            'security_deposit'           => $lease->security_deposit,
            'payment_day'                => $lease->payment_day,
            'payment_frequency'          => $lease->payment_frequency,
            'lease_expiry_reminder_days' => $lease->lease_expiry_reminder_days,
            'lease_terms'                => $request->lease_terms ?? $lease->lease_terms,
            'status'                     => 'active',
            'renewal_of_id'              => $lease->id,
        ]);
        app(LeasePaymentSyncService::class)->syncLease($newLease);

        return back()->with('success', 'Lease renewed successfully. New contract is active until ' . \Carbon\Carbon::parse($request->end_date)->format('d M Y') . '.');
    }

    public function leasesShow(Lease $lease)
    {
        abort_if($lease->landlord_id !== Auth::id(), 403);
        app(LeasePaymentSyncService::class)->syncLease($lease);
        $lease->load('property', 'unit', 'tenant', 'landlord', 'payments');
        return view('landlord.leases.show', compact('lease'));
    }

    public function leasesDownload(Lease $lease)
    {
        abort_if($lease->landlord_id !== Auth::id(), 403);
        $lease->load('property', 'unit', 'tenant', 'landlord');

        $filename = 'lease-contract-' . ($lease->unit?->unit_number ?? $lease->id) . '-' . now()->format('Ymd') . '.pdf';

        // Generate via CLI PHP (has working GD → renders the logo PNG).
        $tmpPath = storage_path('app/temp/lease-' . $lease->id . '-' . uniqid() . '.pdf');
        @mkdir(storage_path('app/temp'), 0755, true);

        $phpCli  = 'C:/Users/Admin/Downloads/php-8.4.7-Win32-vs17-x64/php.exe';
        $artisan = base_path('artisan');

        $process = \Illuminate\Support\Facades\Process::run(
            [$phpCli, $artisan, 'remis:generate-pdf', $lease->id, $tmpPath]
        );

        if (file_exists($tmpPath)) {
            $content = file_get_contents($tmpPath);
            @unlink($tmpPath);
            return response($content, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Fallback: generate directly (logo will be missing if GD unavailable).
        $pdf = Pdf::loadView('landlord.leases.pdf', compact('lease'))
                  ->setPaper([0, 0, 595.28, 841.89], 'portrait') // A4 in points (exact)
                  ->setOption('default_paper_size', 'a4')
                  ->setOption('default_paper_orientation', 'portrait')
                  ->setOption('defaultFont', 'serif')
                  ->setOption('isRemoteEnabled', false)
                  ->setOption('isHtml5ParserEnabled', true)
                  ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download($filename);
    }

    public function leasesSendNotice(Request $request, Lease $lease)
    {
        abort_if($lease->landlord_id !== Auth::id(), 403);
        abort_if($lease->status !== 'active', 422);
        abort_if(! $lease->tenant?->email, 422);

        $validated = $request->validate([
            'reason'   => 'required|string|max:255',
            'comments' => 'nullable|string|max:2000',
        ]);

        $lease->load('property', 'unit', 'tenant', 'landlord');

        $reason   = $validated['reason'];
        $comments = $validated['comments'] ?? '';

        // Send after the response is flushed so the page loads instantly.
        dispatch(function () use ($lease, $reason, $comments) {
            Mail::to($lease->tenant->email)
                ->send(new TenantWarningNotice(
                    lease:    $lease,
                    reason:   $reason,
                    comments: $comments,
                ));
        })->afterResponse();

        return back()->with('success', 'Warning notice sent successfully to ' . $lease->tenant->name . '.');
    }

    // ─────────────────────── LINK TENANT (legacy) ─────────────

    public function linkTenant(Request $request, Property $property)
    {
        $this->authorizeProperty($property);

        $request->validate(['tenant_id' => 'required|exists:users,id']);

        $lease = $property->activeLease;
        if ($lease) {
            $lease->update(['tenant_id' => $request->tenant_id]);
        }

        $property->update(['status' => 'occupied']);

        return redirect()->route('landlord.properties.show', $property)
                         ->with('success', 'Tenant linked successfully.');
    }

    // ─────────────────────── TENANTS ──────────────────────────

    public function tenantsIndex()
    {
        $landlord    = Auth::user();
        $propertyIds = $landlord->properties()->pluck('id');

        $tenants = User::where('role', 'tenant')
                       ->where(function ($q) use ($propertyIds, $landlord) {
                           $q->whereHas('leasesAsTenant', fn($q) => $q->whereIn('property_id', $propertyIds))
                             ->orWhere('landlord_id', $landlord->id);
                       })
                       ->with([
                           'leasesAsTenant' => fn($q) => $q->whereIn('property_id', $propertyIds)
                                                           ->with('property', 'unit')
                                                           ->latest(),
                           'payments' => fn($q) => $q->latest()->limit(1),
                       ])
                       ->get();

        $stats = [
            'total'          => $tenants->count(),
            'active'         => $tenants->filter(fn($t) => $t->leasesAsTenant->firstWhere('status', 'active') !== null)->count(),
            'inactive'       => $tenants->filter(fn($t) => $t->leasesAsTenant->firstWhere('status', 'active') === null)->count(),
            'expiring_soon'  => $tenants->filter(function ($t) {
                $lease = $t->leasesAsTenant->firstWhere('status', 'active');
                if (!$lease) return false;
                $days = now()->startOfDay()->diffInDays($lease->end_date, false);
                return $days >= 0 && $days <= 30;
            })->count(),
        ];

        $showFoRecommendation = $stats['active'] > 3
            && ! $landlord->hasActiveFinancialOfficer()
            && ! $landlord->preference('fo_recommendation_dismissed', false);

        return view('landlord.tenants.index', compact('tenants', 'stats', 'showFoRecommendation'));
    }

    public function tenantsShow(User $user)
    {
        $landlord    = Auth::user();
        $propertyIds = $landlord->properties()->pluck('id');

        // Ensure this tenant belongs to this landlord's context
        $belongsToLandlord = $user->landlord_id === $landlord->id
            || Lease::where('tenant_id', $user->id)->whereIn('property_id', $propertyIds)->exists();
        abort_if(!$belongsToLandlord, 403);

        $user->load([
            'leasesAsTenant' => fn($q) => $q->whereIn('property_id', $propertyIds)
                                            ->with('property', 'unit')
                                            ->latest(),
            'maintenanceRequests.property',
        ]);

        // SQL aggregates — avoids loading every payment row into memory
        $totalPaid    = $user->payments()->where('status', 'paid')->sum('amount');
        $totalOverdue = $user->payments()->where('status', 'overdue')->sum('amount');
        $pendingCount = $user->payments()->whereIn('status', ['pending', 'overdue'])->count();
        $payments     = $user->payments()->orderByDesc('due_date')->get();

        return view('landlord.tenants.show', [
            'tenant'       => $user,
            'totalPaid'    => $totalPaid,
            'totalOverdue' => $totalOverdue,
            'pendingCount' => $pendingCount,
            'payments'     => $payments,
        ]);
    }

    public function tenantsCreate(Request $request)
    {
        $fromProperty = $request->query('from_property');
        $fromUnit     = $request->query('from_unit');
        $fromLease    = $request->query('from_lease');
        return view('landlord.tenants.create', compact('fromProperty', 'fromUnit', 'fromLease'));
    }

    public function tenantsStore(Request $request)
    {
        $mode = $request->input('mode', 'manual');

        $plainPassword = $this->generateSimplePassword();

        if ($mode === 'tin') {
            $validated = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name'  => 'nullable|string|max:100',
                'tin'        => 'required|string|max:50',
                'email'      => 'required|email|unique:users,email',
                'phone'      => 'nullable|string|max:20',
                'gender'     => 'nullable|in:male,female',
                'notes'      => 'nullable|string',
            ]);

            $fullName = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));

            $tenant = User::create([
                'name'                  => $fullName ?: $validated['first_name'],
                'email'                 => $validated['email'],
                'phone'                 => $validated['phone'] ?? null,
                'gender'                => $validated['gender'] ?? null,
                'nationality'           => 'Tanzanian',
                'tin'                   => $validated['tin'],
                'nida_number'           => null,
                'role'                  => 'tenant',
                'password'              => Hash::make($plainPassword),
                'landlord_id'           => Auth::id(),
                'force_password_change' => true,
                'default_password_hint' => $plainPassword,
                'invitation_status'     => 'invited',
            ]);
        } else {
            $validated = $request->validate([
                'first_name'    => 'required|string|max:100',
                'last_name'     => 'required|string|max:100',
                'email'         => 'required|email|unique:users,email',
                'phone'         => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date',
                'gender'        => 'nullable|in:male,female',
                'nationality'   => 'nullable|string|max:100',
                'tin'           => 'nullable|string|max:50',
                'nida_number'   => 'nullable|string|max:50',
                'notes'         => 'nullable|string',
            ]);

            $tenant = User::create([
                'name'                  => $validated['first_name'] . ' ' . $validated['last_name'],
                'email'                 => $validated['email'],
                'phone'                 => $validated['phone'] ?? null,
                'gender'                => $validated['gender'] ?? null,
                'nationality'           => $validated['nationality'] ?? null,
                'tin'                   => $validated['tin'] ?? null,
                'nida_number'           => $validated['nida_number'] ?? null,
                'role'                  => 'tenant',
                'password'              => Hash::make($plainPassword),
                'landlord_id'           => Auth::id(),
                'force_password_change' => true,
                'default_password_hint' => $plainPassword,
                'invitation_status'     => 'invited',
            ]);
        }

        $channel = $request->input('channel', 'email');
        $result  = $this->sendInvitation($tenant, $plainPassword, $channel);

        if (!$result['success']) {
            return redirect()->route('landlord.tenants.index')
                             ->with('warning', 'Tenant added but invitation could not be sent: ' . $result['error']);
        }

        $channelLabel = $channel === 'sms' ? 'SMS' : 'email';

        // If coming from a unit lease flow, auto-assign and redirect back
        $fromLeaseId = $request->input('from_lease');
        $fromUnitId  = $request->input('from_unit');
        $fromPropId  = $request->input('from_property');

        if ($fromLeaseId && $fromUnitId && $fromPropId) {
            $property = Property::where('id', $fromPropId)->where('landlord_id', Auth::id())->first();
            $unit     = $property ? Unit::where('id', $fromUnitId)->where('property_id', $property->id)->first() : null;
            $lease    = $unit    ? Lease::where('id', $fromLeaseId)->where('unit_id', $unit->id)->first() : null;

            if ($lease) {
                $lease->update(['tenant_id' => $tenant->id]);
                $unit->update(['status' => 'occupied']);
                app(LeasePaymentSyncService::class)->syncLease($lease->refresh());
                return redirect()
                    ->route('landlord.properties.show', [$property, 'tab' => 'units'])
                    ->with('success', 'Tenant added, assigned to unit ' . $unit->unit_number . ', and invitation ' . $channelLabel . ' sent.');
            }
        }

        if ($fromPropId) {
            $property = Property::where('id', $fromPropId)->where('landlord_id', Auth::id())->first();
            if ($property) {
                $lease = $property->activeLease;
                if ($lease) {
                    $lease->update(['tenant_id' => $tenant->id]);
                    app(LeasePaymentSyncService::class)->syncLease($lease->refresh());
                }
                $property->update(['status' => 'occupied']);
                return redirect()->route('landlord.properties.show', $property)
                                 ->with('success', 'Tenant added, linked to property, and invitation ' . $channelLabel . ' sent.');
            }
        }

        // FO recommendation: prompt when active tenants exceed 3 and no active FO exists
        $activeTenantCount = User::where('role', 'tenant')
            ->where('landlord_id', Auth::id())
            ->where('tenant_status', 'active')
            ->count();

        $foRecommend = $activeTenantCount > 3
            && ! Auth::user()->hasActiveFinancialOfficer()
            && ! Auth::user()->preference('fo_recommendation_dismissed', false);

        return redirect()->route('landlord.tenants.index')
                         ->with('success', 'Tenant added and invitation ' . $channelLabel . ' sent.')
                         ->with('fo_recommendation', $foRecommend);
    }

    // ─────────────────────── FINANCIAL OFFICER MANAGEMENT ─────────────────

    public function foIndex()
    {
        $officers    = User::where('role', 'financial_officer')
            ->where('landlord_id', Auth::id())
            ->latest()
            ->get();
        $propertyIds = Auth::user()->properties()->pluck('id');

        foreach ($officers as $fo) {
            $fo->paymentsVerified = Payment::where('fo_verified_by', $fo->id)->count();
            $fo->totalCollected   = (float) Payment::where('fo_verified_by', $fo->id)->where('status', 'paid')->sum('amount');
        }

        $summary = [
            'collected' => (float) Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propertyIds))
                ->where('status', 'paid')->whereMonth('paid_date', now()->month)->whereYear('paid_date', now()->year)->sum('amount'),
            'pending'   => Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propertyIds))->where('status', 'pending')->count(),
            'overdue'   => Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propertyIds))->where('status', 'overdue')->count(),
            'verified'  => Payment::whereHas('lease', fn($q) => $q->whereIn('property_id', $propertyIds))->whereNotNull('fo_verified_by')->whereMonth('paid_date', now()->month)->count(),
        ];

        return view('landlord.financial-officer.index', compact('officers', 'summary'));
    }

    public function foCreate()
    {
        return view('landlord.financial-officer.create');
    }

    public function foStore(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
        ]);

        $plain = $this->generateSimplePassword();

        $fo = User::create([
            'name'                  => $validated['name'],
            'email'                 => $validated['email'],
            'phone'                 => $validated['phone'] ?? null,
            'role'                  => 'financial_officer',
            'password'              => Hash::make($plain),
            'landlord_id'           => Auth::id(),
            'tenant_status'         => 'active',
            'force_password_change' => true,
            'default_password_hint' => $plain,
            'invitation_status'     => 'invited',
        ]);

        // Send invitation email
        $loginUrl = rtrim(url('/'), '/') . '/#login';
        try {
            Mail::to($fo->email)->send(new \App\Mail\FoInvitation($fo, $plain, $loginUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('FO invitation email failed', ['error' => $e->getMessage()]);
        }

        // Dismiss the FO recommendation once one is created
        $prefs = Auth::user()->preferences ?? [];
        $prefs['fo_recommendation_dismissed'] = true;
        Auth::user()->update(['preferences' => $prefs]);

        return redirect()->route('landlord.fo.index')
                         ->with('success', 'Financial Officer account created and invitation sent to ' . $fo->email . '.');
    }

    public function foEdit(User $fo)
    {
        abort_if($fo->landlord_id !== Auth::id() || ! $fo->isFinancialOfficer(), 403);
        return view('landlord.financial-officer.edit', compact('fo'));
    }

    public function foUpdate(Request $request, User $fo)
    {
        abort_if($fo->landlord_id !== Auth::id() || ! $fo->isFinancialOfficer(), 403);

        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $fo->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $fo->update($validated);
        return redirect()->route('landlord.fo.index')->with('success', 'Financial Officer updated.');
    }

    public function foToggle(User $fo)
    {
        abort_if($fo->landlord_id !== Auth::id() || ! $fo->isFinancialOfficer(), 403);

        $newStatus = $fo->tenant_status === 'active' ? 'inactive' : 'active';
        $fo->update(['tenant_status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';
        return back()->with('success', "Financial Officer account {$label}.");
    }

    public function foDestroy(User $fo)
    {
        abort_if($fo->landlord_id !== Auth::id() || ! $fo->isFinancialOfficer(), 403);
        $fo->delete();
        return redirect()->route('landlord.fo.index')->with('success', 'Financial Officer removed.');
    }

    public function foResendInvitation(User $fo)
    {
        abort_if($fo->landlord_id !== Auth::id() || ! $fo->isFinancialOfficer(), 403);

        $plain = $this->generateSimplePassword();
        $fo->update([
            'password'              => Hash::make($plain),
            'force_password_change' => true,
            'default_password_hint' => $plain,
            'invitation_status'     => 'invited',
        ]);

        $loginUrl = rtrim(url('/'), '/') . '/#login';
        try {
            Mail::to($fo->email)->send(new \App\Mail\FoInvitation($fo, $plain, $loginUrl));
        } catch (\Exception $e) {
            Log::warning('FO resend invitation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Could not send invitation email: ' . $e->getMessage());
        }

        return back()->with('success', 'Invitation email resent to ' . $fo->email . '.');
    }

    public function foDismissRecommendation()
    {
        $prefs = Auth::user()->preferences ?? [];
        $prefs['fo_recommendation_dismissed'] = true;
        Auth::user()->update(['preferences' => $prefs]);
        return redirect()->back();
    }

    public function tenantInvite(Request $request, User $user)
    {
        $channel       = $request->input('channel', 'email');
        $plainPassword = $this->generateSimplePassword();
        $user->update([
            'password'              => Hash::make($plainPassword),
            'force_password_change' => true,
            'default_password_hint' => $plainPassword,
            'invitation_status'     => 'invited',
        ]);
        $result = $this->sendInvitation($user, $plainPassword, $channel);

        if (!$result['success']) {
            return back()->with('error', 'Invitation could not be sent: ' . $result['error']);
        }

        $channelLabel = $channel === 'sms' ? 'SMS' : 'email';
        return back()->with('success', 'Invitation ' . $channelLabel . ' sent to ' . ($channel === 'sms' ? $user->phone : $user->email) . '.');
    }

    public function tenantsEdit(User $user)
    {
        $landlord    = Auth::user();
        $propertyIds = $landlord->properties()->pluck('id');
        $belongsToLandlord = $user->landlord_id === $landlord->id
            || Lease::where('tenant_id', $user->id)->whereIn('property_id', $propertyIds)->exists();
        abort_if(!$belongsToLandlord, 403);

        return view('landlord.tenants.edit', compact('user'));
    }

    public function tenantsUpdate(Request $request, User $user)
    {
        $landlord    = Auth::user();
        $propertyIds = $landlord->properties()->pluck('id');
        $belongsToLandlord = $user->landlord_id === $landlord->id
            || Lease::where('tenant_id', $user->id)->whereIn('property_id', $propertyIds)->exists();
        abort_if(!$belongsToLandlord, 403);

        // Name is only editable for manually-added tenants (no TIN on record)
        $rules = [
            'phone'       => 'nullable|string|max:20',
            'gender'      => 'nullable|in:male,female',
            'nationality' => 'nullable|string|max:100',
        ];
        if (! $user->tin) {
            $rules['first_name'] = 'required|string|max:100';
            $rules['last_name']  = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $update = [
            'phone'       => $validated['phone'] ?? null,
            'gender'      => $validated['gender'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
        ];
        if (! $user->tin) {
            $update['name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        }

        $user->update($update);

        return redirect()->route('landlord.tenants.show', $user)
                         ->with('success', 'Tenant profile updated.');
    }

    /**
     * Generate a simple, human-friendly default password.
     * Format: Remis@{4 random digits}  e.g. Remis@4729
     * Satisfies uppercase + lowercase + special char + number requirements.
     */
    private function generateSimplePassword(): string
    {
        return 'Remis@' . random_int(1000, 9999);
    }

    private function sendInvitation(User $tenant, string $plainPassword, string $channel = 'email'): array
    {
        $loginUrl = rtrim(url('/'), '/') . '/#login';

        if ($channel === 'sms') {
            if (empty($tenant->phone)) {
                // No phone — fall through to email silently
            } else {
                $message = $this->buildSmsInvitation($tenant, $plainPassword, $this->smsLoginUrl());
                $smsResult = app(BriqSmsService::class)->send($tenant->phone, $message);
                if ($smsResult['success']) {
                    return $smsResult;
                }
                // SMS failed — fall back to email below
                \Illuminate\Support\Facades\Log::warning('SMS invitation failed, falling back to email', [
                    'tenant' => $tenant->id,
                    'error'  => $smsResult['error'] ?? 'unknown',
                ]);
            }
        }

        // Email path (primary or fallback)
        try {
            Mail::to($tenant->email)->send(new TenantInvitation($tenant, $plainPassword, $loginUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tenant invitation email failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Invitation could not be sent. Please check your mail configuration.'];
        }
        return ['success' => true];
    }

    private function buildSmsInvitation(User $tenant, string $plainPassword, string $loginUrl): string
    {
        $firstName = explode(' ', $tenant->name)[0];
        return "Hi {$firstName}, your REMIS account is ready!\n"
             . "Email: {$tenant->email}\n"
             . "Password: {$plainPassword}\n"
             . "Login: {$loginUrl}\n"
             . "Please change your password after first login.";
    }

    /**
     * Build a login link the recipient can actually open. APP_URL is
     * "http://localhost/..." on dev machines, which no phone can resolve —
     * carriers silently drop SMS containing such a dead link. Substitute
     * this machine's real LAN IP instead, so anyone on the same network
     * (e.g. same office/property WiFi) can open it from their phone.
     */
    private function smsLoginUrl(): string
    {
        $appUrl = rtrim(config('app.url'), '/');
        $parsed = parse_url($appUrl);
        $host   = $parsed['host'] ?? 'localhost';

        if ($host === 'localhost' || $host === '127.0.0.1') {
            $lanIp = $this->detectLanIp();
            if ($lanIp) {
                $scheme = $parsed['scheme'] ?? 'http';
                $path   = $parsed['path'] ?? '';
                return "{$scheme}://{$lanIp}{$path}/#login";
            }
        }

        return $appUrl . '/#login';
    }

    /**
     * Determine this machine's outbound LAN IP by "connecting" a UDP socket
     * to an external address (no packets are actually sent) and reading the
     * local address the OS would route through. More reliable than
     * gethostbyname(gethostname()), which can return an unrelated adapter
     * (e.g. a VPN or VirtualBox host-only IP) on multi-NIC machines.
     */
    private function detectLanIp(): ?string
    {
        $sock = @stream_socket_client('udp://8.8.8.8:53', $errno, $errstr, 1);
        if (!$sock) {
            return null;
        }
        $name = stream_socket_get_name($sock, false);
        fclose($sock);

        return $name ? explode(':', $name)[0] : null;
    }

    // ─────────────────────── MAINTENANCE ─────────────────────

    public function maintenanceIndex()
    {
        $landlordId = Auth::id();
        $base = MaintenanceRequest::whereHas('property', fn($q) => $q->where('landlord_id', $landlordId))
                                   ->with('property', 'tenant');

        $new        = (clone $base)->where('status', 'open')->latest()->get();
        $inProgress = (clone $base)->where('status', 'in_progress')->latest()->get();
        $completed  = (clone $base)->whereIn('status', ['resolved', 'closed'])->latest()->get();

        $properties = Property::where('landlord_id', $landlordId)->get();

        return view('landlord.maintenance.index', compact('new', 'inProgress', 'completed', 'properties'));
    }

    public function maintenanceStore(Request $request)
    {
        $validated = $request->validate([
            'property_id'  => 'required|exists:properties,id',
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'priority'     => 'required|in:low,medium,high,urgent',
            'status'       => 'required|in:open,in_progress,resolved,closed',
            'due_date'     => 'nullable|date',
            'viewable_by'  => 'required|in:landlord_only,all',
        ]);

        $this->authorizeProperty(Property::findOrFail($validated['property_id']));
        MaintenanceRequest::create($validated);

        return back()->with('success', 'Maintenance request created.');
    }

    public function maintenanceUpdate(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorizeProperty($maintenanceRequest->property);
        $request->validate(['status' => 'required|in:open,in_progress,resolved,closed']);
        $maintenanceRequest->update(['status' => $request->status]);

        return back()->with('success', 'Status updated.');
    }

    public function maintenanceDestroy(MaintenanceRequest $maintenanceRequest)
    {
        $this->authorizeProperty($maintenanceRequest->property);
        $maintenanceRequest->delete();

        return back()->with('success', 'Maintenance request removed.');
    }

    // ─────────────────────── REPORTS ──────────────────────────

    public function reportsIndex()
    {
        $landlordId = Auth::id();
        $stats = [
            'payments'   => Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlordId))->count(),
            'tenants'    => User::where('role', 'tenant')->where('landlord_id', $landlordId)->count(),
            'overdue'    => Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlordId))
                                ->where(fn($q) => $q->where('status', 'overdue')
                                    ->orWhere(fn($q2) => $q2->where('status', 'pending')->where('due_date', '<', now()->toDateString())))
                                ->count(),
            'properties' => Property::where('landlord_id', $landlordId)->count(),
        ];
        return view('landlord.reports.index', compact('stats'));
    }

    public function reportRentPaymentsPdf()
    {
        return $this->streamReportPdf('rent-payments', 'rent-payments-report-' . now()->format('Ymd') . '.pdf');
    }

    public function reportTenantsPdf()
    {
        return $this->streamReportPdf('tenants', 'tenants-report-' . now()->format('Ymd') . '.pdf');
    }

    public function reportOverduePdf()
    {
        return $this->streamReportPdf('overdue', 'overdue-payments-report-' . now()->format('Ymd') . '.pdf');
    }

    public function reportPropertiesPdf()
    {
        return $this->streamReportPdf('properties', 'properties-report-' . now()->format('Ymd') . '.pdf');
    }

    private function streamReportPdf(string $type, string $filename)
    {
        $landlordId = Auth::id();
        $tmpPath    = storage_path('app/temp/report-' . $type . '-' . $landlordId . '-' . uniqid() . '.pdf');
        @mkdir(storage_path('app/temp'), 0755, true);

        $phpCli  = 'C:/Users/Admin/Downloads/php-8.4.7-Win32-vs17-x64/php.exe';
        $artisan = base_path('artisan');

        \Illuminate\Support\Facades\Process::run(
            [$phpCli, $artisan, 'remis:generate-report-pdf', $type, $landlordId, $tmpPath]
        );

        if (file_exists($tmpPath)) {
            $content = file_get_contents($tmpPath);
            @unlink($tmpPath);
            return response($content, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Fallback: direct generation (logo may be missing without GD).
        $data = match ($type) {
            'rent-payments' => ['payments' => \App\Models\Payment::with(['lease.property', 'lease.unit', 'tenant'])
                ->whereHas('lease', fn($q) => $q->where('landlord_id', $landlordId))
                ->orderBy('due_date', 'desc')->get()],
            'tenants'       => ['tenants' => \App\Models\User::with(['leasesAsTenant' => fn($q) => $q->where('landlord_id', $landlordId)->with('property', 'unit')->latest()])
                ->where('role', 'tenant')->where('landlord_id', $landlordId)->orderBy('name')->get()],
            'overdue'       => ['payments' => \App\Models\Payment::with(['lease.property', 'lease.unit', 'tenant'])
                ->whereHas('lease', fn($q) => $q->where('landlord_id', $landlordId))
                ->where(fn($q) => $q->where('status', 'overdue')->orWhere(fn($q2) => $q2->where('status', 'pending')->where('due_date', '<', now()->toDateString())))
                ->orderBy('due_date', 'asc')->get()],
            'properties'    => ['properties' => \App\Models\Property::with('units')->where('landlord_id', $landlordId)->orderBy('name')->get()],
        };

        return Pdf::loadView("landlord.reports.pdf.{$type}", $data)
                  ->setPaper('a4', 'portrait')
                  ->download($filename);
    }

    // ─────────────────────── SETTINGS ─────────────────────────

    public function settingsIndex()
    {
        return view('landlord.settings', ['user' => Auth::user()]);
    }

    public function settingsUpdateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'required|email|max:150|unique:users,email,' . $user->id,
            'phone'           => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $validated['profile_picture'] = $request->file('profile_picture')
                ->store('avatars', 'public');
        } else {
            unset($validated['profile_picture']);
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function settingsUpdatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->with('section', 'security');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function settingsUpdateNotifications(Request $request)
    {
        $user = Auth::user();
        $prefs = $user->preferences ?? [];

        $prefs['notify_rent_due']       = $request->boolean('notify_rent_due');
        $prefs['notify_late_payment']   = $request->boolean('notify_late_payment');
        $prefs['notify_lease_expiry']   = $request->boolean('notify_lease_expiry');

        $user->update(['preferences' => $prefs]);

        return back()->with('success', 'Notification preferences saved.');
    }

    public function settingsRemovePicture()
    {
        $user = Auth::user();
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->update(['profile_picture' => null]);
        }
        return back()->with('success', 'Profile picture removed.');
    }

    public function settingsUpdatePreferences(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark',
        ]);

        $user = Auth::user();
        $prefs = $user->preferences ?? [];
        $prefs['theme'] = $request->theme;

        $user->update(['preferences' => $prefs]);

        return back()->with('success', 'Preferences saved.');
    }

    // ─────────────────────── HELPERS ──────────────────────────

    private function authorizeProperty(Property $property): void
    {
        abort_if($property->landlord_id !== Auth::id(), 403);
    }
}
