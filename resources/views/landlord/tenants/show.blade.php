@extends('layouts.landlord')
@section('title', $tenant->name . ' – Tenant Profile')

@section('content')

@php
    $activeLease  = $tenant->leasesAsTenant->firstWhere('status', 'active');
    $allLeases    = $tenant->leasesAsTenant;
    $maintenance  = $tenant->maintenanceRequests;
    // $payments, $totalPaid, $totalOverdue, $pendingCount injected from controller

    // Status reflects actual lease situation, not just the account flag
    $statusLabel = match(true) {
        $tenant->tenant_status === 'blacklisted'           => 'Blacklisted',
        $tenant->tenant_status === 'inactive'              => 'Inactive',
        $activeLease !== null                              => 'Active Tenant',
        default                                            => 'No Active Lease',
    };
    $statusBadge = match(true) {
        $tenant->tenant_status === 'blacklisted'           => 'bg-red-50 text-red-700 border border-red-200',
        $tenant->tenant_status === 'inactive'              => 'bg-gray-100 text-gray-600 border border-gray-200',
        $activeLease !== null                              => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        default                                            => 'bg-amber-50 text-amber-700 border border-amber-200',
    };

    $invStatus = $tenant->invitation_status;
    $invBadgeClass = match($invStatus) {
        'accepted' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'invited'  => 'bg-blue-50 text-blue-700 border border-blue-200',
        default    => 'bg-gray-100 text-gray-500 border border-gray-200',
    };
    $invBadgeLabel = match($invStatus) {
        'accepted' => 'Portal Access Active',
        'invited'  => 'Invitation Sent',
        default    => 'Not Invited',
    };
@endphp

<div class="space-y-6 max-w-5xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('landlord.tenants.index') }}" class="hover:text-gray-700 transition-colors">Tenants</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium truncate">{{ $tenant->name }}</span>
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

    {{-- ══════════════════════════════════════════════════════
         PROFILE HEADER CARD
    ══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Top section: avatar + info + actions --}}
        <div class="px-10 pb-8" style="padding-top: 36px;">
            <div class="flex flex-col sm:flex-row sm:items-start gap-5">

                {{-- Avatar --}}
                <div class="w-16 h-16 rounded-2xl bg-primary-500 flex items-center justify-center
                            text-white text-2xl font-bold flex-shrink-0 self-start">
                    {{ strtoupper(substr($tenant->name, 0, 2)) }}
                </div>

                {{-- Name + meta --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-gray-900 truncate">{{ $tenant->name }}</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge }} flex-shrink-0">
                            @if($activeLease)
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            @endif
                            {{ $statusLabel }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $invBadgeClass }} flex-shrink-0">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $invBadgeLabel }}
                        </span>
                    </div>

                    <div class="space-y-0.5">
                        <p class="text-sm text-gray-600">{{ $tenant->email }}</p>
                        @if($tenant->phone)
                            <p class="text-sm text-gray-400">{{ $tenant->phone }}</p>
                        @endif
                        <p class="text-xs text-gray-400 pt-0.5">
                            Tenant since {{ $tenant->created_at->format('M Y') }}
                            &nbsp;·&nbsp; {{ $allLeases->count() }} {{ Str::plural('lease', $allLeases->count()) }}
                            @if($tenant->gender) &nbsp;·&nbsp; {{ ucfirst($tenant->gender) }} @endif
                            @if($tenant->nationality) &nbsp;·&nbsp; {{ $tenant->nationality }} @endif
                        </p>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex flex-wrap items-center gap-2 flex-shrink-0 self-start">

                    <a href="{{ route('landlord.tenants.edit', $tenant) }}"
                       class="inline-flex items-center gap-1.5 bg-gray-50 hover:bg-gray-100
                              text-gray-600 font-semibold px-3.5 py-2 rounded-xl text-xs
                              border border-gray-200 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>

                    {{-- Resend via Email --}}
                    <form method="POST" action="{{ route('landlord.tenants.invite', $tenant) }}" class="inline">
                        @csrf
                        <input type="hidden" name="channel" value="email">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100
                                       text-indigo-600 font-semibold px-3.5 py-2 rounded-xl text-xs
                                       border border-indigo-100 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Resend via Email
                        </button>
                    </form>

                    {{-- Resend via SMS --}}
                    <form method="POST" action="{{ route('landlord.tenants.invite', $tenant) }}" class="inline">
                        @csrf
                        <input type="hidden" name="channel" value="sms">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100
                                       text-emerald-600 font-semibold px-3.5 py-2 rounded-xl text-xs
                                       border border-emerald-100 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
                            </svg>
                            Resend via SMS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Stats strip --}}
        <div class="grid grid-cols-3 divide-x divide-gray-100 border-t border-gray-100">

            <div class="flex items-center gap-3.5 px-7 py-5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 font-medium">Total Paid</p>
                    <p class="text-base font-bold text-gray-900 truncate">
                        Tshs {{ number_format($totalPaid, 0) }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3.5 px-7 py-5">
                <div class="w-9 h-9 rounded-xl {{ $totalOverdue > 0 ? 'bg-red-50' : 'bg-gray-50' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $totalOverdue > 0 ? 'text-red-400' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 font-medium">Overdue</p>
                    <p class="text-base font-bold {{ $totalOverdue > 0 ? 'text-red-600' : 'text-gray-900' }} truncate">
                        Tshs {{ number_format($totalOverdue, 0) }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3.5 px-7 py-5">
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium">Maintenance</p>
                    <p class="text-base font-bold text-gray-900">{{ $maintenance->count() }}</p>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         IDENTIFICATION DETAILS
    ══════════════════════════════════════════════════════ --}}
    @if($tenant->tin || $tenant->nida_number || $tenant->gender || $tenant->nationality)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-2.5 mb-5">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-800">Identification</h2>
        </div>

        {{-- Each field: label stacked above value — no flex justify-between --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-5">
            @if($tenant->gender)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Gender</p>
                <p class="text-sm font-semibold text-gray-800">{{ ucfirst($tenant->gender) }}</p>
            </div>
            @endif
            @if($tenant->nationality)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Nationality</p>
                <p class="text-sm font-semibold text-gray-800">{{ $tenant->nationality }}</p>
            </div>
            @endif
            @if($tenant->tin)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">TIN Number</p>
                <p class="text-sm font-semibold text-gray-800 font-mono tracking-wide">{{ $tenant->tin }}</p>
            </div>
            @endif
            @if($tenant->nida_number)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">NIDA Number</p>
                <p class="text-sm font-semibold text-gray-800 font-mono tracking-wide">{{ $tenant->nida_number }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         ACTIVE LEASE
    ══════════════════════════════════════════════════════ --}}
    @if($activeLease)
    @php
        $daysLeft   = now()->startOfDay()->diffInDays($activeLease->end_date, false);
        $isExpiring = $daysLeft >= 0 && $daysLeft <= 30;
        $isOverdue  = $daysLeft < 0;
        $totalDays  = $activeLease->start_date->diffInDays($activeLease->end_date);
        $elapsed    = $activeLease->start_date->diffInDays(now());
        $pct        = $totalDays > 0 ? min(100, round($elapsed / $totalDays * 100)) : 100;
        $barColor   = $isOverdue ? 'bg-red-400' : ($isExpiring ? 'bg-amber-400' : 'bg-emerald-400');
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Active Lease</h2>
            </div>
            <a href="{{ route('landlord.leases.show', $activeLease) }}"
               class="text-xs text-primary-600 hover:text-primary-700 font-semibold transition-colors">
                View Contract →
            </a>
        </div>

        {{-- 4 fields in a consistent grid, label above value --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-5 mb-6">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Property</p>
                <p class="text-sm font-semibold text-gray-800">{{ $activeLease->property->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Unit</p>
                <p class="text-sm font-bold text-primary-600">{{ $activeLease->unit->unit_number ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Monthly Rent</p>
                <p class="text-sm font-semibold text-gray-800">Tshs {{ number_format($activeLease->monthly_rent, 0) }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Lease Ends</p>
                <p class="text-sm font-semibold
                    {{ $isOverdue ? 'text-red-600' : ($isExpiring ? 'text-amber-600' : 'text-gray-800') }}">
                    {{ $activeLease->end_date->format('d M Y') }}
                </p>
                @if($isOverdue)
                    <p class="text-xs text-red-400 mt-0.5">Overdue</p>
                @elseif($isExpiring)
                    <p class="text-xs text-amber-500 mt-0.5">{{ $daysLeft }}d remaining</p>
                @endif
            </div>
        </div>

        {{-- Lease timeline progress --}}
        <div class="bg-gray-50 rounded-xl px-4 py-3">
            <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                <span>{{ $activeLease->start_date->format('d M Y') }}</span>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                    {{ $isOverdue ? 'bg-red-50 text-red-600 border border-red-200' :
                       ($isExpiring ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                       'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                    {{ $isOverdue ? 'Overdue' : ($isExpiring ? 'Expiring Soon' : 'Active') }}
                </span>
                <span>{{ $activeLease->end_date->format('d M Y') }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-1.5">
                <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1.5 text-center">{{ $pct }}% of lease term elapsed</p>
        </div>
    </div>
    @else
    {{-- No lease placeholder --}}
    <div class="bg-white rounded-2xl border border-dashed border-gray-200 shadow-sm p-6">
        <div class="flex items-center gap-2.5 mb-1">
            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-400">Active Lease</h2>
        </div>
        <p class="text-sm text-gray-400 ml-10.5 pl-0.5">No active lease assigned to this tenant.</p>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         LEASE HISTORY
    ══════════════════════════════════════════════════════ --}}
    @if($allLeases->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2.5 px-6 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-800">
                Lease History
                <span class="ml-1 text-gray-400 font-normal text-xs">({{ $allLeases->count() }})</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Property / Unit</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Period</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Rent / mo</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($allLeases as $lease)
                    @php
                        $lBadge = match($lease->status) {
                            'active'     => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            'renewed'    => 'bg-blue-50 text-blue-700 border border-blue-200',
                            'terminated' => 'bg-red-50 text-red-700 border border-red-200',
                            default      => 'bg-gray-100 text-gray-500 border border-gray-200',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-gray-800">{{ $lease->property->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Unit {{ $lease->unit->unit_number ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs hidden sm:table-cell">
                            <p>{{ $lease->start_date->format('d M Y') }}</p>
                            <p class="mt-0.5">{{ $lease->end_date->format('d M Y') }}</p>
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-gray-700 hidden md:table-cell">
                            Tshs {{ number_format($lease->monthly_rent, 0) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $lBadge }}">
                                {{ ucfirst($lease->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('landlord.leases.show', $lease) }}"
                               class="text-xs text-primary-600 hover:text-primary-700 font-semibold transition-colors">
                                View →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         PAYMENT HISTORY
    ══════════════════════════════════════════════════════ --}}
    <div id="payments" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2.5 px-6 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-800">
                Payment History
                <span class="ml-1 text-gray-400 font-normal text-xs">({{ $payments->count() }})</span>
            </h2>
        </div>

        @if($payments->isEmpty())
            <div class="py-12 text-center">
                <p class="text-sm text-gray-400">No payments recorded yet.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Amount</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Due Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Paid Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($payments->take(20) as $payment)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-3.5 font-semibold text-gray-900">
                            Tshs {{ number_format($payment->amount, 0) }}
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs hidden sm:table-cell">
                            {{ $payment->due_date ? \Carbon\Carbon::parse($payment->due_date)->format('d M Y') : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs hidden md:table-cell">
                            {{ $payment->paid_date ? \Carbon\Carbon::parse($payment->paid_date)->format('d M Y') : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $payment->status === 'paid'
                                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                    : ($payment->status === 'overdue'
                                        ? 'bg-red-50 text-red-700 border border-red-200'
                                        : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($payments->count() > 20)
            <div class="px-5 py-3 border-t border-gray-100">
                <p class="text-xs text-gray-400 text-center">Showing 20 of {{ $payments->count() }} payments</p>
            </div>
        @endif
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════
         MAINTENANCE REQUESTS
    ══════════════════════════════════════════════════════ --}}
    @if($maintenance->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2.5 px-6 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-800">
                Maintenance Requests
                <span class="ml-1 text-gray-400 font-normal text-xs">({{ $maintenance->count() }})</span>
            </h2>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($maintenance->take(10) as $req)
            @php
                $pBadge = match($req->priority ?? 'low') {
                    'urgent' => 'bg-red-50 text-red-700 border border-red-200',
                    'high'   => 'bg-orange-50 text-orange-700 border border-orange-200',
                    'medium' => 'bg-amber-50 text-amber-700 border border-amber-200',
                    default  => 'bg-gray-100 text-gray-500 border border-gray-200',
                };
                $sBadge = match($req->status) {
                    'open'        => 'bg-blue-50 text-blue-700 border border-blue-200',
                    'in_progress' => 'bg-amber-50 text-amber-700 border border-amber-200',
                    'resolved'    => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                    default       => 'bg-gray-100 text-gray-500 border border-gray-200',
                };
            @endphp
            <div class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50/70 transition-colors">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $req->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $req->property->name ?? '—' }}
                        @if($req->created_at)
                            &nbsp;·&nbsp; {{ $req->created_at->format('d M Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $pBadge }}">
                        {{ ucfirst($req->priority ?? 'Low') }}
                    </span>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $sBadge }}">
                        {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════
     TERMINATE LEASE MODAL
══════════════════════════════════════════════════════ --}}
@if($activeLease)
<div id="terminate-modal"
     class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Terminate Lease</h3>
                    <p class="text-xs text-gray-400">This action cannot be undone</p>
                </div>
            </div>
            <button onclick="closeTerminateModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="terminate-form" method="POST" action="">
            @csrf
            <div class="p-6 space-y-5">
                <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm text-red-700">
                    This will set the unit to <strong>Vacant</strong> and mark <strong>{{ $tenant->name }}</strong> as Inactive.
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Reason for Termination</label>
                    <div class="space-y-2">
                        @foreach([
                            'Non-payment of rent'  => 'Non-payment of rent',
                            'Lease violation'       => 'Lease violation',
                            'Tenant request'        => 'Tenant request (voluntary exit)',
                            'Property sale'         => 'Property sale or renovation',
                            'End of lease term'     => 'End of lease term (not renewed)',
                            'Other'                 => 'Other',
                        ] as $value => $label)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="termination_reason" value="{{ $value }}"
                                   class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500 cursor-pointer">
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Additional Notes <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea name="termination_notes" rows="3" placeholder="Any additional context…"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800
                                     placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-400
                                     focus:border-red-400 resize-none transition"></textarea>
                </div>

                @include('landlord.leases._termination_notice')
            </div>
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-2xl">
                <button type="button" onclick="closeTerminateModal()"
                        class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                    Confirm Termination
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     RENEW LEASE MODAL
══════════════════════════════════════════════════════ --}}
<div id="renew-modal"
     class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Renew Lease</h3>
                    <p class="text-xs text-gray-400">Creates a new lease as continuation</p>
                </div>
            </div>
            <button onclick="closeRenewModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-gray-100 text-gray-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="renew-form" method="POST" action="">
            @csrf
            <div class="px-6 pt-5 pb-2 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        New Lease End Date <span class="text-red-400 normal-case font-normal">*</span>
                    </label>
                    <input type="date" id="renew-end-date" name="end_date" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800
                                  focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Updated Monthly Rent
                    </label>
                    <div class="flex rounded-xl border border-gray-200 overflow-hidden
                                focus-within:ring-2 focus-within:ring-emerald-400 focus-within:border-emerald-400 transition-all">
                        <span class="flex items-center px-4 py-3 bg-gray-50 text-sm font-semibold text-gray-500 border-r border-gray-200 select-none">
                            Tshs
                        </span>
                        <input type="number" id="renew-rent" name="monthly_rent" min="0" step="1000"
                               class="flex-1 px-4 py-3 text-sm text-gray-800 bg-white outline-none min-w-0"
                               placeholder="Leave blank to keep current rent">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Updated Lease Terms <span class="text-gray-400 normal-case font-normal">(optional)</span>
                    </label>
                    <textarea name="lease_terms" rows="3"
                              placeholder="Leave blank to carry over existing terms…"
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800
                                     placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-400
                                     focus:border-emerald-400 resize-none transition"></textarea>
                </div>
            </div>
            <div class="px-6 pt-4 pb-6 space-y-2.5">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600
                               hover:bg-emerald-700 text-white font-bold px-6 py-3.5 rounded-xl text-sm
                               shadow-lg shadow-emerald-500/25 hover:-translate-y-px transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
@endif

@endsection

@push('scripts')
<script>
const appBaseUrl = '{{ rtrim(url('/'), '/') }}';

function openTerminateModal(leaseId, tenantName) {
    const form = document.getElementById('terminate-form');
    form.action = `${appBaseUrl}/landlord/leases/${leaseId}/terminate`;
    const modal = document.getElementById('terminate-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeTerminateModal() {
    const modal = document.getElementById('terminate-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
function openRenewModal(leaseId, currentEndDate, currentRent) {
    const form      = document.getElementById('renew-form');
    form.action     = `${appBaseUrl}/landlord/leases/${leaseId}/renew`;
    const endInput  = document.getElementById('renew-end-date');
    const rentInput = document.getElementById('renew-rent');
    if (endInput && currentEndDate) {
        const d = new Date(currentEndDate);
        d.setDate(d.getDate() + 1);
        endInput.min   = d.toISOString().split('T')[0];
        endInput.value = '';
    }
    if (rentInput) rentInput.value = currentRent || '';
    const modal = document.getElementById('renew-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeRenewModal() {
    const modal = document.getElementById('renew-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.addEventListener('DOMContentLoaded', () => {
    ['terminate-modal', 'renew-modal'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('click', e => {
            if (e.target === el) { el.classList.add('hidden'); el.classList.remove('flex'); }
        });
    });
});
</script>
@endpush
