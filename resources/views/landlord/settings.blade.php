@extends('layouts.landlord')
@section('title', 'Settings')

@section('content')
<div class="space-y-6 max-w-2xl">

    {{-- Page header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your profile, security, and preferences.</p>
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

    {{-- ══ 1. PROFILE SETTINGS ══════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Profile Settings</h2>
        </div>

        <form method="POST" action="{{ route('landlord.settings.profile') }}" class="p-6 space-y-5">
            @csrf

            {{-- Avatar (initials only) --}}
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-primary-500 flex items-center justify-center
                            text-white text-xl font-bold shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
                </div>
            </div>

            {{-- Full Name --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Full Name <span class="text-red-400">*</span>
                </label>
                <input type="text" name="name"
                       value="{{ old('name', $user->name) }}"
                       required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition
                              @error('name') border-red-400 @enderror">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Email Address <span class="text-red-400">*</span>
                </label>
                <input type="email" name="email"
                       value="{{ old('email', $user->email) }}"
                       required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition
                              @error('email') border-red-400 @enderror">
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Phone Number
                </label>
                <input type="text" name="phone"
                       value="{{ old('phone', $user->phone) }}"
                       placeholder="+255 7XX XXX XXX"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
            </div>

            <div class="flex justify-end pt-1">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                               font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Profile
                </button>
            </div>
        </form>
    </div>

    {{-- ══ 2. ACCOUNT & SECURITY ════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Account & Security</h2>
        </div>

        <form method="POST" action="{{ route('landlord.settings.password') }}" class="p-6 space-y-5">
            @csrf

            @error('current_password')
                <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </div>
            @enderror

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Current Password <span class="text-red-400">*</span>
                </label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    New Password <span class="text-red-400">*</span>
                </label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition
                              @error('password') border-red-400 @enderror">
                @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Confirm New Password <span class="text-red-400">*</span>
                </label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
            </div>

            <div class="flex justify-end pt-1">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white
                               font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Change Password
                </button>
            </div>
        </form>
    </div>

    {{-- ══ 3. NOTIFICATION SETTINGS ═════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Notification Settings</h2>
        </div>

        <form method="POST" action="{{ route('landlord.settings.notifications') }}" class="p-6 space-y-4">
            @csrf

            @php
                $notifyRentDue     = $user->preference('notify_rent_due', true);
                $notifyLatePayment = $user->preference('notify_late_payment', true);
                $notifyLeaseExpiry = $user->preference('notify_lease_expiry', true);
            @endphp

            {{-- Hidden inputs hold the actual submitted values --}}
            <input type="hidden" name="notify_rent_due"     id="inp-rent-due"     value="{{ $notifyRentDue     ? '1' : '0' }}">
            <input type="hidden" name="notify_late_payment" id="inp-late-payment" value="{{ $notifyLatePayment ? '1' : '0' }}">
            <input type="hidden" name="notify_lease_expiry" id="inp-lease-expiry" value="{{ $notifyLeaseExpiry ? '1' : '0' }}">

            {{-- Rent Due Alerts --}}
            <div class="flex items-center justify-between gap-4 py-2">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Rent Due Alerts</p>
                    <p class="text-xs text-gray-400 mt-0.5">Get notified when rent is due from tenants.</p>
                </div>
                <button type="button" id="toggle-rent-due" onclick="toggleNotif('rent-due')"
                        style="width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;transition:background .2s;background:{{ $notifyRentDue ? '#40916c' : '#d1d5db' }};">
                    <span id="knob-rent-due"
                          style="position:absolute;top:2px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .2s;left:{{ $notifyRentDue ? '22px' : '2px' }};"></span>
                </button>
            </div>

            <div class="border-t border-gray-50"></div>

            {{-- Late Payment Alerts --}}
            <div class="flex items-center justify-between gap-4 py-2">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Late Payment Alerts</p>
                    <p class="text-xs text-gray-400 mt-0.5">Get notified when a tenant misses a payment.</p>
                </div>
                <button type="button" id="toggle-late-payment" onclick="toggleNotif('late-payment')"
                        style="width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;transition:background .2s;background:{{ $notifyLatePayment ? '#40916c' : '#d1d5db' }};">
                    <span id="knob-late-payment"
                          style="position:absolute;top:2px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .2s;left:{{ $notifyLatePayment ? '22px' : '2px' }};"></span>
                </button>
            </div>

            <div class="border-t border-gray-50"></div>

            {{-- Lease Expiry Alerts --}}
            <div class="flex items-center justify-between gap-4 py-2">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Lease Expiry Alerts</p>
                    <p class="text-xs text-gray-400 mt-0.5">Get notified 30 days before a lease expires.</p>
                </div>
                <button type="button" id="toggle-lease-expiry" onclick="toggleNotif('lease-expiry')"
                        style="width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;transition:background .2s;background:{{ $notifyLeaseExpiry ? '#40916c' : '#d1d5db' }};">
                    <span id="knob-lease-expiry"
                          style="position:absolute;top:2px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .2s;left:{{ $notifyLeaseExpiry ? '22px' : '2px' }};"></span>
                </button>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                               font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Notifications
                </button>
            </div>
        </form>
    </div>

    {{-- ══ 4. FINANCIAL OFFICER ═════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Financial Officer</h2>
        </div>

        <div class="p-6">
            @if($user->hasActiveFinancialOfficer())
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                        <p class="text-sm text-gray-600">
                            You have an active Financial Officer managing rent collection and payment verification.
                        </p>
                    </div>
                    <a href="{{ route('landlord.fo.index') }}"
                       class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700
                              font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors whitespace-nowrap">
                        Manage
                    </a>
                </div>
            @else
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm text-gray-600">
                        Delegate rent collection, control numbers, and payment verification
                        to a dedicated Financial Officer.
                    </p>
                    <a href="{{ route('landlord.fo.create') }}"
                       class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                              font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Officer
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── Notification toggles ──────────────────────────────────────
const notifState = {
    'rent-due':     {{ $user->preference('notify_rent_due',     true) ? 'true' : 'false' }},
    'late-payment': {{ $user->preference('notify_late_payment', true) ? 'true' : 'false' }},
    'lease-expiry': {{ $user->preference('notify_lease_expiry', true) ? 'true' : 'false' }},
};

function toggleNotif(key) {
    notifState[key] = !notifState[key];
    const on = notifState[key];

    const btn  = document.getElementById('toggle-' + key);
    const knob = document.getElementById('knob-' + key);
    const inp  = document.getElementById('inp-' + key.replace('-', '-').replace('rent-due', 'rent-due')
                                                      .replace('late-payment', 'late-payment')
                                                      .replace('lease-expiry', 'lease-expiry'));

    btn.style.background = on ? '#40916c' : '#d1d5db';
    knob.style.left      = on ? '22px' : '2px';

    const inputMap = {
        'rent-due':     'inp-rent-due',
        'late-payment': 'inp-late-payment',
        'lease-expiry': 'inp-lease-expiry',
    };
    document.getElementById(inputMap[key]).value = on ? '1' : '0';
}
</script>
@endpush
