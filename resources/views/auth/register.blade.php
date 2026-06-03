<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account – REMIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-900 min-h-screen">

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">

    <p class="self-start text-gray-400 text-sm font-medium ml-4 sm:ml-12 mb-3">Landlord signup page</p>

    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden flex max-h-[calc(100vh-4rem)]">

        {{-- ══ LEFT — FORM ══ --}}
        <div class="w-full lg:w-[50%] bg-primary-100 flex flex-col px-8 sm:px-12 py-8 overflow-y-auto">
            <div class="w-full max-w-sm mx-auto">

                {{-- Logo + heading --}}
                <div class="flex flex-col items-center text-center mb-4">
                    <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS"
                         class="h-10 w-auto object-contain mb-1">
                    <h1 class="text-xl font-bold text-gray-900 mt-1">Create Your Account</h1>
                    <p class="text-xs text-gray-500 mt-0.5 leading-snug max-w-xs">
                        Join thousands of property owners simplifying their management.
                    </p>
                </div>

                {{-- General error summary (only shows if errors exist but no specific field) --}}
                @if ($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('phone') && !$errors->has('password') && !$errors->has('password_confirmation'))
                    <div class="mb-4 flex items-start gap-2.5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="register-form" class="space-y-3" novalidate>
                    @csrf
                    <input type="hidden" name="role" value="landlord">

                    {{-- Full Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="field-name"
                               value="{{ old('name') }}"
                               placeholder="e.g. John Doe"
                               autocomplete="name"
                               class="w-full bg-white rounded-2xl px-4 py-2.5 text-sm text-gray-900
                                      shadow-sm focus:outline-none focus:ring-2 transition
                                      {{ $errors->has('name') ? 'ring-2 ring-red-400 border-red-300' : 'border-0 focus:ring-primary-400' }}">
                        @error('name')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p id="err-name" class="hidden mt-1.5 flex items-center gap-1.5 text-xs text-red-600">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span></span>
                        </p>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="field-email"
                               value="{{ old('email') }}"
                               placeholder="you@example.com"
                               autocomplete="email"
                               class="w-full bg-white rounded-2xl px-4 py-2.5 text-sm text-gray-900
                                      shadow-sm focus:outline-none focus:ring-2 transition
                                      {{ $errors->has('email') ? 'ring-2 ring-red-400 border-red-300' : 'border-0 focus:ring-primary-400' }}">
                        @error('email')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p id="err-email" class="hidden mt-1.5 flex items-center gap-1.5 text-xs text-red-600">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span></span>
                        </p>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number
                            <span class="text-gray-400 text-xs font-normal">(optional)</span>
                        </label>
                        <input type="tel" name="phone" id="field-phone"
                               value="{{ old('phone') }}"
                               placeholder="+255 7XX XXX XXX"
                               autocomplete="tel"
                               class="w-full bg-white rounded-2xl px-4 py-2.5 text-sm text-gray-900
                                      shadow-sm focus:outline-none focus:ring-2 transition
                                      {{ $errors->has('phone') ? 'ring-2 ring-red-400 border-red-300' : 'border-0 focus:ring-primary-400' }}">
                        @error('phone')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p id="err-phone" class="hidden mt-1.5 flex items-center gap-1.5 text-xs text-red-600">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span></span>
                        </p>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="field-password"
                                   placeholder="Min. 8 characters"
                                   autocomplete="new-password"
                                   class="w-full bg-white rounded-2xl px-4 py-2.5 pr-11 text-sm text-gray-900
                                          shadow-sm focus:outline-none focus:ring-2 transition
                                          {{ $errors->has('password') ? 'ring-2 ring-red-400 border-red-300' : 'border-0 focus:ring-primary-400' }}">
                            <button type="button" onclick="togglePwd('field-password','eye-pw')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg id="eye-pw" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Password strength bar --}}
                        <div class="mt-2 space-y-1.5" id="pw-strength-wrap" style="display:none;">
                            <div class="flex gap-1">
                                <div id="bar1" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300"></div>
                                <div id="bar2" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300"></div>
                                <div id="bar3" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300"></div>
                                <div id="bar4" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300"></div>
                            </div>
                            <p id="pw-strength-label" class="text-xs font-medium text-gray-400"></p>
                        </div>

                        {{-- Requirements checklist --}}
                        <ul id="pw-requirements" class="mt-2 space-y-0.5" style="display:none;">
                            <li id="req-len"   class="flex items-center gap-1.5 text-xs text-gray-400"><span class="req-dot">○</span> At least 8 characters</li>
                            <li id="req-upper" class="flex items-center gap-1.5 text-xs text-gray-400"><span class="req-dot">○</span> One uppercase letter</li>
                            <li id="req-lower" class="flex items-center gap-1.5 text-xs text-gray-400"><span class="req-dot">○</span> One lowercase letter</li>
                            <li id="req-num"   class="flex items-center gap-1.5 text-xs text-gray-400"><span class="req-dot">○</span> One number</li>
                            <li id="req-sym"   class="flex items-center gap-1.5 text-xs text-gray-400"><span class="req-dot">○</span> One special character (@#!$%...)</li>
                        </ul>

                        @error('password')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="field-confirm"
                                   placeholder="Re-enter your password"
                                   autocomplete="new-password"
                                   class="w-full bg-white rounded-2xl px-4 py-2.5 pr-11 text-sm text-gray-900
                                          shadow-sm focus:outline-none focus:ring-2 transition
                                          {{ $errors->has('password_confirmation') ? 'ring-2 ring-red-400 border-red-300' : 'border-0 focus:ring-primary-400' }}">
                            <button type="button" onclick="togglePwd('field-confirm','eye-cf')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg id="eye-cf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <p id="err-confirm" class="hidden mt-1.5 flex items-center gap-1.5 text-xs text-red-600">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span>Passwords do not match.</span>
                        </p>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" id="submit-btn"
                            class="w-full bg-primary-500 hover:bg-primary-600 active:bg-primary-700
                                   text-white font-semibold py-3 rounded-full transition-colors duration-200
                                   flex items-center justify-center gap-2 mt-1">
                        <span id="btn-label">Create Account</span>
                        <svg id="btn-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                </form>

                <p class="mt-3 text-center text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-primary-700 font-semibold hover:underline">Sign In</a>
                </p>
            </div>
        </div>

        {{-- ══ RIGHT — BRANDING ══ --}}
        <div class="hidden lg:flex flex-1 bg-white flex-col items-center justify-center px-12 py-12 text-center">
            <h2 class="text-4xl font-extrabold text-gray-900 leading-tight mb-2">
                Managing Properties
            </h2>
            <h2 class="text-4xl font-extrabold leading-tight mb-10">
                Just Got <span class="text-primary-500">Smarter</span>!
            </h2>
            <img src="{{ asset('images/signUp/signUp.png') }}" alt="Property management illustration"
                 class="w-72 h-64 object-contain animate-float">
        </div>

    </div>
</div>

<script>
// ── Show/hide password ────────────────────────────────────────
function togglePwd(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const eye   = document.getElementById(eyeId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    eye.innerHTML = isText
        ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
}

// ── Field helpers ─────────────────────────────────────────────
function setError(fieldId, errId, msg) {
    const field = document.getElementById(fieldId);
    const err   = document.getElementById(errId);
    if (field) { field.classList.add('ring-2', 'ring-red-400'); field.classList.remove('focus:ring-primary-400'); }
    if (err)   { err.querySelector('span').textContent = msg; err.classList.remove('hidden'); err.style.display = 'flex'; }
}
function clearError(fieldId, errId) {
    const field = document.getElementById(fieldId);
    const err   = document.getElementById(errId);
    if (field) { field.classList.remove('ring-2', 'ring-red-400'); field.classList.add('focus:ring-primary-400'); }
    if (err)   { err.classList.add('hidden'); err.style.display = ''; }
}

// ── Name validation ───────────────────────────────────────────
document.getElementById('field-name').addEventListener('blur', function() {
    const v = this.value.trim();
    if (!v) return setError('field-name', 'err-name', 'Full name is required.');
    if (!/^[\p{L}\s\-']+$/u.test(v)) return setError('field-name', 'err-name', 'Name may only contain letters, spaces, hyphens, or apostrophes.');
    if (v.length > 100) return setError('field-name', 'err-name', 'Full name must not exceed 100 characters.');
    clearError('field-name', 'err-name');
});

// ── Email validation ──────────────────────────────────────────
document.getElementById('field-email').addEventListener('blur', function() {
    const v = this.value.trim();
    if (!v) return setError('field-email', 'err-email', 'Email address is required.');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return setError('field-email', 'err-email', 'Please enter a valid email address.');
    clearError('field-email', 'err-email');
});

// ── Phone validation ──────────────────────────────────────────
document.getElementById('field-phone').addEventListener('blur', function() {
    const v = this.value.trim();
    if (!v) return clearError('field-phone', 'err-phone');
    if (!/^\+?[\d\s\-]{7,20}$/.test(v)) return setError('field-phone', 'err-phone', 'Phone must be 7–20 digits. Only numbers, spaces, or hyphens allowed.');
    clearError('field-phone', 'err-phone');
});

// ── Password strength & requirements ─────────────────────────
const pwField   = document.getElementById('field-password');
const cfField   = document.getElementById('field-confirm');
const strengthWrap = document.getElementById('pw-strength-wrap');
const reqWrap      = document.getElementById('pw-requirements');
const bars = ['bar1','bar2','bar3','bar4'].map(id => document.getElementById(id));
const barColors = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-green-500'];
const strengthLabels = ['Weak','Fair','Good','Strong'];
const strengthColors = ['text-red-500','text-orange-500','text-yellow-600','text-green-600'];

function checkReq(id, ok) {
    const el = document.getElementById(id);
    if (!el) return;
    el.querySelector('.req-dot').textContent = ok ? '✓' : '○';
    el.className = 'flex items-center gap-1.5 text-xs ' + (ok ? 'text-green-600 font-medium' : 'text-gray-400');
}

pwField.addEventListener('input', function() {
    const v = this.value;
    if (!v) { strengthWrap.style.display = 'none'; reqWrap.style.display = 'none'; return; }
    strengthWrap.style.display = 'block';
    reqWrap.style.display      = 'block';

    const ok = {
        len:   v.length >= 8,
        upper: /[A-Z]/.test(v),
        lower: /[a-z]/.test(v),
        num:   /[0-9]/.test(v),
        sym:   /[^A-Za-z0-9]/.test(v),
    };
    checkReq('req-len',   ok.len);
    checkReq('req-upper', ok.upper);
    checkReq('req-lower', ok.lower);
    checkReq('req-num',   ok.num);
    checkReq('req-sym',   ok.sym);

    const score = Object.values(ok).filter(Boolean).length - 1; // 0–4
    bars.forEach((b, i) => {
        b.className = 'h-1 flex-1 rounded-full transition-colors duration-300 ' + (i <= score ? barColors[Math.min(score,3)] : 'bg-gray-200');
    });
    const lbl = document.getElementById('pw-strength-label');
    lbl.textContent = strengthLabels[Math.min(score,3)];
    lbl.className   = 'text-xs font-medium ' + strengthColors[Math.min(score,3)];

    // re-check confirm match
    if (cfField.value) checkConfirm();
});

function checkConfirm() {
    const match = pwField.value === cfField.value;
    const err   = document.getElementById('err-confirm');
    if (!match && cfField.value) {
        cfField.classList.add('ring-2','ring-red-400');
        err.classList.remove('hidden'); err.style.display = 'flex';
    } else {
        cfField.classList.remove('ring-2','ring-red-400');
        err.classList.add('hidden'); err.style.display = '';
    }
}
cfField.addEventListener('input',  checkConfirm);
cfField.addEventListener('blur',   checkConfirm);

// ── Form submit guard ─────────────────────────────────────────
document.getElementById('register-form').addEventListener('submit', function(e) {
    let valid = true;

    const name  = document.getElementById('field-name').value.trim();
    const email = document.getElementById('field-email').value.trim();
    const phone = document.getElementById('field-phone').value.trim();
    const pw    = pwField.value;
    const cf    = cfField.value;

    if (!name) { setError('field-name', 'err-name', 'Full name is required.'); valid = false; }
    else if (!/^[\p{L}\s\-']+$/u.test(name)) { setError('field-name', 'err-name', 'Name may only contain letters, spaces, hyphens, or apostrophes.'); valid = false; }

    if (!email) { setError('field-email', 'err-email', 'Email address is required.'); valid = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setError('field-email', 'err-email', 'Please enter a valid email address.'); valid = false; }

    if (phone && !/^\+?[\d\s\-]{7,20}$/.test(phone)) { setError('field-phone', 'err-phone', 'Phone must be 7–20 digits. Only numbers, spaces, or hyphens allowed.'); valid = false; }

    if (!pw) { setError('field-password', null, ''); valid = false; }
    else if (pw.length < 8 || !/[A-Z]/.test(pw) || !/[a-z]/.test(pw) || !/[0-9]/.test(pw) || !/[^A-Za-z0-9]/.test(pw)) {
        document.getElementById('field-password').classList.add('ring-2','ring-red-400'); valid = false;
    }

    if (!cf) { valid = false; }
    else if (pw !== cf) { checkConfirm(); valid = false; }

    if (!valid) { e.preventDefault(); return; }

    // Show spinner
    document.getElementById('btn-label').textContent = 'Creating account…';
    document.getElementById('btn-spinner').classList.remove('hidden');
    document.getElementById('submit-btn').disabled = true;
});
</script>

</body>
</html>
