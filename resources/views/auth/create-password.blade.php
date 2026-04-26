<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Password – REMIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center font-sans antialiased">

<div class="w-full max-w-sm px-4">

    {{-- Logo --}}
    <div class="flex flex-col items-center mb-8">
        <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS" class="h-14 w-auto mb-3">
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-10">

        <h1 class="text-2xl font-bold text-gray-900 text-center mb-8">Create Password</h1>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.setup.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            {{-- Enter Password --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Enter Password</label>
                <div class="relative">
                    <input type="password" name="password" id="pwd"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm pr-10
                                  focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent"
                           required minlength="8">
                    <button type="button" onclick="togglePwd('pwd','eye1')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="eye1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                     -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Confirm Password --}}
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="pwd_confirm"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm pr-10
                                  focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent"
                           required minlength="8">
                    <button type="button" onclick="togglePwd('pwd_confirm','eye2')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="eye2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                     -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 rounded-xl
                           transition-colors duration-150 text-sm">
                Create Password
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        &copy; {{ date('Y') }} REMIS &mdash; Rental Management Information System
    </p>
</div>

<script>
function togglePwd(inputId, eyeId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
