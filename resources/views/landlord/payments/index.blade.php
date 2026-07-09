@extends('layouts.landlord')
@section('title', 'Payments')

@section('content')
<div class="space-y-6">

    {{-- ── Page header ─────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Payments</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage NMB control numbers and track rent collection</p>
        </div>
        <span class="inline-flex items-center gap-1.5 bg-primary-50 text-primary-700 text-sm font-semibold px-4 py-2 rounded-full border border-primary-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ $stats['total'] }} {{ Str::plural('Tenant', $stats['total']) }}
        </span>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────── --}}
    @if(session('success'))
    <div id="flash-success" class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div id="flash-error" class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Live payment-confirmed banner (injected by JS) --}}
    <div id="live-paid-banner" class="hidden flex items-center gap-3 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-xl text-sm font-medium">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span id="live-paid-text"></span>
    </div>

    {{-- ── Summary cards ────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Pending --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending</span>
                <div class="w-9 h-9 rounded-xl bg-yellow-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Awaiting payment</p>
        </div>

        {{-- Overdue --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Overdue</span>
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['overdue'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Past due date</p>
        </div>

        {{-- Due Soon --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Due Soon</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['upcoming'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Within 7 days</p>
        </div>

        {{-- Total --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</span>
                <div class="w-9 h-9 rounded-xl bg-primary-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-1">All tenants</p>
        </div>
    </div>

    {{-- ── Filter bar + Search ──────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">

        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-gray-100">

            {{-- Filter tabs (pill style matching leases page) --}}
            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1 text-sm font-medium flex-wrap">
                @php
                    $tabs = [
                        'all'      => 'All',
                        'pending'  => 'Pending',
                        'overdue'  => 'Overdue',
                        'upcoming' => 'Due Soon',
                        'paid'     => 'Paid',
                        'previous' => 'Previous',
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                <a href="{{ route('landlord.payments.index', ['filter' => $key]) }}"
                   class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap
                          {{ $filter === $key
                              ? 'bg-white text-gray-800 shadow-sm'
                              : 'text-gray-500 hover:text-gray-800' }}">
                    {{ $label }}@if($key === 'upcoming' && $stats['upcoming'] > 0)<span class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px] font-bold {{ $filter === $key ? 'bg-amber-100 text-amber-700' : 'bg-amber-200 text-amber-800' }}">{{ $stats['upcoming'] > 9 ? '9+' : $stats['upcoming'] }}</span>@endif
                </a>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="relative sm:ml-auto sm:w-72">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="pay-search" type="text" placeholder="Search tenant or property…"
                       oninput="searchRows(this.value)"
                       class="w-full bg-white pl-10 pr-4 py-2.5 text-sm text-gray-800
                              border border-gray-300 rounded-xl shadow-sm placeholder-gray-400
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
            </div>
        </div>

        {{-- ── Table ───────────────────────────────────────────── --}}
        @if($tenants->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-gray-600 font-medium">No tenants found for this filter.</p>
                <p class="text-sm text-gray-400 mt-1">Try a different filter or add tenants from the Tenants page.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
                        <th class="px-5 py-3.5">Tenant</th>
                        <th class="px-5 py-3.5 hidden md:table-cell">Property / Unit</th>
                        <th class="px-5 py-3.5 hidden lg:table-cell">Balance</th>
                        <th class="px-5 py-3.5 hidden md:table-cell">Due Date</th>
                        <th class="px-5 py-3.5">Payment Status</th>
                        <th class="px-5 py-3.5 hidden lg:table-cell">Control Number</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="pay-table" class="divide-y divide-gray-50">

                @foreach($tenants as $tenant)
                @php
                    $row           = $rowData[$tenant->id] ?? [];
                    $payment       = $row['payment']       ?? null;
                    $lease         = $row['active_lease']  ?? null;
                    $canGenerate   = (bool) ($row['can_generate']   ?? false);
                    $activeControl = (bool) ($row['active_control'] ?? false);

                    $linkedBalance = $lease?->monthly_rent ?? $payment?->amount;
                    $isLeaseActive = $lease
                                     && $lease->status === 'active'
                                     && ($lease->end_date->isFuture() || $lease->end_date->isToday());
                    $isPaid        = $payment && $payment->status === 'paid';
                    $isOverdue     = $payment && ($payment->status === 'overdue'
                                     || ($payment->status === 'pending'
                                         && $payment->due_date?->isPast()
                                         && ! $payment->due_date?->isToday()));
                    $isDueSoon     = $payment && ! $isPaid
                                     && $payment->due_date
                                     && $payment->due_date->between(now()->subDays(1), now()->addDays(7));
                    $hasControl    = $payment && ! empty($payment->control_number);
                    $isPrevious    = $tenant->tenant_status === 'inactive';

                    $propertyName = optional(optional($lease)->property)->name
                                 ?? optional(optional(optional($payment)->lease)->property)->name
                                 ?? '—';
                    $unitNumber   = optional(optional($lease)->unit)->unit_number
                                 ?? optional(optional(optional($payment)->lease)->unit)->unit_number;
                @endphp
                <tr id="row-{{ $tenant->id }}"
                    class="hover:bg-gray-50/60 transition-colors pay-row"
                    data-search="{{ strtolower($tenant->name . ' ' . $propertyName . ' ' . ($unitNumber ?? '')) }}">

                    {{-- Tenant --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($tenant->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $tenant->name }}</p>
                                <p class="text-xs text-gray-400">{{ $tenant->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Property / Unit --}}
                    <td class="px-5 py-4 hidden md:table-cell">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 truncate max-w-[140px]">{{ $propertyName }}</p>
                                @if($unitNumber)
                                    <p class="text-xs text-gray-400">{{ $unitNumber }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Balance --}}
                    <td class="px-5 py-4 hidden lg:table-cell">
                        @if($linkedBalance !== null)
                            <p class="font-semibold {{ $isOverdue ? 'text-red-600' : 'text-gray-900' }}">
                                Tzs {{ number_format($linkedBalance, 0) }}
                            </p>
                            @if($lease)
                                <p class="text-xs text-gray-400 mt-0.5">From lease rent</p>
                            @endif
                            @if($isPaid && $payment->nmb_receipt_number)
                                <p class="text-xs text-gray-400 mt-0.5">Rcpt: {{ $payment->nmb_receipt_number }}</p>
                            @endif
                        @else
                            <span class="text-gray-400 text-xs">No payment</span>
                        @endif
                    </td>

                    {{-- Due Date --}}
                    <td class="px-5 py-4 hidden md:table-cell">
                        @if($payment && $payment->due_date)
                            <p class="{{ $isOverdue && !$isPaid ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                                {{ $payment->due_date->format('d M Y') }}
                            </p>
                            @if($isDueSoon && !$isPaid)
                                <p class="text-xs text-amber-600 font-medium mt-0.5">Due soon</p>
                            @elseif($isPaid && $payment->paid_date)
                                <p class="text-xs text-gray-400 mt-0.5">Paid {{ $payment->paid_date->format('d M Y') }}</p>
                            @endif
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Payment Status --}}
                    <td class="px-5 py-4">
                        @if($isPrevious && !$payment)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-gray-100 text-gray-500 border-gray-200">
                                Previous
                            </span>
                        @elseif(!$payment)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border bg-gray-100 text-gray-400 border-gray-200">
                                No record
                            </span>
                        @elseif(!$isLeaseActive && !$isPaid)
                            <span id="status-badge-{{ $tenant->id }}"
                                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border bg-red-50 text-red-600 border-red-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Lease expired
                            </span>
                        @elseif($isPaid)
                            <span id="status-badge-{{ $tenant->id }}"
                                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Paid
                            </span>
                        @elseif($isOverdue)
                            <span id="status-badge-{{ $tenant->id }}"
                                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border bg-red-50 text-red-600 border-red-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Overdue
                            </span>
                        @else
                            <span id="status-badge-{{ $tenant->id }}"
                                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border bg-amber-50 text-amber-700 border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Pending
                            </span>
                        @endif
                    </td>

                    {{-- Control Number --}}
                    <td class="px-5 py-4 hidden lg:table-cell">
                        @if($hasControl)
                            <p id="ctrl-display-{{ $tenant->id }}"
                               class="font-mono text-sm font-semibold {{ $activeControl ? 'text-primary-700 bg-primary-50' : 'text-gray-500 bg-gray-100' }} px-2.5 py-1 rounded-lg inline-block">
                                {{ $payment->control_number }}
                            </p>
                            @unless($activeControl)
                                <p class="text-xs text-red-500 mt-0.5">Expired</p>
                            @endunless
                            @if($payment->control_number_sent_at)
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Sent via {{ $payment->control_number_sent_via }}
                                    &bull; {{ $payment->control_number_sent_at->format('d M') }}
                                </p>
                            @endif
                        @else
                            <span id="ctrl-display-{{ $tenant->id }}" class="text-xs text-gray-400 italic">Not generated</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                        @if($isPaid)
                            <span class="text-xs text-gray-400 italic">
                                Collected
                            </span>
                        @elseif(!$payment)
                            <span class="text-xs text-gray-400 italic">No active payment</span>
                        @else
                            {{-- Generate Control Number --}}
                            @if($canGenerate)
                            <form method="POST"
                                  action="{{ route('landlord.payments.generate', $payment) }}"
                                  onsubmit="spinBtn(event, 'gen-{{ $tenant->id }}')">
                                @csrf
                                <button type="submit" id="gen-{{ $tenant->id }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                               bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Generate
                                </button>
                            </form>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                         bg-gray-100 text-gray-400 text-xs font-semibold cursor-not-allowed"
                                  title="{{ $activeControl ? 'Control number already exists' : 'Lease expired or payment already settled' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $activeControl ? 'Generated' : 'Generate' }}
                            </span>
                            @endif

                            {{-- Send Control Number --}}
                            @if($activeControl)
                            <button type="button"
                                    onclick="openSendModal(
                                        {{ $payment->id }},
                                        @js($payment->control_number),
                                        @js($tenant->name),
                                        @js($tenant->email),
                                        @js($tenant->phone))"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                           bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Send
                            </button>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                         bg-gray-50 text-gray-300 text-xs font-semibold cursor-not-allowed"
                                  title="Generate a control number first">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Send
                            </span>
                            @endif

                            {{-- Check status (only when control number exists) --}}
                            @if($hasControl)
                            <button type="button"
                                    id="chk-{{ $tenant->id }}"
                                    onclick="checkStatus({{ $tenant->id }}, {{ $payment->id }}, '{{ $payment->control_number }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                                           bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors"
                                    title="Check NMB payment status">
                                <svg id="chk-icon-{{ $tenant->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                            @endif
                        @endif
                        </div>
                    </td>

                </tr>
                @endforeach

                </tbody>
            </table>

            {{-- No search results --}}
            <div id="no-results" class="hidden py-12 text-center">
                <p class="text-sm text-gray-400">No tenants match your search.</p>
            </div>
        </div>

        {{-- Table footer --}}
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Showing <span id="visible-count">{{ $tenants->count() }}</span> of {{ $tenants->total() }} tenants
            </p>
            <p class="text-xs text-gray-400">
                {{ ucfirst($filter) }} view
            </p>
        </div>
        @endif

        {{-- Pagination --}}
        @if($tenants->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $tenants->links() }}
        </div>
        @endif

    </div>

</div>

{{-- ═══ Send Control Number Modal ═════════════════════════════════════════ --}}
<div id="send-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" onclick="closeSendModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">

        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Send Control Number</h3>
                <p class="text-sm text-gray-500 mt-0.5">Choose how to deliver the payment reference to the tenant</p>
            </div>
            <button onclick="closeSendModal()"
                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tenant info --}}
        <div class="bg-gray-50 rounded-xl p-3 mb-4 flex items-center gap-3">
            <div id="modal-avatar"
                 class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
            </div>
            <div>
                <div id="modal-tenant-name" class="font-semibold text-gray-900 text-sm"></div>
                <div id="modal-tenant-contact" class="text-xs text-gray-400"></div>
            </div>
        </div>

        {{-- Control number --}}
        <div class="bg-primary-50 border border-primary-200 rounded-xl p-3 mb-5 text-center">
            <div class="text-xs text-primary-500 font-semibold uppercase tracking-wide mb-1">Control Number</div>
            <div id="modal-ctrl-num" class="font-mono text-2xl font-bold text-primary-800"></div>
        </div>

        {{-- Channel selection --}}
        <form id="send-form" method="POST" action="">
            @csrf
            <input type="hidden" name="channel" id="modal-channel" value="email">

            <p class="text-sm font-medium text-gray-700 mb-3">Delivery channel</p>
            <div class="grid grid-cols-2 gap-3 mb-5">
                <label class="channel-opt flex flex-col items-center gap-2 p-4 border-2 rounded-xl cursor-pointer transition-colors
                              border-primary-500 bg-primary-50" data-ch="email">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <div class="text-center">
                        <div class="text-sm font-semibold text-gray-900">Email</div>
                        <div id="email-hint" class="text-xs text-gray-400 truncate max-w-[100px]"></div>
                    </div>
                </label>

                <label class="channel-opt flex flex-col items-center gap-2 p-4 border-2 rounded-xl cursor-pointer transition-colors
                              border-gray-200 bg-white" data-ch="sms">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 3H3c-1.1 0-2 .9-2 2v14l4-4h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
                    </svg>
                    <div class="text-center">
                        <div class="text-sm font-semibold text-gray-900">SMS</div>
                        <div id="sms-hint" class="text-xs text-gray-400"></div>
                    </div>
                </label>
            </div>

            <div id="no-phone-warn"
                 class="hidden mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Tenant has no phone number. SMS is unavailable.
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeSendModal()"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                    Send Control Number
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<style>
    .spin-anim { animation: spin360 1s linear infinite; }
    @keyframes spin360 { to { transform: rotate(360deg); } }
    .row-flash { animation: rowFlash 0.8s ease forwards; }
    @keyframes rowFlash { 0% { background:#d1fae5; } 100% { background:transparent; } }
</style>
<script>
const BASE = '{{ url("landlord/payments") }}';

// ── Search ─────────────────────────────────────────────────────────────────
function searchRows(q) {
    const term = q.toLowerCase();
    const rows = document.querySelectorAll('.pay-row');
    let visible = 0;
    rows.forEach(row => {
        const show = !term || row.dataset.search.includes(term);
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });
    const cnt = document.getElementById('visible-count');
    if (cnt) cnt.textContent = visible;
    const nr = document.getElementById('no-results');
    if (nr) nr.classList.toggle('hidden', visible > 0);
}

// ── Send modal ─────────────────────────────────────────────────────────────
function openSendModal(paymentId, ctrlNum, name, email, phone) {
    document.getElementById('modal-ctrl-num').textContent      = ctrlNum;
    document.getElementById('modal-tenant-name').textContent   = name;
    document.getElementById('modal-avatar').textContent        = name.substring(0, 2).toUpperCase();
    document.getElementById('modal-tenant-contact').textContent = email || '—';
    document.getElementById('email-hint').textContent          = email || 'No email';
    document.getElementById('sms-hint').textContent            = phone || 'No phone';

    const noPhone = !phone || phone.trim() === '';
    document.getElementById('no-phone-warn').classList.toggle('hidden', !noPhone);
    document.getElementById('send-form').action = `${BASE}/${paymentId}/send`;
    document.getElementById('modal-channel').value = 'email';
    selectChannel('email');
    document.getElementById('send-modal').classList.remove('hidden');
}
function closeSendModal() {
    document.getElementById('send-modal').classList.add('hidden');
}
function selectChannel(ch) {
    document.getElementById('modal-channel').value = ch;
    document.querySelectorAll('.channel-opt').forEach(el => {
        const sel = el.dataset.ch === ch;
        el.classList.toggle('border-primary-500', sel);
        el.classList.toggle('bg-primary-50', sel);
        el.classList.toggle('border-gray-200', !sel);
        el.classList.toggle('bg-white', !sel);
        el.querySelector('svg').classList.toggle('text-primary-600', sel);
        el.querySelector('svg').classList.toggle('text-gray-400', !sel);
    });
}
document.querySelectorAll('.channel-opt').forEach(el => {
    el.addEventListener('click', () => {
        if (el.dataset.ch === 'sms' && document.getElementById('sms-hint').textContent === 'No phone') return;
        selectChannel(el.dataset.ch);
    });
});

// ── Generate spinner ───────────────────────────────────────────────────────
function spinBtn(event, id) {
    const btn = document.getElementById(id);
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-3.5 h-3.5 spin-anim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg> Generating…`;
}

// ── Single status check ────────────────────────────────────────────────────
async function checkStatus(tenantId, paymentId, ctrlNum) {
    const btn  = document.getElementById(`chk-${tenantId}`);
    const icon = document.getElementById(`chk-icon-${tenantId}`);
    if (btn) { btn.disabled = true; icon.classList.add('spin-anim'); }

    try {
        const res  = await fetch(`${BASE}/${paymentId}/status`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (data.status === 'just_paid' || data.status === 'paid') {
            markRowPaid(tenantId, data);
            showBanner(`Payment of TZS ${data.amount || ''} confirmed by NMB for control number ${ctrlNum}.${data.receipt ? ' Receipt: ' + data.receipt : ''}`);
        } else if (data.status === 'error') {
            showBanner(data.message, 'error');
        } else {
            showBanner(`Payment not yet received for ${ctrlNum}. Status: ${data.invoice_status || 'pending'}.`, 'info');
        }
    } catch (e) {
        showBanner('Could not reach the payment gateway. Please try again.', 'error');
    } finally {
        if (btn) { btn.disabled = false; icon.classList.remove('spin-anim'); }
    }
}

// ── Auto background poll (every 45 s) ─────────────────────────────────────
async function pollAll() {
    try {
        const res  = await fetch(`{{ route('landlord.payments.poll-all') }}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.now_paid && data.now_paid.length > 0) {
            const names = data.now_paid.map(p => p.tenant_name || 'A tenant').join(', ');
            showBanner(`New payment(s) confirmed: ${names}. Refreshing…`);
            setTimeout(() => location.reload(), 3000);
        }
    } catch (_) {}
}

// ── DOM helpers ────────────────────────────────────────────────────────────
function markRowPaid(tenantId, data) {
    const badge = document.getElementById(`status-badge-${tenantId}`);
    if (badge) {
        badge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-100';
        badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Paid';
    }
    const row = document.getElementById(`row-${tenantId}`);
    if (row) row.classList.add('row-flash');
}

function showBanner(msg, type = 'success') {
    const banner = document.getElementById('live-paid-banner');
    const text   = document.getElementById('live-paid-text');
    if (!banner || !text) return;
    text.textContent = msg;
    banner.classList.remove('hidden', 'bg-green-50','border-green-300','text-green-800',
                                     'bg-red-50','border-red-300','text-red-800',
                                     'bg-blue-50','border-blue-300','text-blue-800');
    if (type === 'error') banner.classList.add('bg-red-50','border-red-300','text-red-800');
    else if (type === 'info') banner.classList.add('bg-blue-50','border-blue-300','text-blue-800');
    else banner.classList.add('bg-green-50','border-green-300','text-green-800');
    setTimeout(() => banner.classList.add('hidden'), 8000);
}

// ── Init ───────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[id^="chk-"]')) {
        setInterval(pollAll, 45000);
    }
    // Auto-dismiss flash messages after 5 s
    ['flash-success','flash-error'].forEach(id => {
        const el = document.getElementById(id);
        if (el) setTimeout(() => el.remove(), 5000);
    });
});
</script>
@endpush
