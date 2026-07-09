<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#40916c">
    <title>@yield('title', 'Dashboard') – REMIS</title>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    </noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased bg-gray-50 font-sans">

<div class="flex min-h-screen">

    {{-- ===== SIDEBAR ===== --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 w-56 bg-primary-50 border-r border-primary-100 flex flex-col
                  transform -translate-x-full lg:translate-x-0 transition-transform duration-300 shadow-sm">

        {{-- Logo --}}
        <div class="flex items-center justify-center px-5 py-5 border-b border-primary-100">
            <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS" class="h-12 w-auto object-contain">
        </div>

        {{-- Role badge --}}
        <div class="px-4 py-2.5 border-b border-primary-100">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-700 bg-primary-100 px-3 py-1 rounded-full">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                Financial Officer
            </span>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-5 space-y-0.5" aria-label="Sidebar">

            <a href="{{ route('fo.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('fo.dashboard') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-3a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('fo.payments.index') }}"
               class="sidebar-link {{ request()->routeIs('fo.payments.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Payments
            </a>

            <a href="{{ route('fo.reports.index') }}"
               class="sidebar-link {{ request()->routeIs('fo.reports.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Reports
            </a>

            <a href="{{ route('fo.settings.index') }}"
               class="sidebar-link {{ request()->routeIs('fo.settings.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profile
            </a>
        </nav>

        {{-- Logout --}}
        <div class="px-3 py-4 border-t border-primary-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-500 hover:bg-red-50 hover:text-red-600 text-sm font-medium transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== MAIN AREA ===== --}}
    <div id="page-wrapper" class="flex-1 lg:ml-56 flex flex-col min-h-screen">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 sm:px-6 h-16 flex items-center justify-between">

            {{-- Left: hamburger --}}
            <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100"
                    aria-label="Toggle sidebar"
                    onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Right: avatar --}}
            <div class="flex items-center gap-3 ml-auto">
                <div class="flex items-center gap-2">
                    @if(Auth::user()->profile_picture)
                        <img src="{{ Storage::url(Auth::user()->profile_picture) }}"
                             alt="{{ Auth::user()->name }}"
                             class="w-9 h-9 rounded-full object-cover border border-primary-100 flex-shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'F', 0, 2)) }}
                        </div>
                    @endif
                    <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? '' }}</span>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main id="main-content" class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>

        <div id="page-scripts">@stack('scripts')</div>
    </div>
</div>

<div id="nav-progress" aria-hidden="true"></div>

<div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden"
     onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden')"></div>

<script>
document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
    const overlay = document.getElementById('sidebar-overlay');
    const sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('-translate-x-full')) {
        overlay.classList.remove('hidden');
    } else {
        overlay.classList.add('hidden');
    }
});
</script>

</body>
</html>
