<?php

namespace App\Http\Controllers;

use App\Mail\TenantInvitation;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;

class LandlordController extends Controller
{
    // ─────────────────────── DASHBOARD ───────────────────────

    public function dashboard()
    {
        $landlord   = Auth::user();
        $properties = Property::where('landlord_id', $landlord->id)->with('activeLease.tenant')->get();

        $totalRevenue   = Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlord->id))
                                  ->where('status', 'paid')->sum('amount');

        $upcomingRent   = Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlord->id))
                                  ->where('status', 'pending')
                                  ->where('due_date', '>=', now())
                                  ->sum('amount');

        $overdueAmount  = Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlord->id))
                                  ->where('status', 'overdue')->sum('amount');

        // Collection stats for current month
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();
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

        // Chart data: income & expenses for last 6 months
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
            $date  = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();

            $months[]   = $date->format('M');
            $income[]   = (float) Payment::whereHas('lease', fn($q) => $q->where('landlord_id', $landlordId))
                                          ->where('status', 'paid')
                                          ->whereBetween('paid_date', [$start, $end])
                                          ->sum('amount');
            $expenses[] = 0; // Placeholder – extend when expenses table is added
        }

        return compact('months', 'income', 'expenses');
    }

    // ─────────────────────── PROPERTIES ───────────────────────

    public function propertiesIndex()
    {
        $landlord   = Auth::user();
        $properties = Property::where('landlord_id', $landlord->id)->with('activeLease.tenant')->get();

        $stats = [
            'total'    => $properties->count(),
            'occupied' => $properties->where('status', 'occupied')->count(),
            'available'=> $properties->where('status', 'available')->count(),
        ];

        return view('landlord.properties.index', compact('properties', 'stats'));
    }

    public function propertiesCreate()
    {
        return view('landlord.properties.create');
    }

    public function storeProperty(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'required|string|max:255',
            'city'        => 'nullable|string|max:100',
            'county'      => 'nullable|string|max:100',
            'total_area'  => 'nullable|numeric|min:0',
            'type'        => 'required|in:apartment,house,condo,studio,commercial',
            'bedrooms'    => 'required|integer|min:0',
            'bathrooms'   => 'required|integer|min:1',
            'rent_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['landlord_id'] = Auth::id();
        $property = Property::create($validated);

        return redirect()->route('landlord.properties.show', $property)
                         ->with('success', 'Property added successfully.');
    }

    public function propertiesShow(Property $property)
    {
        $this->authorizeProperty($property);
        $property->load('activeLease.tenant');

        $tenants = User::where('role', 'tenant')->get();

        return view('landlord.properties.show', compact('property', 'tenants'));
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

        $validated['property_id']       = $property->id;
        $validated['landlord_id']       = Auth::id();
        $validated['tenant_id']         = null;
        $validated['status']            = 'active';
        $validated['security_deposit']  = $validated['security_deposit'] ?? 0;

        Lease::create($validated);

        $property->update(['status' => 'occupied']);

        return redirect()->route('landlord.properties.show', $property)
                         ->with('success', 'Lease created. Now link a tenant to complete setup.')
                         ->with('open_link_tenant', true);
    }

    // ─────────────────────── LEASES INDEX ─────────────────────

    public function leasesIndex()
    {
        $landlordId = Auth::id();

        $leases = Lease::where('landlord_id', $landlordId)
            ->with('property', 'tenant')
            ->orderByRaw("FIELD(status,'active','pending','expired','terminated')")
            ->latest()
            ->get();

        $today = now()->startOfDay();

        $stats = [
            'total'          => $leases->count(),
            'active'         => $leases->where('status', 'active')->count(),
            'expiring_soon'  => $leases->filter(fn($l) =>
                $l->status === 'active' &&
                $l->end_date >= $today &&
                $l->end_date <= $today->copy()->addDays(30)
            )->count(),
            'expired'        => $leases->filter(fn($l) =>
                $l->status === 'active' && $l->end_date < $today
            )->count(),
        ];

        return view('landlord.leases.index', compact('leases', 'stats'));
    }

    // ─────────────────────── LINK TENANT ──────────────────────

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
                         ->with('success', 'Tenant linked successfully.')
                         ->with('show_invite', true)
                         ->with('invited_tenant_id', $request->tenant_id);
    }

    // ─────────────────────── TENANTS ──────────────────────────

    public function tenantsIndex()
    {
        $landlord    = Auth::user();
        $propertyIds = $landlord->properties()->pluck('id');

        $tenants = User::where('role', 'tenant')
                       ->whereHas('leasesAsTenant', fn($q) => $q->whereIn('property_id', $propertyIds))
                       ->with(['leasesAsTenant' => fn($q) => $q->whereIn('property_id', $propertyIds)->with('property')])
                       ->get();

        return view('landlord.tenants.index', compact('tenants'));
    }

    public function tenantsCreate(Request $request)
    {
        $fromProperty = $request->query('from_property');
        return view('landlord.tenants.create', compact('fromProperty'));
    }

    public function tenantsStore(Request $request)
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'notes'         => 'nullable|string',
        ]);

        $tenant = User::create([
            'name'     => $validated['first_name'] . ' ' . $validated['last_name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'role'     => 'tenant',
            'password' => Hash::make(\Illuminate\Support\Str::random(16)),
        ]);

        $this->sendInvitation($tenant);

        // If coming from a property flow, auto-link and redirect back
        $fromPropertyId = $request->input('from_property');
        if ($fromPropertyId) {
            $property = Property::where('id', $fromPropertyId)
                                 ->where('landlord_id', Auth::id())
                                 ->first();
            if ($property) {
                $lease = $property->activeLease;
                if ($lease) {
                    $lease->update(['tenant_id' => $tenant->id]);
                }
                $property->update(['status' => 'occupied']);
                return redirect()->route('landlord.properties.show', $property)
                                 ->with('success', 'Tenant added and linked to property.')
                                 ->with('show_invite', true)
                                 ->with('invited_tenant_id', $tenant->id);
            }
        }

        return redirect()->route('landlord.tenants.index')
                         ->with('success', 'Tenant added and invitation email sent.');
    }

    public function tenantInvite(User $user)
    {
        $this->sendInvitation($user);

        return back()->with('success', 'Invitation email sent to ' . $user->email . '.');
    }

    private function sendInvitation(User $tenant): void
    {
        $token      = Password::broker()->createToken($tenant);
        $setupUrl   = route('password.setup.show', ['token' => $token, 'email' => $tenant->email]);

        Mail::to($tenant->email)->send(new TenantInvitation($tenant, $setupUrl));
    }

    // ─────────────────────── MAINTENANCE ─────────────────────

    public function maintenanceIndex()
    {
        $landlordId = Auth::id();
        $base = MaintenanceRequest::whereHas('property', fn($q) => $q->where('landlord_id', $landlordId))
                                   ->with('property', 'tenant');

        $new       = (clone $base)->where('status', 'open')->latest()->get();
        $inProgress= (clone $base)->where('status', 'in_progress')->latest()->get();
        $completed = (clone $base)->whereIn('status', ['resolved', 'closed'])->latest()->get();

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
        return view('landlord.reports.index');
    }

    // ─────────────────────── HELPERS ──────────────────────────

    private function authorizeProperty(Property $property): void
    {
        abort_if($property->landlord_id !== Auth::id(), 403);
    }
}
