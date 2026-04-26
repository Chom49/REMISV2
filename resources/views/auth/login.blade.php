<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In – REMIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-900 min-h-screen">

{{-- ══════════════ POPUP BACKDROP ══════════════ --}}
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">

    {{-- Top label --}}
    <p class="self-start text-gray-400 text-sm font-medium ml-4 sm:ml-12 mb-3">Landlord login page</p>

    {{-- Modal card --}}
    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden flex min-h-[560px]">

    {{-- ══════════════ LEFT — FORM ══════════════ --}}
    <div class="w-full lg:w-[50%] bg-primary-100 flex items-center justify-center px-8 sm:px-12 py-12">
        <div class="w-full max-w-sm">

            {{-- Heading --}}
            <h1 class="text-4xl font-extrabold text-gray-900 leading-tight mb-10">
                Sign In to<br>Your Account
            </h1>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-white border-0 rounded-2xl px-4 py-3 text-sm text-gray-900
                                  shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400
                                  placeholder-gray-300 transition">
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full bg-white border-0 rounded-2xl px-4 py-3 text-sm text-gray-900
                                  shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400
                                  placeholder-gray-300 transition">
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-primary-500 hover:bg-primary-600 active:bg-primary-700
                               text-white font-semibold py-3 rounded-full transition-colors duration-200 mt-2">
                    Log In
                </button>
            </form>

            <p class="mt-7 text-center text-sm text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-primary-700 font-semibold hover:underline">Register Now</a>
            </p>
        </div>
    </div>

    {{-- ══════════════ RIGHT — BRANDING ══════════════ --}}
    <div class="hidden lg:flex flex-1 bg-white flex-col items-center justify-center px-12 py-12 text-center">

        {{-- Illustration --}}
        <img src="{{ asset('images/signIn/signIn.png') }}" alt="Welcome illustration"
             class="w-44 h-44 object-contain mb-8">

        <h2 class="text-3xl font-extrabold text-gray-900 uppercase tracking-wide mb-5">
            Glad To See You !
        </h2>

        <p class="text-gray-500 text-sm leading-7 max-w-xs mb-8">
            Managing properties doesn't have to be complicated.<br>
            Our platform gives you smart tools to stay organized<br>
            and in control
        </p>

        {{-- Sign Up CTA --}}
        <a href="{{ route('register') }}"
           class="bg-primary-500 hover:bg-primary-600 text-white font-semibold
                  px-12 py-3 rounded-full transition-colors duration-200 mb-9">
            Sign Up
        </a>

        {{-- Social icons --}}
        <div class="flex items-center gap-4 mt-1">

            {{-- Google --}}
            <a href="#"
               class="w-11 h-11 rounded-full border border-gray-200 flex items-center justify-center
                      text-gray-500 hover:border-primary-400 hover:text-primary-600 transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
            </a>

            {{-- Facebook --}}
            <a href="#"
               class="w-11 h-11 rounded-full border border-gray-200 flex items-center justify-center
                      text-gray-500 hover:border-blue-500 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </a>

            {{-- X / Twitter --}}
            <a href="#"
               class="w-11 h-11 rounded-full border border-gray-200 flex items-center justify-center
                      text-gray-500 hover:border-gray-800 hover:text-gray-900 transition-colors">
                <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.741l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
            </a>

            {{-- GitHub --}}
            <a href="#"
               class="w-11 h-11 rounded-full border border-gray-200 flex items-center justify-center
                      text-gray-500 hover:border-gray-800 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                </svg>
            </a>
        </div>
    </div>

    </div> {{-- /modal card --}}
</div>

</body>
</html>
