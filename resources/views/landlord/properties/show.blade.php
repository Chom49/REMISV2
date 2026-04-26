@extends('layouts.landlord')

@section('title', 'Property – ' . $property->name)

@section('content')

@php
    $hasLease  = $property->activeLease !== null;
    $hasTenant = $hasLease && $property->activeLease->tenant !== null && $property->activeLease->tenant->role === 'tenant';
@endphp

<div class="space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('landlord.properties.index') }}" class="hover:text-gray-700">Properties</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium">{{ $property->name }}</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Setup Progress (shown until fully configured) ───────── --}}
    @if (!$hasLease || !$hasTenant)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Setup Progress</p>
        <div class="flex items-center">

            {{-- Step 1: Property ✓ --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-emerald-600">Property</span>
            </div>

            <div class="flex-1 h-px mx-3 {{ $hasLease ? 'bg-emerald-300' : 'bg-gray-200' }}"></div>

            {{-- Step 2: Lease --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                @if ($hasLease)
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-emerald-600">Lease</span>
                @else
                    <div class="w-8 h-8 rounded-full bg-primary-500 ring-4 ring-primary-100 flex items-center justify-center">
                        <span class="text-white text-xs font-bold">2</span>
                    </div>
                    <span class="text-sm font-semibold text-primary-700">Add Lease</span>
                @endif
            </div>

            <div class="flex-1 h-px mx-3 {{ $hasTenant ? 'bg-emerald-300' : 'bg-gray-200' }}"></div>

            {{-- Step 3: Tenant --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                @if ($hasTenant)
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-emerald-600">Tenant</span>
                @elseif ($hasLease)
                    <div class="w-8 h-8 rounded-full bg-primary-500 ring-4 ring-primary-100 flex items-center justify-center">
                        <span class="text-white text-xs font-bold">3</span>
                    </div>
                    <span class="text-sm font-semibold text-primary-700">Link Tenant</span>
                @else
                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="text-gray-400 text-xs font-bold">3</span>
                    </div>
                    <span class="text-sm text-gray-400">Tenant</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ── Property Details Card ─────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $property->name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $property->address }}{{ $property->city ? ', ' . $property->city : '' }}{{ $property->county ? ', ' . $property->county : '' }}
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                {{ $hasTenant ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                {{ $hasTenant ? 'Occupied' : 'Vacant' }}
            </span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Type</p>
                <p class="text-sm font-semibold text-gray-800 capitalize">{{ $property->type ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Monthly Rent</p>
                <p class="text-sm font-semibold text-gray-800">Tshs {{ number_format($property->rent_amount, 0) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Total Area</p>
                <p class="text-sm font-semibold text-gray-800">{{ $property->total_area ? number_format($property->total_area, 0) . ' m²' : '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">City / Region</p>
                <p class="text-sm font-semibold text-gray-800">{{ $property->city ?? $property->county ?? '—' }}</p>
            </div>
        </div>

        @if($property->description)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Description</p>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $property->description }}</p>
            </div>
        @endif
    </div>

    {{-- ── Next Step: Add Lease ────────────────────────────────── --}}
    @if (!$hasLease)
    <div class="bg-white rounded-2xl border-2 border-dashed border-primary-200 p-6">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-primary-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Create a Lease Contract</p>
                <p class="text-xs text-gray-400">Define the rental terms for this property</p>
            </div>
        </div>
        <a href="{{ route('landlord.properties.leases.create', $property) }}"
           class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Lease
        </a>
    </div>

    {{-- ── Next Step: Link Tenant (lease exists, no tenant yet) ── --}}
    @elseif (!$hasTenant)
    <div class="bg-white rounded-2xl border-2 border-dashed border-primary-200 p-6">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-primary-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Link a Tenant</p>
                <p class="text-xs text-gray-400">Assign a tenant to complete the lease setup</p>
            </div>
        </div>
        <button onclick="document.getElementById('link-tenant-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Link Tenant
        </button>
    </div>

    {{-- ── Active Lease + Tenant (fully configured) ───────────── --}}
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-gray-900">Active Lease</h2>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">Active</span>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">Tenant</p>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($property->activeLease->tenant->name, 0, 2)) }}
                    </div>
                    <p class="text-sm font-semibold text-gray-800">{{ $property->activeLease->tenant->name }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Monthly Rent</p>
                <p class="text-sm font-semibold text-gray-800">Tshs {{ number_format($property->activeLease->monthly_rent, 0) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Lease Start</p>
                <p class="text-sm font-semibold text-gray-800">{{ $property->activeLease->start_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Lease End</p>
                <p class="text-sm font-semibold text-gray-800">{{ $property->activeLease->end_date->format('d M Y') }}</p>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ===== LINK TENANT MODAL ===== --}}
<div id="link-tenant-modal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
     role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6">

        <h2 class="text-lg font-bold text-gray-900 mb-1">Link Tenant</h2>
        <p class="text-xs text-gray-400 mb-4">Select an existing tenant or add a new one</p>

        @if($tenants->isEmpty())
            {{-- No tenants exist yet --}}
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700 mb-1">No tenants yet</p>
                <p class="text-xs text-gray-400 mb-4">Add a new tenant and they'll be automatically linked to this property.</p>
                <a href="{{ route('landlord.tenants.create', ['from_property' => $property->id]) }}"
                   class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Tenant
                </a>
            </div>
        @else
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-gray-500">Select tenant</span>
                <a href="{{ route('landlord.tenants.create', ['from_property' => $property->id]) }}"
                   class="text-xs text-primary-600 font-semibold hover:underline">+ Add New</a>
            </div>

            <form method="POST" action="{{ route('landlord.properties.link-tenant', $property) }}" id="link-form">
                @csrf
                <div class="space-y-2 max-h-60 overflow-y-auto mb-4">
                    @foreach($tenants as $tenant)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-primary-200 hover:bg-primary-50 cursor-pointer transition-colors">
                            <input type="radio" name="tenant_id" value="{{ $tenant->id }}" class="text-primary-600 focus:ring-primary-500">
                            <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-sm font-bold text-primary-700 flex-shrink-0">
                                {{ strtoupper(substr($tenant->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $tenant->name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $tenant->email }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('link-tenant-modal').classList.add('hidden')"
                            class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2">Cancel</button>
                    <button type="submit"
                            class="bg-primary-500 hover:bg-primary-600 text-white font-semibold px-6 py-2 rounded-xl text-sm transition-colors">
                        Link Tenant
                    </button>
                </div>
            </form>
        @endif

        @if($tenants->isEmpty())
        <div class="flex justify-end pt-4 border-t border-gray-100 mt-4">
            <button type="button" onclick="document.getElementById('link-tenant-modal').classList.add('hidden')"
                    class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2">Cancel</button>
        </div>
        @endif
    </div>
</div>

{{-- ===== INVITE MODAL ===== --}}
@if(session('show_invite'))
<div id="invite-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
     role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-primary-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-900 mb-2">Send Invitation?</h2>
        <p class="text-sm text-gray-500 mb-6">Would you like to send this tenant an email invitation to create their portal account?</p>
        <div class="flex items-center justify-center gap-3">
            <button onclick="document.getElementById('invite-modal').classList.add('hidden')"
                    class="text-sm text-gray-600 hover:text-gray-800 font-medium px-5 py-2.5 border border-gray-200 rounded-xl">
                Skip
            </button>
            <form method="POST" action="{{ route('landlord.tenants.invite', session('invited_tenant_id')) }}">
                @csrf
                <button type="submit"
                        class="bg-primary-500 hover:bg-primary-600 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                    Send Invite
                </button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.getElementById('link-tenant-modal')?.addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});

// Auto-open link tenant modal if redirected after lease creation
@if(session('open_link_tenant'))
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('link-tenant-modal').classList.remove('hidden');
    });
@endif
</script>
@endpush
