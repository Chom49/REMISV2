@extends('layouts.landlord')

@section('title', 'Lease Contract – ' . ($lease->unit?->unit_number ?? '#' . $lease->id))

@section('content')

@php
    $today      = now()->startOfDay();
    $daysLeft   = $today->diffInDays($lease->end_date, false);
    $isExpiring = $lease->status === 'active' && $daysLeft >= 0 && $daysLeft <= 30;
    $isOverdue  = $lease->status === 'active' && $daysLeft < 0;
    $badgeClass = match(true) {
        $isOverdue  => 'bg-red-50 text-red-700 border-red-200',
        $isExpiring => 'bg-amber-50 text-amber-700 border-amber-200',
        $lease->status === 'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        default     => 'bg-gray-100 text-gray-600 border-gray-200',
    };
    $badgeLabel = match(true) {
        $isOverdue  => 'Overdue',
        $isExpiring => 'Expiring Soon',
        $lease->status === 'active' => 'Active',
        default     => ucfirst($lease->status),
    };
@endphp

<div class="space-y-6 max-w-4xl">

    {{-- ── Breadcrumb ──────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('landlord.leases.index') }}" class="hover:text-gray-700 transition-colors">Leases</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">
            {{ $lease->property->name ?? 'Lease' }}
            @if($lease->unit) &mdash; Unit {{ $lease->unit->unit_number }} @endif
        </span>
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

    {{-- ── Hero Header ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5">

            {{-- Left: Contract identity --}}
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-primary-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl font-bold text-gray-900">Lease Contract</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                            {{ $badgeLabel }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $lease->property->name ?? '—' }}
                        @if($lease->unit) &middot; Unit {{ $lease->unit->unit_number }} @endif
                        &middot; Contract #{{ $lease->id }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Created {{ $lease->created_at->format('d M Y') }}
                    </p>
                </div>
            </div>

            {{-- Right: Actions --}}
            <div class="flex flex-col items-stretch sm:items-end gap-2.5 flex-shrink-0">

                {{-- Primary action: Download PDF — always visible --}}
                <a href="{{ route('landlord.leases.download', $lease) }}"
                   class="inline-flex items-center justify-center gap-2.5 bg-primary-600 hover:bg-primary-700
                          active:bg-primary-800 text-white font-bold px-5 py-3 rounded-xl text-sm
                          shadow-lg shadow-primary-500/30 hover:shadow-xl hover:shadow-primary-500/40
                          hover:-translate-y-px transition-all duration-200 select-none"
                   onclick="handleDownload(this)">
                    {{-- PDF document icon --}}
                    <svg class="w-4 h-4 flex-shrink-0 dl-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v6h6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6M9 17h4"/>
                    </svg>
                    <svg class="hidden w-4 h-4 animate-spin flex-shrink-0 dl-spinner" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span class="dl-label">Download PDF</span>
                </a>

                {{-- Secondary: Lease lifecycle actions (active leases only) --}}
                @if($lease->status === 'active')
                <div class="flex items-center gap-2">
                    <button onclick="openRenewModal()"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5
                                   bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold
                                   px-4 py-2 rounded-xl text-xs border border-emerald-200 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Renew
                    </button>
                    <button onclick="openTerminateModal()"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5
                                   bg-red-50 hover:bg-red-100 text-red-700 font-semibold
                                   px-4 py-2 rounded-xl text-xs border border-red-200 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Terminate
                    </button>
                </div>
                @endif
            </div>
        </div>

        {{-- Progress bar for expiry --}}
        @if($lease->status === 'active')
        @php
            $totalDays  = $lease->start_date->diffInDays($lease->end_date);
            $elapsed    = $lease->start_date->diffInDays(now());
            $pct        = $totalDays > 0 ? min(100, round($elapsed / $totalDays * 100)) : 100;
            $barColor   = $isOverdue ? 'bg-red-400' : ($isExpiring ? 'bg-amber-400' : 'bg-emerald-400');
        @endphp
        <div class="mt-5 pt-5 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                <span>{{ $lease->start_date->format('d M Y') }}</span>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $isOverdue ? 'bg-red-50 text-red-600' : ($isExpiring ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">
                    {{ $isOverdue ? 'Overdue' : ($isExpiring ? 'Expiring Soon' : 'Active') }}
                </span>
                <span>{{ $lease->end_date->format('d M Y') }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Two-column detail grid ───────────────────────────── --}}
    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Property & Unit --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Property Details</h2>
            </div>
            <dl class="space-y-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Name</dt>
                    <dd class="font-semibold text-gray-800 text-right">{{ $lease->property->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Address</dt>
                    <dd class="font-semibold text-gray-800 text-right max-w-[60%]">{{ $lease->property->address ?? '—' }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">City</dt>
                    <dd class="font-semibold text-gray-800">{{ $lease->property->city ?? '—' }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Region</dt>
                    <dd class="font-semibold text-gray-800">{{ $lease->property->county ?? '—' }}</dd>
                </div>
                @if($lease->unit)
                <div class="pt-3 border-t border-gray-100 flex justify-between text-sm">
                    <dt class="text-gray-400">Unit / Space</dt>
                    <dd class="font-bold text-primary-600">{{ $lease->unit->unit_number }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Tenant Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Tenant Information</h2>
            </div>
            @if($lease->tenant)
                <div class="flex items-center gap-3 mb-4 p-3 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        {{ strtoupper(substr($lease->tenant->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $lease->tenant->name }}</p>
                        <p class="text-xs text-gray-400">{{ $lease->tenant->email }}</p>
                    </div>
                </div>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-400">Phone</dt>
                        <dd class="font-semibold text-gray-800">{{ $lease->tenant->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-400">Email</dt>
                        <dd class="font-semibold text-gray-800 text-right max-w-[60%] truncate">{{ $lease->tenant->email }}</dd>
                    </div>
                </dl>
            @else
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">No tenant assigned yet</p>
                    <p class="text-xs text-gray-400 mt-1">Assign a tenant from the property page.</p>
                </div>
            @endif
        </div>

        {{-- Financial Terms --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Financial Terms</h2>
            </div>
            <dl class="space-y-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Monthly Rent</dt>
                    <dd class="font-bold text-gray-900 text-lg leading-none">Tshs {{ number_format($lease->monthly_rent, 0) }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Security Deposit</dt>
                    <dd class="font-semibold text-gray-800">Tshs {{ number_format($lease->security_deposit ?? 0, 0) }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Payment Frequency</dt>
                    <dd class="font-semibold text-gray-800 capitalize">{{ $lease->payment_frequency ?? 'Monthly' }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Payment Due Day</dt>
                    <dd class="font-semibold text-gray-800">
                        {{ $lease->payment_day ? 'Day ' . $lease->payment_day . ' of month' : '—' }}
                    </dd>
                </div>
                @php $totalRent = $lease->start_date->diffInMonths($lease->end_date) * $lease->monthly_rent; @endphp
                <div class="flex justify-between text-sm pt-3 border-t border-gray-100">
                    <dt class="text-gray-400">Total Contract Value</dt>
                    <dd class="font-bold text-primary-700">Tshs {{ number_format($totalRent, 0) }}</dd>
                </div>
            </dl>
        </div>

        {{-- Lease Period --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Lease Period</h2>
            </div>
            <dl class="space-y-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Start Date</dt>
                    <dd class="font-semibold text-gray-800">{{ $lease->start_date->format('d F Y') }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">End Date</dt>
                    <dd class="font-semibold text-gray-800">{{ $lease->end_date->format('d F Y') }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Duration</dt>
                    <dd class="font-semibold text-gray-800">
                        {{ $lease->start_date->diffInMonths($lease->end_date) }} months
                    </dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-400">Expiry Reminder</dt>
                    <dd class="font-semibold text-gray-800">
                        {{ $lease->lease_expiry_reminder_days ? $lease->lease_expiry_reminder_days . ' days before' : '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- ── Lease Terms ──────────────────────────────────────── --}}
    @if($lease->lease_terms)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 10h16M4 14h10"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-800">Lease Terms & Conditions</h2>
        </div>
        <div class="bg-gray-50 rounded-xl px-5 py-4 text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $lease->lease_terms }}</div>
    </div>
    @endif

    {{-- ── Payment History ──────────────────────────────────── --}}
    @if($lease->payments->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-800">Payment History
                <span class="ml-1 font-normal text-gray-400">({{ $lease->payments->count() }})</span>
            </h2>
        </div>
        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase tracking-wide text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-2 px-1">Amount</th>
                        <th class="text-left py-2 px-1 hidden sm:table-cell">Due Date</th>
                        <th class="text-left py-2 px-1 hidden md:table-cell">Paid Date</th>
                        <th class="text-left py-2 px-1">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($lease->payments->sortByDesc('due_date') as $payment)
                    <tr>
                        <td class="py-2.5 px-1 font-semibold text-gray-800">Tshs {{ number_format($payment->amount, 0) }}</td>
                        <td class="py-2.5 px-1 text-gray-500 hidden sm:table-cell">{{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}</td>
                        <td class="py-2.5 px-1 text-gray-500 hidden md:table-cell">{{ $payment->paid_date ? \Carbon\Carbon::parse($payment->paid_date)->format('d M Y') : '—' }}</td>
                        <td class="py-2.5 px-1">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $payment->status === 'paid' ? 'bg-emerald-50 text-emerald-700' :
                                   ($payment->status === 'overdue' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Termination Info (if terminated) ──────────────────── --}}
    @if($lease->status === 'terminated')
    <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-red-800">Lease Terminated</h2>
        </div>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
                <dt class="text-red-500">Terminated On</dt>
                <dd class="font-semibold text-red-800">{{ $lease->terminated_at ? \Carbon\Carbon::parse($lease->terminated_at)->format('d M Y') : '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-red-500">Reason</dt>
                <dd class="font-semibold text-red-800">{{ $lease->termination_reason ?? '—' }}</dd>
            </div>
            @if($lease->termination_notes)
            <div class="pt-2 border-t border-red-200">
                <dt class="text-red-500 mb-1">Notes</dt>
                <dd class="text-red-800 leading-relaxed">{{ $lease->termination_notes }}</dd>
            </div>
            @endif
        </dl>
    </div>
    @endif

    {{-- ── Bottom Download Strip ───────────────────────────── --}}
    <div class="flex items-center justify-between gap-4 bg-gray-50 border border-gray-200 rounded-2xl px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v6h6"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Print-ready PDF</p>
                <p class="text-xs text-gray-400">Includes all contract details and signature sections.</p>
            </div>
        </div>
        <a href="{{ route('landlord.leases.download', $lease) }}"
           class="flex-shrink-0 inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700
                  text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all duration-200
                  shadow-md shadow-primary-500/25 hover:shadow-lg hover:shadow-primary-500/35
                  hover:-translate-y-px select-none"
           onclick="handleDownload(this)">
            <svg class="w-4 h-4 flex-shrink-0 dl-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <svg class="hidden w-4 h-4 animate-spin flex-shrink-0 dl-spinner" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="dl-label">Download PDF</span>
        </a>
    </div>

</div>

{{-- ── Terminate Lease Modal ───────────────────────────── --}}
@if($lease->status === 'active')
<div id="terminate-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
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
            <button onclick="closeTerminateModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="terminate-form" method="POST" action="{{ route('landlord.leases.terminate', $lease) }}">
            @csrf
            <div class="p-6 space-y-5">
                <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm text-red-700">
                    Terminating this lease will set the unit to <strong>Vacant</strong>
                    @if($lease->tenant) and mark <strong>{{ $lease->tenant->name }}</strong> as Inactive. @endif
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Reason for Termination</label>
                    <div class="space-y-2">
                        @foreach([
                            'Non-payment of rent'   => 'Non-payment of rent',
                            'Lease violation'        => 'Lease violation',
                            'Tenant request'         => 'Tenant request (voluntary exit)',
                            'Property sale'          => 'Property sale or renovation',
                            'End of lease term'      => 'End of lease term (not renewed)',
                            'Other'                  => 'Other',
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Additional Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="termination_notes" rows="3"
                              placeholder="Any additional context…"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800
                                     placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-400
                                     focus:border-red-400 resize-none"></textarea>
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

{{-- ── Renew Lease Modal ────────────────────────────────── --}}
<div id="renew-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
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
                    <p class="text-xs text-gray-400">Creates a new lease starting from {{ $lease->end_date->format('d M Y') }}</p>
                </div>
            </div>
            <button onclick="closeRenewModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('landlord.leases.renew', $lease) }}">
            @csrf
            <div class="px-6 pt-5 pb-2 space-y-5">

                {{-- Current rent context --}}
                <div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3">
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Current Monthly Rent</span>
                    <span class="text-sm font-bold text-emerald-800">Tshs {{ number_format($lease->monthly_rent, 0) }}</span>
                </div>

                {{-- New end date --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        New Lease End Date <span class="text-red-400 normal-case font-normal">*</span>
                    </label>
                    <input type="date" name="end_date" required
                           min="{{ $lease->end_date->addDay()->format('Y-m-d') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800
                                  focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 transition">
                </div>

                {{-- Monthly rent — split input group --}}
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
                        <input type="number" name="monthly_rent" min="0" step="1000"
                               value="{{ $lease->monthly_rent }}"
                               class="flex-1 px-4 py-3 text-sm text-gray-800 bg-white outline-none min-w-0">
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Update to change rent for the new term, or leave as-is.</p>
                </div>

                {{-- Lease terms --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Updated Lease Terms
                        <span class="text-gray-400 normal-case font-normal ml-1">(optional)</span>
                    </label>
                    <textarea name="lease_terms" rows="3"
                              placeholder="Leave blank to carry over existing terms…"
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800
                                     placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-400
                                     focus:border-emerald-400 resize-none transition"></textarea>
                </div>
            </div>

            {{-- Footer --}}
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
@endif

@endsection

@push('scripts')
<script>
function handleDownload(btn) {
    const icon    = btn.querySelector('.dl-icon');
    const spinner = btn.querySelector('.dl-spinner');
    const label   = btn.querySelector('.dl-label');
    if (icon)    icon.classList.add('hidden');
    if (spinner) spinner.classList.remove('hidden');
    if (label)   label.textContent = 'Generating…';
    setTimeout(() => {
        if (spinner) spinner.classList.add('hidden');
        if (icon)    icon.classList.remove('hidden');
        if (label)   label.textContent = 'Download PDF';
    }, 4000);
}

function openTerminateModal() {
    const m = document.getElementById('terminate-modal');
    if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
}
function closeTerminateModal() {
    const m = document.getElementById('terminate-modal');
    if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
}
function openRenewModal() {
    const m = document.getElementById('renew-modal');
    if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
}
function closeRenewModal() {
    const m = document.getElementById('renew-modal');
    if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
}

// Close modals on backdrop click
document.addEventListener('DOMContentLoaded', () => {
    ['terminate-modal','renew-modal'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('click', e => { if (e.target === el) { el.classList.add('hidden'); el.classList.remove('flex'); } });
    });
});
</script>
@endpush
