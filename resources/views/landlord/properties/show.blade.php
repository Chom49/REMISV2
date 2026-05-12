@extends('layouts.landlord')

@section('title', $property->name)

@section('content')

@php
    $units      = $property->units;
    $leases     = $property->leases->sortByDesc('created_at');
    $activeLease = $property->leases->where('status', 'active')->first();
    $hasTenants = $units->filter(fn($u) => $u->status === 'occupied')->count() > 0;
    $tab        = $activeTab ?? 'overview';

    // For assign-tenant modal
    $openAssignLease = session('open_assign_tenant');
    if ($openAssignLease) {
        $assignLease = $leases->firstWhere('id', $openAssignLease);
        $assignUnit  = $assignLease ? $units->firstWhere('id', $assignLease->unit_id) : null;
    }
@endphp

<div class="space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('landlord.properties.index') }}" class="hover:text-gray-700 transition-colors">Properties</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">{{ $property->name }}</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Property Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($property->image)
            <div class="h-40 sm:h-52 overflow-hidden">
                <img src="{{ Storage::url($property->image) }}" alt="{{ $property->name }}"
                     class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex items-start gap-4">
                    @if(!$property->image)
                    <div class="w-14 h-14 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-7 h-7 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl font-bold text-gray-900">{{ $property->name }}</h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $property->status === 'occupied' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ ucfirst($property->status) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ $property->property_category === 'multi' ? 'Multi Unit' : 'Single Unit' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $property->address }}{{ $property->city ? ', ' . $property->city : '' }}{{ $property->county ? ', ' . $property->county : '' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-5 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Total Units</p>
                    <p class="text-lg font-bold text-gray-900">{{ $units->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Occupied</p>
                    <p class="text-lg font-bold text-emerald-600">{{ $units->where('status', 'occupied')->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Vacant</p>
                    <p class="text-lg font-bold text-amber-500">{{ $units->where('status', 'vacant')->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Total Area</p>
                    <p class="text-lg font-bold text-gray-900">{{ $property->total_area ? number_format($property->total_area, 0) . ' m²' : '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tab Navigation ──────────────────────────────────── --}}
    <div class="flex gap-1 overflow-x-auto bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5">
        @foreach([
            ['key' => 'overview',     'label' => 'Overview'],
            ['key' => 'units',        'label' => 'Units / Spaces'],
            ['key' => 'leases',       'label' => 'Leases'],
            ['key' => 'tenants',      'label' => 'Tenants'],
            ['key' => 'payments',     'label' => 'Payments'],
            ['key' => 'maintenance',  'label' => 'Maintenance'],
        ] as $t)
            <a href="{{ route('landlord.properties.show', [$property, 'tab' => $t['key']]) }}"
               class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      {{ $tab === $t['key']
                          ? 'bg-primary-500 text-white shadow-sm'
                          : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                {{ $t['label'] }}
            </a>
        @endforeach
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB: OVERVIEW
    ════════════════════════════════════════════════════════ --}}
    @if($tab === 'overview')
    <div class="space-y-5">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 mb-4">Property Details</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-5 gap-x-6">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Category</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $property->property_category === 'multi' ? 'Multi Unit' : 'Single Unit' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Address</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $property->address ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">City</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $property->city ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Region</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $property->county ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Total Area</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $property->total_area ? number_format($property->total_area, 0) . ' m²' : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Status</p>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $property->status === 'occupied' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ ucfirst($property->status) }}
                    </span>
                </div>
            </div>

            @if($property->description)
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-1">Description</p>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $property->description }}</p>
                </div>
            @endif
        </div>

        {{-- Occupancy snapshot --}}
        @if($units->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 mb-4">Occupancy Snapshot</h2>
            @php
                $occCount = $units->where('status', 'occupied')->count();
                $vacCount = $units->where('status', 'vacant')->count();
                $total    = $units->count();
                $occPct   = $total > 0 ? round($occCount / $total * 100) : 0;
            @endphp
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-500">Occupancy rate</span>
                <span class="font-bold text-gray-900">{{ $occPct }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2.5 mb-4">
                <div class="bg-emerald-400 h-2.5 rounded-full transition-all"
                     style="width: {{ $occPct }}%"></div>
            </div>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-2xl font-bold text-gray-900">{{ $total }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Total</p>
                </div>
                <div class="bg-emerald-50 rounded-xl p-3">
                    <p class="text-2xl font-bold text-emerald-600">{{ $occCount }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Occupied</p>
                </div>
                <div class="bg-amber-50 rounded-xl p-3">
                    <p class="text-2xl font-bold text-amber-500">{{ $vacCount }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Vacant</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB: UNITS / SPACES
    ════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'units')
    @php
        $isMultiFloor = $property->floor_layout === 'multi_floor';
        $floorGroups  = $isMultiFloor
            ? $units->sortBy('floor_number')->groupBy('floor_number')
            : collect(['All Units' => $units]);
    @endphp
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">Units / Spaces
                <span class="ml-2 text-xs font-normal text-gray-400">({{ $units->count() }} total)</span>
            </h2>
        </div>

        @if($units->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">No units found</p>
                <p class="text-xs text-gray-400 mt-1">This property has no units yet.</p>
            </div>

        @elseif($isMultiFloor)
            {{-- ── Multi-floor summary bar ────────────────────────────────── --}}
            @php
                $mfOccupied = $units->where('status', 'occupied')->count();
                $mfVacant   = $units->where('status', 'vacant')->count();
                $mfFloors   = $floorGroups->count();
            @endphp
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 text-xs font-semibold text-gray-700">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    {{ $units->count() }} Total Units
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-xs font-semibold text-emerald-700 border border-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    {{ $mfOccupied }} Occupied
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-xs font-semibold text-amber-700 border border-amber-100">
                    <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                    {{ $mfVacant }} Vacant
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 text-xs font-semibold text-primary-700 border border-primary-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                    </svg>
                    {{ $mfFloors }} {{ Str::plural('Floor', $mfFloors) }}
                </span>
            </div>

            {{-- ── Floor accordion sections ───────────────────────────────── --}}
            <div class="space-y-3" id="floors-accordion">
                @foreach($floorGroups as $floorName => $floorUnits)
                @php
                    $fOcc = $floorUnits->where('status', 'occupied')->count();
                    $fVac = $floorUnits->where('status', 'vacant')->count();
                    $fTotal = $floorUnits->count();
                    $floorKey = 'floor-' . Str::slug($floorName, '-');
                @endphp
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    {{-- Floor header (clickable) --}}
                    <div class="bg-primary-50 border border-primary-100 rounded-xl mx-2 mt-2 px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-primary-100 transition-colors"
                         onclick="toggleFloorAccordion('{{ $floorKey }}')"
                         id="{{ $floorKey }}-header">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-primary-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-primary-900">{{ $floorName ?: 'Unnamed Floor' }}</p>
                                <p class="text-xs text-primary-600 collapsed-summary hidden">{{ $fOcc }} occupied, {{ $fVac }} vacant</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="hidden sm:flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-white text-gray-700 border border-gray-200">
                                    {{ $fTotal }} {{ Str::plural('unit', $fTotal) }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    {{ $fOcc }} occ.
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                    {{ $fVac }} vac.
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-primary-500 transition-transform duration-200 chevron-icon" id="{{ $floorKey }}-chevron"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Floor body (collapsible) --}}
                    <div id="{{ $floorKey }}-body" class="px-2 pb-2 pt-3">
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($floorUnits as $unit)
                            @php
                                $uLease     = $unit->activeLease;
                                $uTenant    = $uLease?->tenant;
                                $isOccupied = $unit->status === 'occupied';
                                $hasLease   = $uLease !== null;
                                $hasTenant  = $uTenant !== null;
                            @endphp
                            <div class="bg-white rounded-2xl border {{ $isOccupied ? 'border-emerald-100' : 'border-gray-100' }} shadow-sm p-5 flex flex-col gap-4 hover:shadow-md transition-shadow">

                                {{-- Unit header --}}
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 rounded-xl {{ $isOccupied ? 'bg-emerald-50' : 'bg-amber-50' }} flex items-center justify-center font-bold text-sm
                                            {{ $isOccupied ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $unit->unit_number }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">Unit {{ $unit->unit_number }}</p>
                                            <p class="text-xs text-gray-400">ID #{{ $unit->id }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $isOccupied ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $isOccupied ? 'Occupied' : 'Vacant' }}
                                    </span>
                                </div>

                                @if($isOccupied && $hasTenant)
                                    <div class="flex items-center gap-3 bg-gray-50 rounded-xl px-3 py-2.5">
                                        <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($uTenant->name, 0, 2)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $uTenant->name }}</p>
                                            @if($uLease)
                                                <p class="text-xs text-gray-400">Rent: Tshs {{ number_format($uLease->monthly_rent, 0) }}/mo</p>
                                            @endif
                                        </div>
                                    </div>
                                    @if($uLease)
                                        <div class="text-xs text-gray-400 -mt-2">
                                            Lease: {{ $uLease->start_date->format('d M Y') }} – {{ $uLease->end_date->format('d M Y') }}
                                        </div>
                                    @endif

                                @elseif($hasLease && !$hasTenant)
                                    <div class="bg-blue-50 border border-blue-100 rounded-xl px-3 py-2.5 text-xs text-blue-700">
                                        Lease created — waiting for tenant assignment
                                    </div>
                                    <button onclick="openAssignModal({{ $uLease->id }}, {{ $unit->id }}, '{{ $unit->unit_number }}')"
                                            class="w-full flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Assign Tenant
                                    </button>

                                @else
                                    <a href="{{ route('landlord.properties.units.leases.create', [$property, $unit]) }}"
                                       class="w-full flex items-center justify-center gap-2 bg-primary-50 hover:bg-primary-100 text-primary-700 text-sm font-semibold px-4 py-2.5 rounded-xl border border-primary-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Create Lease
                                    </a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        @else
            {{-- ── Single floor / legacy: flat grid ──────────────────────── --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($units as $unit)
                @php
                    $uLease  = $unit->activeLease;
                    $uTenant = $uLease?->tenant;
                    $isOccupied = $unit->status === 'occupied';
                    $hasLease   = $uLease !== null;
                    $hasTenant  = $uTenant !== null;
                @endphp
                <div class="bg-white rounded-2xl border {{ $isOccupied ? 'border-emerald-100' : 'border-gray-100' }} shadow-sm p-5 flex flex-col gap-4 hover:shadow-md transition-shadow">

                    {{-- Unit header --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl {{ $isOccupied ? 'bg-emerald-50' : 'bg-amber-50' }} flex items-center justify-center font-bold text-sm
                                {{ $isOccupied ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $unit->unit_number }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Unit {{ $unit->unit_number }}</p>
                                <p class="text-xs text-gray-400">ID #{{ $unit->id }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $isOccupied ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ $isOccupied ? 'Occupied' : 'Vacant' }}
                        </span>
                    </div>

                    {{-- Occupied: show tenant info --}}
                    @if($isOccupied && $hasTenant)
                        <div class="flex items-center gap-3 bg-gray-50 rounded-xl px-3 py-2.5">
                            <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($uTenant->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $uTenant->name }}</p>
                                @if($uLease)
                                    <p class="text-xs text-gray-400">Rent: Tshs {{ number_format($uLease->monthly_rent, 0) }}/mo</p>
                                @endif
                            </div>
                        </div>
                        @if($uLease)
                            <div class="text-xs text-gray-400 -mt-2">
                                Lease: {{ $uLease->start_date->format('d M Y') }} – {{ $uLease->end_date->format('d M Y') }}
                            </div>
                        @endif

                    {{-- Has lease but no tenant yet --}}
                    @elseif($hasLease && !$hasTenant)
                        <div class="bg-blue-50 border border-blue-100 rounded-xl px-3 py-2.5 text-xs text-blue-700">
                            Lease created — waiting for tenant assignment
                        </div>
                        <button onclick="openAssignModal({{ $uLease->id }}, {{ $unit->id }}, '{{ $unit->unit_number }}')"
                                class="w-full flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Assign Tenant
                        </button>

                    {{-- Vacant: show create lease --}}
                    @else
                        <a href="{{ route('landlord.properties.units.leases.create', [$property, $unit]) }}"
                           class="w-full flex items-center justify-center gap-2 bg-primary-50 hover:bg-primary-100 text-primary-700 text-sm font-semibold px-4 py-2.5 rounded-xl border border-primary-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Lease
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB: LEASES
    ════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'leases')
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">Leases
                <span class="ml-2 text-xs font-normal text-gray-400">({{ $leases->count() }})</span>
            </h2>
        </div>

        @if($leases->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">No leases yet</p>
                <p class="text-xs text-gray-400 mt-1">Go to the Units tab to create a lease on a vacant unit.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Unit</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Tenant</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Rent / mo</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Period</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($leases as $lease)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 text-xs font-semibold text-gray-700">
                                    {{ $lease->unit?->unit_number ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($lease->tenant)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($lease->tenant->name, 0, 2)) }}
                                        </div>
                                        <span class="font-medium text-gray-800">{{ $lease->tenant->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 hidden sm:table-cell text-gray-700 font-medium">
                                Tshs {{ number_format($lease->monthly_rent, 0) }}
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell text-gray-500 text-xs">
                                {{ $lease->start_date->format('d M Y') }} – {{ $lease->end_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $lease->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ ucfirst($lease->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB: TENANTS
    ════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'tenants')
    @php
        $propertyTenants = $leases
            ->whereNotNull('tenant_id')
            ->pluck('tenant')
            ->filter()
            ->unique('id');
    @endphp
    <div class="space-y-4">
        <h2 class="text-base font-bold text-gray-900">Tenants
            <span class="ml-2 text-xs font-normal text-gray-400">({{ $propertyTenants->count() }})</span>
        </h2>

        @if($propertyTenants->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">No tenants yet</p>
                <p class="text-xs text-gray-400 mt-1">Create a lease on a vacant unit and assign a tenant.</p>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($propertyTenants as $tenant)
                @php
                    $tLease = $leases->where('tenant_id', $tenant->id)->sortByDesc('created_at')->first();
                    $tUnit  = $tLease?->unit;
                @endphp
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-full bg-primary-500 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr($tenant->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $tenant->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $tenant->email }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-xs text-gray-500">
                        @if($tUnit)
                            <div class="flex items-center justify-between">
                                <span>Unit</span>
                                <span class="font-semibold text-gray-700">{{ $tUnit->unit_number }}</span>
                            </div>
                        @endif
                        @if($tLease)
                            <div class="flex items-center justify-between">
                                <span>Monthly Rent</span>
                                <span class="font-semibold text-gray-700">Tshs {{ number_format($tLease->monthly_rent, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Lease Ends</span>
                                <span class="font-semibold text-gray-700">{{ $tLease->end_date->format('d M Y') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('landlord.tenants.show', $tenant) }}"
                           class="text-xs text-primary-600 hover:text-primary-800 font-medium transition-colors">
                            View Profile →
                        </a>
                        @php $invStatus = $tenant->invitation_status; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $invStatus === 'accepted' ? 'bg-emerald-50 text-emerald-700' :
                               ($invStatus === 'invited' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $invStatus === 'accepted' ? 'Accepted' : ($invStatus === 'invited' ? 'Invited' : 'Not Sent') }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB: PAYMENTS
    ════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'payments')
    @php
        $allPayments = \App\Models\Payment::whereIn('lease_id', $leases->pluck('id'))
                        ->with('lease.unit', 'tenant')
                        ->latest('due_date')
                        ->get();
    @endphp
    <div class="space-y-4">
        <h2 class="text-base font-bold text-gray-900">Payments
            <span class="ml-2 text-xs font-normal text-gray-400">({{ $allPayments->count() }})</span>
        </h2>

        @if($allPayments->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-sm font-semibold text-gray-700">No payments recorded</p>
                <p class="text-xs text-gray-400 mt-1">Payments will appear here once tenants have active leases.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Tenant</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Unit</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Amount</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Due Date</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($allPayments as $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-gray-800">{{ $payment->tenant?->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 hidden sm:table-cell">
                                <span class="text-gray-500">{{ $payment->lease?->unit?->unit_number ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-gray-800">
                                Tshs {{ number_format($payment->amount, 0) }}
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell text-gray-500 text-xs">
                                {{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $payment->status === 'paid' ? 'bg-emerald-50 text-emerald-700' :
                                       ($payment->status === 'overdue' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB: MAINTENANCE
    ════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'maintenance')
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">Maintenance Requests</h2>
        </div>

        @if($property->maintenanceRequests->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-sm font-semibold text-gray-700">No maintenance requests</p>
                <p class="text-xs text-gray-400 mt-1">Requests from tenants or logged by you will appear here.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($property->maintenanceRequests->sortByDesc('created_at') as $req)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <p class="text-sm font-bold text-gray-900">{{ $req->title }}</p>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $req->priority === 'urgent' ? 'bg-red-50 text-red-700' :
                                       ($req->priority === 'high' ? 'bg-orange-50 text-orange-700' :
                                       ($req->priority === 'medium' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                                    {{ ucfirst($req->priority) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">{{ Str::limit($req->description, 100) }}</p>
                            @if($req->tenant)
                                <p class="text-xs text-gray-400 mt-1">By: {{ $req->tenant->name }}</p>
                            @endif
                        </div>
                        <span class="flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $req->status === 'open' ? 'bg-amber-50 text-amber-700' :
                               ($req->status === 'in_progress' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700') }}">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

</div>

{{-- ═══════════════════════════════════════════════════════════
     ASSIGN TENANT MODAL
═══════════════════════════════════════════════════════════ --}}
<div id="assign-tenant-modal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
     role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-0.5">Assign Tenant</h2>
        <p class="text-xs text-gray-400 mb-5" id="assign-modal-subtitle">Select a tenant for this unit</p>

        <form method="POST" id="assign-tenant-form" action="">
            @csrf

            @if($tenants->isEmpty())
                <div class="py-6 text-center">
                    <p class="text-sm text-gray-500 mb-4">No tenants yet. Add a new tenant first.</p>
                    <a id="add-new-tenant-link"
                       href="{{ route('landlord.tenants.create') }}"
                       class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                        Add New Tenant
                    </a>
                </div>
            @else
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-gray-500">Select tenant</span>
                    <a id="add-new-tenant-link"
                       href="{{ route('landlord.tenants.create') }}"
                       class="text-xs text-primary-600 font-semibold hover:underline">+ Add New</a>
                </div>
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
                    <button type="button" onclick="closeAssignModal()"
                            class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2">Cancel</button>
                    <button type="submit"
                            class="bg-primary-500 hover:bg-primary-600 text-white font-semibold px-6 py-2 rounded-xl text-sm transition-colors">
                        Assign Tenant
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>


@endsection

@push('scripts')
<script>
const propertyId  = {{ $property->id }};
const appBaseUrl  = '{{ rtrim(url('/'), '/') }}';

function openAssignModal(leaseId, unitId, unitNumber) {
    const modal    = document.getElementById('assign-tenant-modal');
    const form     = document.getElementById('assign-tenant-form');
    const subtitle = document.getElementById('assign-modal-subtitle');
    const newLink  = document.getElementById('add-new-tenant-link');

    form.action = `${appBaseUrl}/landlord/properties/${propertyId}/units/${unitId}/leases/${leaseId}/assign-tenant`;

    subtitle.textContent = `Assigning tenant to Unit ${unitNumber}`;

    if (newLink) {
        newLink.href = `{{ route('landlord.tenants.create') }}?from_property=${propertyId}&from_unit=${unitId}&from_lease=${leaseId}`;
    }

    modal.classList.remove('hidden');
}

function closeAssignModal() {
    document.getElementById('assign-tenant-modal').classList.add('hidden');
}

// Close on backdrop click
document.getElementById('assign-tenant-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAssignModal();
});

// ─── Floor accordion toggle ──────────────────────────────────────────────────
function toggleFloorAccordion(floorKey) {
    const body    = document.getElementById(floorKey + '-body');
    const chevron = document.getElementById(floorKey + '-chevron');
    const header  = document.getElementById(floorKey + '-header');

    if (!body) return;

    const isCollapsed = body.classList.contains('hidden');

    if (isCollapsed) {
        // Expand
        body.classList.remove('hidden');
        chevron.style.transform = 'rotate(0deg)';
        header.querySelector('.collapsed-summary')?.classList.add('hidden');
    } else {
        // Collapse
        body.classList.add('hidden');
        chevron.style.transform = 'rotate(-90deg)';
        header.querySelector('.collapsed-summary')?.classList.remove('hidden');
    }
}

// Auto-open assign tenant modal after lease creation
@if(session('open_assign_tenant') && isset($assignLease) && isset($assignUnit))
document.addEventListener('DOMContentLoaded', () => {
    openAssignModal(
        {{ $assignLease->id }},
        {{ $assignUnit->id }},
        '{{ $assignUnit->unit_number }}'
    );
});
@endif
</script>
@endpush
