@extends('layouts.landlord')
@section('title', 'Tenants')

@section('content')

<div class="space-y-6">

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tenants</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage leases, renewals, and terminations</p>
        </div>
        <a href="{{ route('landlord.tenants.create') }}"
           class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                  text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm
                  self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Tenant
        </a>
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
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ session('warning') }}
        </div>
    @endif

    {{-- ── Stats Cards ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Total</p>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-1">All tenants</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wide mb-2">Active</p>
            <p class="text-3xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
            <p class="text-xs text-gray-400 mt-1">With active lease</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-amber-500 uppercase tracking-wide mb-2">Expiring</p>
            <p class="text-3xl font-bold text-amber-600">{{ $stats['expiring_soon'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Within 30 days</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">No Lease</p>
            <p class="text-3xl font-bold text-gray-500">{{ $stats['inactive'] }}</p>
            <p class="text-xs text-gray-400 mt-1">No active lease</p>
        </div>
    </div>

    {{-- ── Expiry Alert Banner ───────────────────────────────────── --}}
    @if($stats['expiring_soon'] > 0)
    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-800">
                {{ $stats['expiring_soon'] }} {{ Str::plural('lease', $stats['expiring_soon']) }} expiring within 30 days
            </p>
            <p class="text-xs text-amber-600 mt-0.5">Review and renew leases to avoid disruptions.</p>
        </div>
    </div>
    @endif

    {{-- ── Table Card ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-gray-100">

            {{-- Filter tabs --}}
            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                <button onclick="filterTenants('all')" id="tab-all"
                        class="px-3 py-1.5 rounded-lg bg-white text-gray-800 shadow-sm text-sm font-medium transition-all">All</button>
                <button onclick="filterTenants('active')" id="tab-active"
                        class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-700 text-sm font-medium transition-all">Active</button>
                <button onclick="filterTenants('inactive')" id="tab-inactive"
                        class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-700 text-sm font-medium transition-all">Inactive</button>
                <button onclick="filterTenants('expiring')" id="tab-expiring"
                        class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-700 text-sm font-medium transition-all">Expiring</button>
            </div>

            {{-- Search --}}
            <div class="relative sm:ml-auto sm:w-72">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="tenant-search" type="text" placeholder="Search by name or email…"
                       oninput="searchTenants(this.value)"
                       class="w-full bg-white pl-10 pr-4 py-2.5 text-sm text-gray-800 border border-gray-200
                              rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2
                              focus:ring-primary-400 focus:border-primary-400 transition">
            </div>
        </div>

        {{-- Empty state --}}
        @if($tenants->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-gray-600 font-semibold">No tenants yet</p>
                <p class="text-sm text-gray-400 mt-1">Add a tenant and link them to a lease.</p>
                <a href="{{ route('landlord.tenants.create') }}"
                   class="mt-4 inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                          text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
                    Add First Tenant
                </a>
            </div>
        @else

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Tenant</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden sm:table-cell">Unit / Property</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden md:table-cell">Lease</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden lg:table-cell">Lease End</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Invitation</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="tenants-body">
                    @foreach($tenants as $tenant)
                    @php
                        $activeLease = $tenant->leasesAsTenant->firstWhere('status', 'active');
                        $latestLease = $tenant->leasesAsTenant->first();
                        $lease       = $activeLease ?? $latestLease;
                        $tStatus     = $tenant->tenant_status ?? 'active';
                        $daysLeft    = $activeLease ? now()->startOfDay()->diffInDays($activeLease->end_date, false) : null;
                        $isExpiring  = $activeLease && $daysLeft >= 0 && $daysLeft <= 30;
                        $isOverdue   = $activeLease && $daysLeft !== null && $daysLeft < 0;

                        $leaseStatusClass = match(true) {
                            !$activeLease => 'bg-gray-100 text-gray-500',
                            $isOverdue    => 'bg-red-50 text-red-700',
                            $isExpiring   => 'bg-amber-50 text-amber-700',
                            default       => 'bg-emerald-50 text-emerald-700',
                        };
                        $leaseStatusLabel = match(true) {
                            !$activeLease && $lease?->status === 'terminated' => 'Terminated',
                            !$activeLease && $lease?->status === 'renewed'    => 'Renewed',
                            !$activeLease && $lease?->status === 'expired'    => 'Expired',
                            !$activeLease                                     => 'No Lease',
                            $isOverdue                                        => 'Overdue',
                            $isExpiring                                       => 'Expiring Soon',
                            default                                           => 'Active',
                        };

                        $invStatus = $tenant->invitation_status;
                        $invBadgeClass = match($invStatus) {
                            'accepted' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            'invited'  => 'bg-blue-50 text-blue-700 border border-blue-200',
                            default    => 'bg-gray-100 text-gray-500',
                        };
                        $invBadgeLabel = match($invStatus) {
                            'accepted' => 'Accepted',
                            'invited'  => 'Invited',
                            default    => 'Not Sent',
                        };

                        $filterAttr = match(true) {
                            $isExpiring                => 'expiring active',
                            $tStatus === 'inactive'    => 'inactive',
                            $tStatus === 'blacklisted' => 'inactive',
                            $activeLease !== null      => 'active',
                            default                    => 'inactive',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/70 transition-colors tenant-row"
                        data-filter="{{ $filterAttr }}"
                        data-name="{{ strtolower($tenant->name) }}"
                        data-email="{{ strtolower($tenant->email) }}">

                        {{-- Tenant --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-500 flex items-center justify-center
                                            text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $tenant->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $tenant->email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Unit / Property --}}
                        <td class="px-5 py-4 hidden sm:table-cell">
                            @if($lease && $lease->unit)
                                <p class="font-semibold text-gray-800">Unit {{ $lease->unit->unit_number }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $lease->property->name ?? '—' }}</p>
                            @elseif($lease)
                                <p class="text-sm text-gray-600">{{ $lease->property->name ?? '—' }}</p>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-400">
                                    No Active Lease
                                </span>
                            @endif
                        </td>

                        {{-- Lease Status --}}
                        <td class="px-5 py-4 hidden md:table-cell">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $leaseStatusClass }}">
                                {{ $leaseStatusLabel }}
                            </span>
                        </td>

                        {{-- Lease End --}}
                        <td class="px-5 py-4 hidden lg:table-cell">
                            @if($activeLease)
                                <p class="text-gray-700 font-medium">{{ $activeLease->end_date->format('d M Y') }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">from {{ $activeLease->start_date->format('d M Y') }}</p>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Invitation Status --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $invBadgeClass }}">
                                @if($invStatus === 'accepted')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($invStatus === 'invited')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                @else
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                                {{ $invBadgeLabel }}
                            </span>
                        </td>

                        {{-- Actions dropdown --}}
                        <td class="px-5 py-4 text-right">
                            <div class="relative inline-block" x-data="{ open: false }">

                                {{-- Trigger --}}
                                <button @click="open = !open"
                                        @click.outside="open = false"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl
                                               border border-gray-300 bg-white hover:bg-gray-50
                                               hover:border-gray-400 text-gray-700 text-xs font-semibold
                                               shadow-sm transition-all focus:outline-none
                                               focus:ring-2 focus:ring-primary-300 focus:ring-offset-1">
                                    Actions
                                    <svg class="w-3.5 h-3.5 text-gray-500 transition-transform duration-150"
                                         :class="open ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                {{-- Dropdown panel --}}
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl
                                            shadow-xl border border-gray-100 z-30 py-1.5 overflow-hidden"
                                     style="display:none;">

                                    @if($activeLease)
                                    {{-- ── Has active lease ── --}}
                                    <a href="{{ route('landlord.leases.show', $lease) }}"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700
                                              hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        View Lease
                                    </a>

                                    <a href="{{ route('landlord.tenants.show', $tenant) }}#payments"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700
                                              hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Payment History
                                    </a>

                                    <div class="my-1 mx-3 border-t border-gray-100"></div>

                                    <button onclick="openRenewModal({{ $activeLease->id }}, '{{ addslashes($tenant->name) }}', '{{ $activeLease->end_date->format('Y-m-d') }}', {{ $activeLease->monthly_rent }})"
                                            class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm
                                                   text-emerald-700 hover:bg-emerald-50 transition-colors">
                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Renew Lease
                                    </button>

                                    <button onclick="openTerminateModal({{ $activeLease->id }}, '{{ addslashes($tenant->name) }}', '{{ $lease->unit?->unit_number ?? '' }}')"
                                            class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm
                                                   text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        Terminate Lease
                                    </button>

                                    @elseif($lease)
                                    {{-- ── Has past lease (no active) ── --}}
                                    <a href="{{ route('landlord.leases.show', $lease) }}"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700
                                              hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        View Last Lease
                                    </a>

                                    <a href="{{ route('landlord.tenants.show', $tenant) }}#payments"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700
                                              hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Payment History
                                    </a>

                                    @else
                                    {{-- ── No lease at all ── --}}
                                    <a href="{{ route('landlord.tenants.show', $tenant) }}"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700
                                              hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        View Profile
                                    </a>

                                    <a href="{{ route('landlord.tenants.edit', $tenant) }}"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700
                                              hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit Profile
                                    </a>
                                    @endif

                                    {{-- Resend Invitation — always shown --}}
                                    <div class="my-1 mx-3 border-t border-gray-100"></div>
                                    <div class="px-3 pt-1.5 pb-2">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-2 px-1">Resend Invitation</p>
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('landlord.tenants.invite', $tenant) }}" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="channel" value="email">
                                                <button type="submit"
                                                        class="w-full flex items-center justify-center gap-1.5 px-2 py-2 text-xs font-semibold
                                                               text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    Email
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('landlord.tenants.invite', $tenant) }}" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="channel" value="sms">
                                                <button type="submit"
                                                        class="w-full flex items-center justify-center gap-1.5 px-2 py-2 text-xs font-semibold
                                                               text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
                                                    </svg>
                                                    SMS
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Showing <span id="visible-count">{{ $tenants->count() }}</span>
                of {{ $tenants->count() }} {{ Str::plural('tenant', $tenants->count()) }}
            </p>
        </div>
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════
     TERMINATE LEASE MODAL
════════════════════════════════════════════════════════ --}}
<div id="terminate-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">

        <div class="flex items-start gap-4 p-6 border-b border-gray-100">
            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Terminate Lease</h2>
                <p id="terminate-subtitle" class="text-sm text-gray-500 mt-0.5">This action cannot be undone.</p>
            </div>
        </div>

        <div class="mx-6 mt-5">
            <div class="flex items-start gap-2.5 bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm text-red-700">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>Lease → <strong>Terminated</strong> · Tenant → <strong>Inactive</strong> · Unit → <strong>Vacant</strong>. All records are preserved.</span>
            </div>
        </div>

        <form id="terminate-form" method="POST" action="" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Reason for Termination <span class="text-red-400">*</span>
                </label>
                <div class="space-y-2">
                    @foreach([
                        'failed_to_pay'    => 'Failed to pay rent',
                        'misconduct'       => 'Misconduct / property damage',
                        'lease_violation'  => 'Lease violation',
                        'tenant_moved_out' => 'Tenant requested move-out',
                        'other'            => 'Other',
                    ] as $value => $label)
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200
                                  hover:border-red-200 hover:bg-red-50/30 cursor-pointer transition-colors">
                        <input type="radio" name="termination_reason" value="{{ $value }}" required
                               class="w-4 h-4 text-red-500 border-gray-300 focus:ring-red-400 cursor-pointer">
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Notes <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea name="termination_notes" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-400
                                 focus:ring-2 focus:ring-red-100 outline-none transition text-sm resize-none
                                 placeholder-gray-400"
                          placeholder="Add any additional context…"></textarea>
            </div>

            @include('landlord.leases._termination_notice')

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" onclick="closeTerminateModal()"
                        class="text-sm font-semibold text-gray-600 hover:text-gray-800 px-5 py-2.5
                               border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white
                               font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Confirm Termination
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     RENEW LEASE MODAL
════════════════════════════════════════════════════════ --}}
<div id="renew-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">

        <div class="flex items-start gap-4 p-6 border-b border-gray-100">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Renew Lease</h2>
                <p id="renew-subtitle" class="text-sm text-gray-500 mt-0.5">Extend the lease contract</p>
            </div>
        </div>

        <form id="renew-form" method="POST" action="">
            @csrf
            <div class="px-6 pt-5 pb-2 space-y-5">

                <p id="renew-current-end" class="text-xs text-gray-500 bg-emerald-50 border border-emerald-100
                                                  rounded-xl px-4 py-3 font-medium text-emerald-700"></p>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        New Lease End Date <span class="text-red-400 normal-case font-normal">*</span>
                    </label>
                    <input type="date" name="end_date" id="renew-end-date" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800
                                  focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Updated Monthly Rent
                    </label>
                    <div class="flex rounded-xl border border-gray-200 overflow-hidden
                                focus-within:ring-2 focus-within:ring-emerald-400 focus-within:border-emerald-400 transition-all">
                        <span class="flex items-center px-4 py-3 bg-gray-50 text-sm font-semibold text-gray-500
                                     border-r border-gray-200 select-none whitespace-nowrap">
                            Tshs
                        </span>
                        <input type="number" name="monthly_rent" id="renew-rent" min="0" step="1000"
                               class="flex-1 px-4 py-3 text-sm text-gray-800 bg-white outline-none min-w-0"
                               placeholder="Leave blank to keep current rent">
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Leave blank to keep current rent for the new term.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Updated Lease Terms
                        <span class="text-gray-400 normal-case font-normal ml-1">(optional)</span>
                    </label>
                    <textarea name="lease_terms" rows="3"
                              placeholder="Add or update any terms for the renewed lease…"
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800
                                     placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-400
                                     focus:border-emerald-400 resize-none transition"></textarea>
                </div>
            </div>

            <div class="px-6 pt-4 pb-6 space-y-2.5">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2.5 bg-emerald-600
                               hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold
                               px-6 py-3.5 rounded-xl text-sm shadow-lg shadow-emerald-500/25
                               hover:shadow-xl hover:shadow-emerald-500/35 hover:-translate-y-px
                               transition-all duration-200">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Save Lease Renewal
                </button>
                <button type="button" onclick="closeRenewModal()"
                        class="w-full py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700
                               hover:bg-gray-50 rounded-xl transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
/* ── Filter tabs ─────────────────────────────── */
let activeFilter = 'all';

function filterTenants(filter) {
    activeFilter = filter;
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.className = 'px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-700 text-sm font-medium transition-all';
    });
    const active = document.getElementById('tab-' + filter);
    if (active) active.className = 'px-3 py-1.5 rounded-lg bg-white text-gray-800 shadow-sm text-sm font-medium transition-all';
    applyFilters();
}

function searchTenants(query) { applyFilters(query.toLowerCase()); }

function applyFilters(q = null) {
    const search = q ?? document.getElementById('tenant-search').value.toLowerCase();
    const rows   = document.querySelectorAll('.tenant-row');
    let visible  = 0;
    rows.forEach(row => {
        const ok = (activeFilter === 'all' || row.dataset.filter.includes(activeFilter))
                && (!search || row.dataset.name.includes(search) || row.dataset.email.includes(search));
        row.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    const el = document.getElementById('visible-count');
    if (el) el.textContent = visible;
}

const appBaseUrl = '{{ rtrim(url('/'), '/') }}';

/* ── Terminate Modal ─────────────────────────── */
function openTerminateModal(leaseId, tenantName, unit) {
    const modal    = document.getElementById('terminate-modal');
    const form     = document.getElementById('terminate-form');
    const subtitle = document.getElementById('terminate-subtitle');
    form.action    = `${appBaseUrl}/landlord/leases/${leaseId}/terminate`;
    subtitle.textContent = `Terminating lease for ${tenantName}${unit ? ' — Unit ' + unit : ''}`;
    modal.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
    modal.querySelector('textarea[name="termination_notes"]').value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeTerminateModal() {
    const m = document.getElementById('terminate-modal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

/* ── Renew Modal ─────────────────────────────── */
function openRenewModal(leaseId, tenantName, currentEndDate, currentRent) {
    const modal    = document.getElementById('renew-modal');
    const form     = document.getElementById('renew-form');
    const subtitle = document.getElementById('renew-subtitle');
    const endInput = document.getElementById('renew-end-date');
    const rentInput= document.getElementById('renew-rent');
    const hint     = document.getElementById('renew-current-end');
    form.action    = `${appBaseUrl}/landlord/leases/${leaseId}/renew`;
    if (subtitle) subtitle.textContent = `Renewing lease for ${tenantName}`;
    const nextDay = new Date(currentEndDate);
    nextDay.setDate(nextDay.getDate() + 1);
    endInput.min   = nextDay.toISOString().split('T')[0];
    endInput.value = '';
    rentInput.value = currentRent || '';
    if (hint) {
        hint.textContent = `Current lease ends ${currentEndDate} · Current rent: Tshs ${Number(currentRent).toLocaleString()}`;
        hint.classList.toggle('hidden', !currentEndDate);
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeRenewModal() {
    const m = document.getElementById('renew-modal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

/* ── Close on backdrop click ─────────────────── */
['terminate-modal', 'renew-modal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) { this.classList.add('hidden'); this.classList.remove('flex'); }
    });
});
</script>
@endpush
