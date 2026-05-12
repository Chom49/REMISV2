@extends('layouts.tenant')
@section('title', 'Set New Password')

@section('content')

<div class="min-h-screen flex items-start justify-center pt-8 pb-12 px-4">
<div class="w-full max-w-md">

    {{-- Security banner --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <div>
            <p class="text-sm font-bold text-amber-800">Password change required</p>
            <p class="text-xs text-amber-700 mt-0.5">
                You are logged in with a temporary password. Please set a new personal password to secure your account before continuing.
            </p>
        </div>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary-700 to-primary-500 px-6 py-6 text-center">
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-lg font-bold text-white">Set Your New Password</h1>
            <p class="text-primary-100 text-sm mt-1">Hi {{ Auth::user()->name }} — choose a strong password you'll remember</p>
        </div>

        {{-- Flash errors --}}
        @if($errors->any())
            <div class="mx-6 mt-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <p class="font-semibold mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('tenant.password.update') }}" class="p-6 space-y-5">
            @csrf

            {{-- Current (default) password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Current (Default) Password <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="current_password" id="current_password" required
                           class="input-field pr-11" placeholder="Enter the password from your email"
                           autocomplete="current-password">
                    <button type="button" onclick="toggleVisibility('current_password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-5 h-5 eye-off" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                        <svg class="w-5 h-5 eye-on hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">This is the temporary password sent to your email.</p>
            </div>

            <hr class="border-gray-100">

            {{-- New password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    New Password <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="password" id="new_password" required
                           class="input-field pr-11" placeholder="At least 8 characters"
                           autocomplete="new-password" oninput="checkStrength(this.value)">
                    <button type="button" onclick="toggleVisibility('new_password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-5 h-5 eye-off" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                        <svg class="w-5 h-5 eye-on hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>

                {{-- Strength meter --}}
                <div class="mt-2 space-y-1.5">
                    <div class="flex gap-1">
                        <div class="strength-bar h-1.5 flex-1 rounded-full bg-gray-200 transition-colors" id="bar1"></div>
                        <div class="strength-bar h-1.5 flex-1 rounded-full bg-gray-200 transition-colors" id="bar2"></div>
                        <div class="strength-bar h-1.5 flex-1 rounded-full bg-gray-200 transition-colors" id="bar3"></div>
                        <div class="strength-bar h-1.5 flex-1 rounded-full bg-gray-200 transition-colors" id="bar4"></div>
                    </div>
                    <p id="strength-label" class="text-xs text-gray-400"></p>
                </div>

                {{-- Requirements checklist --}}
                <ul class="mt-3 space-y-1">
                    <li class="req-item flex items-center gap-2 text-xs text-gray-400" id="req-length">
                        <svg class="w-3.5 h-3.5 req-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                        </svg>
                        At least 8 characters
                    </li>
                    <li class="req-item flex items-center gap-2 text-xs text-gray-400" id="req-upper">
                        <svg class="w-3.5 h-3.5 req-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                        </svg>
                        One uppercase letter (A–Z)
                    </li>
                    <li class="req-item flex items-center gap-2 text-xs text-gray-400" id="req-lower">
                        <svg class="w-3.5 h-3.5 req-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                        </svg>
                        One lowercase letter (a–z)
                    </li>
                    <li class="req-item flex items-center gap-2 text-xs text-gray-400" id="req-number">
                        <svg class="w-3.5 h-3.5 req-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                        </svg>
                        One number (0–9)
                    </li>
                </ul>
            </div>

            {{-- Confirm password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Confirm New Password <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="confirm_password" required
                           class="input-field pr-11" placeholder="Re-enter your new password"
                           autocomplete="new-password" oninput="checkMatch()">
                    <button type="button" onclick="toggleVisibility('confirm_password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-5 h-5 eye-off" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                        <svg class="w-5 h-5 eye-on hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p id="match-hint" class="text-xs mt-1 hidden"></p>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600
                           text-white font-bold px-6 py-3.5 rounded-xl text-sm transition-colors mt-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Save New Password &amp; Continue
            </button>
        </form>

    </div>

    <p class="text-center text-xs text-gray-400 mt-5">
        Need help? Contact your landlord or email
        <a href="mailto:{{ config('mail.from.address') }}" class="text-primary-600 hover:underline">{{ config('mail.from.address') }}</a>
    </p>

</div>
</div>

<script>
function toggleVisibility(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('.eye-off').classList.toggle('hidden', !isText);
    btn.querySelector('.eye-on').classList.toggle('hidden',  isText);
}

const bars   = [1,2,3,4].map(i => document.getElementById('bar' + i));
const label  = document.getElementById('strength-label');
const reqs   = {
    length: { el: document.getElementById('req-length'), fn: v => v.length >= 8 },
    upper:  { el: document.getElementById('req-upper'),  fn: v => /[A-Z]/.test(v) },
    lower:  { el: document.getElementById('req-lower'),  fn: v => /[a-z]/.test(v) },
    number: { el: document.getElementById('req-number'), fn: v => /[0-9]/.test(v) },
};

const checkSvgOk  = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>`;
const checkSvgNo  = `<circle cx="12" cy="12" r="9" stroke-width="2"/>`;
const levels = [
    { color: 'bg-red-400',    text: 'Weak',      textColor: 'text-red-500' },
    { color: 'bg-orange-400', text: 'Fair',      textColor: 'text-orange-500' },
    { color: 'bg-amber-400',  text: 'Good',      textColor: 'text-amber-600' },
    { color: 'bg-emerald-500',text: 'Strong ✓',  textColor: 'text-emerald-600' },
];

function checkStrength(val) {
    let score = 0;
    Object.values(reqs).forEach(r => {
        const ok = r.fn(val);
        r.el.classList.toggle('text-emerald-600', ok);
        r.el.classList.toggle('text-gray-400',    !ok);
        r.el.querySelector('.req-icon').innerHTML = ok ? checkSvgOk : checkSvgNo;
        if (ok) score++;
    });

    bars.forEach((b, i) => {
        b.className = 'strength-bar h-1.5 flex-1 rounded-full transition-colors ';
        b.className += (i < score && score > 0) ? (levels[score - 1]?.color || 'bg-gray-200') : 'bg-gray-200';
    });

    label.textContent  = val.length ? (levels[score - 1]?.text || '') : '';
    label.className    = 'text-xs ' + (levels[score - 1]?.textColor || 'text-gray-400');
    checkMatch();
}

function checkMatch() {
    const np   = document.getElementById('new_password').value;
    const cp   = document.getElementById('confirm_password').value;
    const hint = document.getElementById('match-hint');
    if (!cp) { hint.classList.add('hidden'); return; }
    const ok = np === cp;
    hint.textContent  = ok ? '✓ Passwords match' : '✗ Passwords do not match';
    hint.className    = 'text-xs mt-1 ' + (ok ? 'text-emerald-600' : 'text-red-500');
    hint.classList.remove('hidden');
}
</script>
@endsection
