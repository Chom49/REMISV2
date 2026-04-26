@extends('layouts.app')

@section('title', 'REMIS – Rental Management System')

@section('content')

{{-- ===== NAVBAR ===== --}}
<nav class="fixed top-0 inset-x-0 z-50 bg-white/95 backdrop-blur-sm shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS" class="h-10 w-auto max-w-[9rem] object-contain">
            </a>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#features"    class="nav-link">Features</a>
                <a href="#how-it-works" class="nav-link">How It Works</a>
                <a href="#about"       class="nav-link">About</a>
                <a href="#contact"     class="nav-link">Contact</a>
            </div>

            {{-- Auth buttons --}}
            <div class="flex items-center gap-3">
                @auth
                    <span class="text-sm text-gray-600 hidden sm:block">Hi, {{ Auth::user()->name }}</span>
                    @if(Auth::user()->isLandlord())
                        <a href="{{ route('landlord.dashboard') }}" class="btn-primary text-sm">Dashboard</a>
                    @else
                        <a href="{{ route('tenant.dashboard') }}" class="btn-primary text-sm">Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600 transition-colors">Logout</button>
                    </form>
                @else
                    <button onclick="openModal('login')" class="btn-outline text-sm">Sign In</button>
                    <button onclick="openModal('signup')" class="btn-primary text-sm">Get Started</button>
                @endauth

                {{-- Mobile menu --}}
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="md:hidden hidden pb-4 border-t border-gray-100 pt-4">
            <div class="flex flex-col gap-3">
                <a href="#features"     class="nav-link py-1">Features</a>
                <a href="#how-it-works" class="nav-link py-1">How It Works</a>
                <a href="#about"        class="nav-link py-1">About</a>
                <a href="#contact"      class="nav-link py-1">Contact</a>
            </div>
        </div>
    </div>
</nav>

{{-- ===== HERO ===== --}}
<section class="relative pt-16 min-h-screen flex items-center overflow-hidden bg-primary-50">
    {{-- Decorative blobs --}}
    <div class="absolute top-20 right-0 w-96 h-96 bg-primary-200/40 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-primary-300/30 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 items-center">

            {{-- Text --}}
            <div>
                <span class="inline-flex items-center gap-2 bg-primary-700 text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16A8 8 0 0010 2zm0 14a6 6 0 110-12 6 6 0 010 12z"/></svg>
                    Rental Management Made Simple
                </span>

                <h1 class="text-4xl sm:text-5xl lg:text-4xl font-extrabold text-primary-900 leading-tight mb-6">
                   Real Estate <span class="text-primary-600">Management</span><br>Information System
                </h1>

                <p class="text-lg text-gray-600 mb-8 leading-relaxed max-w-lg">
                    REMIS is a comprehensive rental management platform that connects landlords and tenants — streamlining payments, maintenance, and leases all in one place.
                </p>

                <div class="flex flex-wrap gap-4">
                    <button onclick="openModal('signup')" class="btn-primary text-base px-8 py-3">
                        Start for Free
                    </button>
                    <a href="#how-it-works" class="btn-outline text-base px-8 py-3">
                        Learn More
                    </a>
                </div>

                {{-- Stats row --}}
                <div class="mt-12 flex flex-wrap gap-8">
                    <div>
                        <p class="text-3xl font-bold text-primary-700">500+</p>
                        <p class="text-sm text-gray-500 mt-1">Properties Managed</p>
                    </div>
                    <div class="w-px bg-gray-200 self-stretch hidden sm:block"></div>
                    <div>
                        <p class="text-3xl font-bold text-primary-700">1,200+</p>
                        <p class="text-sm text-gray-500 mt-1">Happy Tenants</p>
                    </div>
                    <div class="w-px bg-gray-200 self-stretch hidden sm:block"></div>
                    <div>
                        <p class="text-3xl font-bold text-primary-700">98%</p>
                        <p class="text-sm text-gray-500 mt-1">Satisfaction Rate</p>
                    </div>
                </div>
            </div>

            {{-- Hero image --}}
            <div class="relative flex justify-center lg:justify-end">
                <img src="{{ asset('images/landingPage/remispic.png') }}" alt="REMIS Property Management"
                     class="w-full max-w-md lg:max-w-xl object-contain mix-blend-multiply animate-float"
                     style="filter: drop-shadow(0 24px 40px rgba(27,67,50,0.18)) drop-shadow(0 8px 16px rgba(27,67,50,0.10));">
            </div>

        </div>
    </div>
</section>

{{-- ===== FEATURES ===== --}}
<section id="features" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-widest">Features</span>
            <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">Everything You Need</h2>
            <p class="mt-4 text-gray-500 text-lg max-w-2xl mx-auto">From property listings to rent collection, REMIS handles every aspect of property management.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">

            @php
            $features = [
                ['icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'title' => 'Property Management', 'desc' => 'Add and manage all your properties in one place. Track occupancy, maintenance, and rent with ease.'],
                ['icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'title' => 'Rent Collection', 'desc' => 'Automated rent tracking and payment history. Get instant visibility on paid, pending, and overdue rents.'],
                ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Lease Management', 'desc' => 'Create, track and manage lease agreements digitally. Get alerts on upcoming expirations.'],
                ['icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'title' => 'Maintenance Requests', 'desc' => 'Tenants submit requests instantly. Landlords track and resolve issues with full transparency.'],
                ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'title' => 'Reports & Analytics', 'desc' => 'Comprehensive dashboards showing revenue, occupancy rates, and financial summaries.'],
                ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'title' => 'Secure & Private', 'desc' => 'Your data is encrypted and secured. Role-based access ensures landlords and tenants only see what they need.'],
            ];
            @endphp

            @foreach($features as $f)
            <div class="card hover:shadow-md transition-shadow duration-300 group">
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-primary-600 transition-colors duration-300">
                    <svg class="w-6 h-6 text-primary-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $f['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ===== HOW IT WORKS ===== --}}
<section id="how-it-works" class="py-24 bg-primary-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-widest">Process</span>
            <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">How REMIS Works</h2>
            <p class="mt-4 text-gray-500 text-lg max-w-2xl mx-auto">Get started in minutes. No complex setup required.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-16 items-center">

            <div class="space-y-8">
                @php
                $steps = [
                    ['num' => '01', 'title' => 'Create Your Account', 'desc' => 'Sign up as a Landlord . To see your role and what features you access.'],
                    ['num' => '02', 'title' => 'Add Your Properties', 'desc' => 'Landlords list their properties with details like rent amount, type, and availability.'],
                    ['num' => '03', 'title' => 'Manage Leases & Tenants', 'desc' => 'Create lease agreements, assign tenants to properties, and track all rental activity.'],
                    ['num' => '04', 'title' => 'Track Payments & Requests', 'desc' => 'Monitor rent payments, handle maintenance requests, and generate reports effortlessly.'],
                ];
                @endphp

                @foreach($steps as $step)
                <div class="flex gap-5">
                    <div class="flex-shrink-0 w-12 h-12 bg-primary-700 text-white rounded-xl flex items-center justify-center font-bold text-sm">
                        {{ $step['num'] }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $step['title'] }}</h3>
                        <p class="text-gray-500 mt-1 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-center">
                <img src="{{ asset('images/signUp/signUp.png') }}" alt="How REMIS Works"
                     class="w-full max-w-md mx-auto object-contain mix-blend-multiply animate-float">
            </div>
        </div>
    </div>
</section>

{{-- ===== FOR LANDLORDS & TENANTS ===== --}}
<section id="about" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-widest">Who It's For</span>
            <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">Built for Everyone</h2>
        </div>

        <div class="grid md:grid-cols-2 gap-8">

            {{-- Landlord card --}}
            <div class="relative overflow-hidden bg-primary-700 rounded-3xl p-8 text-white">
                <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">For Landlords</h3>
                    <ul class="space-y-2 text-primary-100 text-sm mb-8">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Manage multiple properties</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Track rent payments</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Handle maintenance requests</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>View revenue analytics</li>
                    </ul>
                    <button onclick="openModal('signup')" class="btn-white text-sm">
                        Register as Landlord
                    </button>
                </div>
            </div>

            {{-- Tenant card --}}
            <div class="relative overflow-hidden bg-gray-900 rounded-3xl p-8 text-white">
                <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">For Tenants</h3>
                    <ul class="space-y-2 text-gray-400 text-sm mb-8">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>View lease details</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Track payment history</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Submit maintenance requests</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Communicate with landlord</li>
                    </ul>
                    <button onclick="openModal('login')" class="bg-white text-gray-900 px-6 py-2.5 rounded-lg font-semibold text-sm hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-900">
                      Login as Tenant
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="relative py-24 bg-primary-50 overflow-hidden">
    {{-- Decorative blobs (mirrors hero) --}}
    <div class="absolute top-0 right-0 w-96 h-96 bg-primary-200/40 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-primary-300/30 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-primary-900 mb-4">Ready to Simplify Property Management?</h2>
        <p class="text-gray-600 text-lg mb-8">Join hundreds of landlords and tenants already using REMIS.</p>
        <div class="flex flex-wrap gap-4 justify-center">
            <button onclick="openModal('signup')" class="btn-primary text-base px-10 py-3">
                Get Started Free
            </button>
            <button onclick="openModal('login')" class="btn-outline text-base px-10 py-3">
                Sign In
            </button>
        </div>
    </div>
</section>

{{-- ===== FOOTER ===== --}}
<footer id="contact" class="bg-white border-t border-gray-200 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

            <div class="lg:col-span-2">
                <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS"
                     class="h-14 w-auto object-contain mb-5">

                <p class="text-sm leading-relaxed text-gray-500 max-w-xs">
                    REMIS is a modern rental management system that simplifies property management for landlords and tenants alike.
                </p>
            </div>

            <div>
                <h4 class="text-gray-900 font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li><a href="#features"     class="hover:text-primary-600 transition-colors">Features</a></li>
                    <li><a href="#how-it-works"  class="hover:text-primary-600 transition-colors">How It Works</a></li>
                    <li><button onclick="openModal('login')"  class="hover:text-primary-600 transition-colors">Sign In</button></li>
                    <li><button onclick="openModal('signup')" class="hover:text-primary-600 transition-colors">Register</button></li>
                </ul>
            </div>

            <div>
                <h4 class="text-gray-900 font-semibold mb-4">Contact</h4>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        support@remis.com
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        +255 756 301 304
                    </li>
                     <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        +255 715 533 001
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-8 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} REMIS – Rental Management Information System. All rights reserved.
        </div>
    </div>
</footer>

{{-- ===== AUTH MODALS ===== --}}
@include('components.auth-modals')

@endsection

@push('scripts')
<script>
// Mobile menu toggle
document.getElementById('mobile-menu-btn').addEventListener('click', () => {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});

// Smooth scroll for nav links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

// Open modal if redirected back with errors
@if(session('open_modal'))
    openModal('{{ session('open_modal') }}');
@endif
</script>
@endpush
