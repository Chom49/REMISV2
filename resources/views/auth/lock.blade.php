<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Session Locked – REMIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    </noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-primary-50 font-sans min-h-screen flex items-center justify-center p-4">

@php
    $user    = Auth::user();
    $isLord  = $user?->isLandlord();
    $initials = strtoupper(substr($user?->name ?? 'U', 0, 2));
    $throttled = session('throttled');
@endphp

<div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="flex justify-center mb-8">
        <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS"
             class="h-14 w-auto object-contain">
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">

        {{-- Top accent bar --}}
        <div class="h-1 bg-gradient-to-r from-primary-500 to-primary-700"></div>

        <div class="p-8">

            {{-- Avatar + greeting --}}
            <div class="flex flex-col items-center mb-5">
                <div class="relative mb-3">
                    <div class="w-16 h-16 rounded-full bg-primary-500 flex items-center justify-center
                                text-white text-xl font-bold shadow-md">
                        {{ $initials }}
                    </div>
                    {{-- Role badge --}}
                    <span class="absolute -bottom-1 -right-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full
                                 {{ $isLord ? 'bg-primary-700 text-white' : 'bg-gray-800 text-white' }}">
                        {{ $isLord ? 'LANDLORD' : 'TENANT' }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-gray-900 mt-1">Welcome back</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $user?->name }}</p>
            </div>

            {{-- Security notice --}}
            <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0
                             00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <p class="text-xs text-amber-700 leading-relaxed">
                    Your session was locked for security after the browser was closed.
                    Re-enter your password to continue.
                </p>
            </div>

            {{-- Error / throttle message --}}
            @if($errors->any())
            <div class="flex items-start gap-2.5 rounded-xl px-4 py-3 mb-5
                        {{ $throttled ? 'bg-orange-50 border border-orange-300' : 'bg-red-50 border border-red-200' }}">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5 {{ $throttled ? 'text-orange-500' : 'text-red-500' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                             1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34
                             16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-xs leading-relaxed
                          {{ $throttled ? 'text-orange-700' : 'text-red-700' }}">
                    {{ $errors->first() }}
                </p>
            </div>
            @endif

            {{-- Unlock form --}}
            <form method="POST" action="{{ route('auth.unlock') }}" id="unlock-form">
                @csrf

                <div class="mb-5">
                    <label for="password"
                           class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password"
                               autocomplete="current-password"
                               class="w-full px-4 py-2.5 pr-11 border rounded-xl text-sm text-gray-900
                                      focus:outline-none focus:ring-2 focus:ring-primary-500
                                      focus:border-transparent placeholder-gray-400 transition-colors
                                      {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}"
                               placeholder="Enter your password"
                               {{ $throttled ? 'disabled' : '' }}
                               autofocus>
                        <button type="button" id="toggle-pw"
                                class="absolute right-3 top-1/2 -translate-y-1/2
                                       text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                                tabindex="-1" aria-label="Toggle password visibility">
                            <svg id="eye-show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                         9.542 7-1.274 4.057-5.064 7-9.542 7-4.477
                                         0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-hide" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478
                                         0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3
                                         3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532
                                         7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0
                                         8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        {{ $throttled ? 'disabled' : '' }}
                        class="w-full font-semibold text-sm py-3 rounded-xl transition-colors
                               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                               {{ $throttled
                                   ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                   : 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white' }}">
                    {{ $throttled ? 'Too many attempts — wait a moment' : 'Continue to Dashboard' }}
                </button>
            </form>

            {{-- Sign out --}}
            <div class="mt-5 pt-5 border-t border-gray-100 text-center">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="text-sm text-gray-400 hover:text-red-500 transition-colors
                                   focus:outline-none hover:underline underline-offset-2">
                        Not you? Sign out
                    </button>
                </form>
            </div>

        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        &copy; {{ date('Y') }} REMIS &mdash; All rights reserved.
    </p>
</div>

<script>
(function () {
    const btn   = document.getElementById('toggle-pw');
    const input = document.getElementById('password');
    const show  = document.getElementById('eye-show');
    const hide  = document.getElementById('eye-hide');

    if (btn && input) {
        btn.addEventListener('click', function () {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            show.classList.toggle('hidden', !visible);
            hide.classList.toggle('hidden', visible);
        });
    }

    // Prevent double-submit
    const form = document.getElementById('unlock-form');
    if (form) {
        form.addEventListener('submit', function () {
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Verifying…';
                submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    }
})();
</script>

</body>
</html>
