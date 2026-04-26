{{-- ════════════════════════════════════════════════════════════
     AUTH MODAL — Login + Signup share the same overlay/card
     Triggered from the landing page via openModal('login'|'signup')
═══════════════════════════════════════════════════════════════ --}}
<div id="auth-overlay"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="modal-title">

    {{-- Dimmed backdrop (click to close) --}}
    <div id="auth-backdrop"
         class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity duration-200"
         onclick="closeModal()"></div>

    {{-- Modal card --}}
    <div id="auth-modal"
         class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden z-10
                max-h-[92vh] opacity-0 scale-95 transition-all duration-200 ease-out"
         role="document">

        {{-- Close button --}}
        <button onclick="closeModal()"
                class="absolute top-3 right-3 z-30 w-8 h-8 flex items-center justify-center rounded-full
                       bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-all
                       shadow-sm border border-gray-200"
                aria-label="Close">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- ════════════════════════ LOGIN PANEL ════════════════════════ --}}
        <div id="login-panel" class="hidden">
            <div class="flex flex-col lg:flex-row min-h-[440px]">

                {{-- ── Left: Form ─────────────────────────────────── --}}
                <div class="w-full lg:w-1/2 bg-primary-100 flex items-center justify-center px-10 sm:px-12 py-10">
                    <div class="w-full max-w-[275px]">

                        <h1 id="modal-title" class="text-2xl font-extrabold text-gray-900 leading-tight mb-7">
                            Sign In to<br>Your Account
                        </h1>

                        @if ($errors->any() && session('open_modal') === 'login')
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-3 py-2 text-xs">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="w-full bg-white border-0 rounded-xl px-4 py-2.5 text-sm text-gray-900
                                              shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Password</label>
                                <input type="password" name="password" required
                                       class="w-full bg-white border-0 rounded-xl px-4 py-2.5 text-sm text-gray-900
                                              shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 transition">
                            </div>

                            <button type="submit"
                                    class="w-full bg-primary-500 hover:bg-primary-600 active:bg-primary-700
                                           text-white font-semibold text-sm py-2.5 rounded-full transition-colors duration-200 mt-2">
                                Log In
                            </button>
                        </form>

                        <p class="mt-5 text-center text-xs text-gray-600">
                            Don't have an account?
                            <button type="button" onclick="switchModal('signup')"
                                    class="text-primary-700 font-semibold hover:underline">Register Now</button>
                        </p>
                    </div>
                </div>

                {{-- ── Right: Branding ───────────────────────────── --}}
                <div class="hidden lg:flex w-1/2 bg-white flex-col items-center justify-center px-8 py-10 text-center">

                    <img src="{{ asset('images/signIn/signIn.png') }}" alt="Welcome"
                         class="w-28 h-28 object-contain mb-4">

                    <h2 class="text-xl font-extrabold text-gray-900 uppercase tracking-wide mb-2">
                        Glad To See You !
                    </h2>

                    <p class="text-gray-500 text-xs leading-5 max-w-[200px] mb-4">
                        Managing properties doesn't have to be complicated. Our platform gives you smart tools to stay organized and in control.
                    </p>

                    <button type="button" onclick="switchModal('signup')"
                            class="bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm
                                   px-10 py-2.5 rounded-full transition-colors duration-200 mb-4">
                        Sign Up
                    </button>

                    {{-- Social icons --}}
                    <div class="flex items-center gap-3 mb-2">
                        <a href="#" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:border-primary-400 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:border-blue-500 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:border-gray-800 hover:text-gray-900 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.741l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:border-gray-800 hover:text-gray-900 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════ SIGNUP PANEL ════════════════════════ --}}
        <div id="signup-panel" class="hidden">
            <div class="flex flex-col lg:flex-row min-h-[520px]">

                {{-- ── Left: Form ─────────────────────────────────── --}}
                <div class="w-full lg:w-1/2 bg-primary-100 flex items-center justify-center px-10 sm:px-12 py-8">
                    <div class="w-full max-w-[275px]">

                        {{-- Logo + heading --}}
                        <div class="flex flex-col items-center text-center mb-5">
                            <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS"
                                 class="h-10 w-auto object-contain mb-1">
                            <h1 class="text-base font-bold text-gray-900 mt-1.5">Create Your Account</h1>
                            <p class="text-xs text-gray-500 mt-1 leading-snug">
                                Join thousands of property owners simplifying their management.
                            </p>
                        </div>

                        @if ($errors->any() && session('open_modal') === 'signup')
                            <div class="mb-3 bg-red-50 border border-red-200 text-red-700 rounded-lg px-3 py-2 text-xs">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="space-y-3.5">
                            @csrf
                            <input type="hidden" name="role" value="landlord">

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Full Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full bg-white border-0 rounded-xl px-4 py-2.5 text-sm text-gray-900
                                              shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="w-full bg-white border-0 rounded-xl px-4 py-2.5 text-sm text-gray-900
                                              shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="w-full bg-white border-0 rounded-xl px-4 py-2.5 text-sm text-gray-900
                                              shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Password</label>
                                <input type="password" name="password" required minlength="8"
                                       class="w-full bg-white border-0 rounded-xl px-4 py-2.5 text-sm text-gray-900
                                              shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Confirm Password</label>
                                <input type="password" name="password_confirmation" required minlength="8"
                                       class="w-full bg-white border-0 rounded-xl px-4 py-2.5 text-sm text-gray-900
                                              shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 transition">
                            </div>

                            <button type="submit"
                                    class="w-full bg-primary-500 hover:bg-primary-600 active:bg-primary-700
                                           text-white font-semibold text-sm py-2.5 rounded-full transition-colors duration-200 mt-2">
                                Create Account
                            </button>
                        </form>

                        <p class="mt-4 text-center text-xs text-gray-600">
                            Already have an account?
                            <button type="button" onclick="switchModal('login')"
                                    class="text-primary-700 font-semibold hover:underline">Sign In</button>
                        </p>
                    </div>
                </div>

                {{-- ── Right: Branding ───────────────────────────── --}}
                <div class="hidden lg:flex w-1/2 bg-white flex-col items-center justify-center px-8 py-10 text-center">

                    <h2 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1">
                        Managing Properties
                    </h2>
                    <h2 class="text-2xl font-extrabold leading-tight mb-6">
                        Just Got <span class="text-primary-500">Smarter</span> !
                    </h2>

                    <img src="{{ asset('images/signUp/signUp.png') }}" alt="Property management illustration"
                         class="w-52 h-44 object-contain animate-float">
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function openModal(type) {
    const overlay = document.getElementById('auth-overlay');
    const modal   = document.getElementById('auth-modal');

    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
    switchModal(type);
    document.body.style.overflow = 'hidden';

    // Entrance animation
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0', 'scale-95');
        modal.classList.add('opacity-100', 'scale-100');
    });

    setTimeout(() => {
        const firstInput = document.querySelector(`#${type}-panel input:not([type=hidden])`);
        if (firstInput) firstInput.focus();
    }, 80);
}

function closeModal() {
    const overlay = document.getElementById('auth-overlay');
    const modal   = document.getElementById('auth-modal');

    modal.classList.add('opacity-0', 'scale-95');
    modal.classList.remove('opacity-100', 'scale-100');

    setTimeout(() => {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        document.body.style.overflow = '';
    }, 200);
}

function switchModal(type) {
    document.getElementById('login-panel').classList.add('hidden');
    document.getElementById('signup-panel').classList.add('hidden');
    document.getElementById(`${type}-panel`).classList.remove('hidden');
}

// Close on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !document.getElementById('auth-overlay').classList.contains('hidden')) {
        closeModal();
    }
});
</script>
@endpush
