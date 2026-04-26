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

{{-- ══════════════ POPUP BACKDROP ══════════════ --}}
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">

    {{-- Top label --}}
    <p class="self-start text-gray-400 text-sm font-medium ml-4 sm:ml-12 mb-3">Landlord signup page</p>

    {{-- Modal card --}}
    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden flex min-h-[640px]">

    {{-- ══════════════ LEFT — FORM ══════════════ --}}
    <div class="w-full lg:w-[50%] bg-primary-100 flex items-center justify-center px-8 sm:px-12 py-10">
        <div class="w-full max-w-sm">

            {{-- Logo + heading --}}
            <div class="flex flex-col items-center text-center mb-7">
                <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS"
                     class="h-14 w-auto object-contain mb-1">
                <h1 class="text-xl font-bold text-gray-900 mt-2">Create Your Account</h1>
                <p class="text-sm text-gray-500 mt-1 leading-snug max-w-xs">
                    Join thousands of property owners simplifying<br>their management.
                </p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="landlord">

                {{-- Full Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-white border-0 rounded-2xl px-4 py-3 text-sm text-gray-900
                                  shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400
                                  placeholder-gray-300 transition">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-white border-0 rounded-2xl px-4 py-3 text-sm text-gray-900
                                  shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400
                                  placeholder-gray-300 transition">
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
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

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-white border-0 rounded-2xl px-4 py-3 text-sm text-gray-900
                                  shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400
                                  placeholder-gray-300 transition">
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-primary-500 hover:bg-primary-600 active:bg-primary-700
                               text-white font-semibold py-3 rounded-full transition-colors duration-200 mt-1">
                    Create Account
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Already have an account?
                <a href="{{ route('login') }}" class="text-primary-700 font-semibold hover:underline">Sign In</a>
            </p>
        </div>
    </div>

    {{-- ══════════════ RIGHT — BRANDING ══════════════ --}}
    <div class="hidden lg:flex flex-1 bg-white flex-col items-center justify-center px-12 py-12 text-center">

        <h2 class="text-4xl font-extrabold text-gray-900 leading-tight mb-2">
            Managing Properties
        </h2>
        <h2 class="text-4xl font-extrabold leading-tight mb-10">
            Just Got <span class="text-primary-500">Smarter</span> !
        </h2>

        <img src="{{ asset('images/signUp/signUp.png') }}" alt="Property management illustration"
             class="w-72 h-64 object-contain animate-float">
    </div>

    </div> {{-- /modal card --}}
</div>

</body>
</html>
