@extends('layouts.landlord')
@section('title', 'Add Tenant')

@section('content')

<div class="max-w-2xl">

    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Add Tenant</h1>
        <p class="text-sm text-gray-400 mt-0.5">Choose how you'd like to add the tenant.</p>
    </div>

    @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <p class="font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Mode Selector ─────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-3 mb-5">

        <button type="button" onclick="setMode('tin')" id="mode-btn-tin"
                class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 border-primary-500
                       bg-primary-50 text-primary-700 transition-all focus:outline-none">
            <div class="w-9 h-9 rounded-xl bg-primary-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="text-center">
                <p class="text-sm font-bold">Look up by TIN</p>
                <p class="text-xs text-primary-500 mt-0.5">Verify via TRA system</p>
            </div>
        </button>

        <button type="button" onclick="setMode('manual')" id="mode-btn-manual"
                class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 border-gray-200
                       bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all focus:outline-none">
            <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div class="text-center">
                <p class="text-sm font-bold">Enter Manually</p>
                <p class="text-xs text-gray-400 mt-0.5">Fill in details by hand</p>
            </div>
        </button>

    </div>


    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- TIN LOOKUP PANEL                                        --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div id="panel-tin">

        {{-- TIN input card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
            <div class="flex items-center gap-2 pb-3 border-b border-gray-100 mb-5">
                <div class="w-7 h-7 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-800">TIN Verification</h2>
                    <p class="text-xs text-gray-400">Fetch taxpayer details from the TRA system</p>
                </div>
            </div>

            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                TIN Number <span class="text-red-400">*</span>
            </label>
            <div class="flex gap-2">
                <input id="tin-input" type="text"
                       class="input-field flex-1" placeholder="e.g. 100-200-300"
                       oninput="resetTinState()"
                       onkeydown="if(event.key==='Enter'){event.preventDefault();verifyTin();}">
                <button type="button" onclick="verifyTin()" id="verify-btn"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 hover:bg-primary-600
                               text-white text-sm font-bold rounded-xl transition-colors flex-shrink-0
                               focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-1">
                    <svg id="verify-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <svg id="verify-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span id="verify-label">Verify</span>
                </button>
            </div>

            {{-- Inline feedback shown directly below TIN input --}}
            <div id="tin-feedback" class="hidden mt-3"></div>
        </div>

        {{-- Verified result card (hidden until successful lookup) --}}
        <div id="tin-result-card" class="hidden">

            {{-- Taxpayer identity summary --}}
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-emerald-800">Taxpayer Verified</p>
                        <p id="result-name" class="text-base font-bold text-gray-900 mt-1"></p>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2">
                            <span class="text-xs text-emerald-700">
                                TIN: <span id="result-tin" class="font-mono font-semibold"></span>
                            </span>
                            <span id="result-vat-badge"
                                  class="hidden text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                VAT Registered
                            </span>
                            <span id="result-vrn-wrap" class="hidden text-xs text-emerald-700">
                                VRN: <span id="result-vrn" class="font-mono font-semibold"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact form (TIN flow) --}}
            <form id="tin-form" method="POST" action="{{ route('landlord.tenants.store') }}">
                @csrf
                <input type="hidden" name="mode"       value="tin">
                <input type="hidden" name="first_name" id="hidden-first-name">
                <input type="hidden" name="last_name"  id="hidden-last-name">
                <input type="hidden" name="tin"        id="hidden-tin">
                @if($fromProperty) <input type="hidden" name="from_property" value="{{ $fromProperty }}"> @endif
                @if($fromUnit)     <input type="hidden" name="from_unit"     value="{{ $fromUnit }}"> @endif
                @if($fromLease)    <input type="hidden" name="from_lease"    value="{{ $fromLease }}"> @endif

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5 mb-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                        <div class="w-7 h-7 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-800">Contact Details</h2>
                            <p class="text-xs text-gray-400">Required to create the tenant account</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Email Address <span class="text-red-400">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="input-field" placeholder="tenant@example.com">
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="input-field" placeholder="+255 700 000 000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                            <div class="relative">
                                <select name="gender" class="input-field appearance-none pr-10">
                                    <option value="" disabled selected>Select gender</option>
                                    <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea name="notes" rows="2" class="input-field resize-none"
                                  placeholder="Any relevant notes…">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Invitation Delivery Channel --}}
                @include('landlord.tenants._channel_picker', ['formId' => 'tin'])

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col sm:flex-row gap-3">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600
                                   text-white font-bold px-6 py-3.5 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirm &amp; Add Tenant
                    </button>
                    <a href="{{ route('landlord.tenants.index') }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center text-sm font-semibold
                              text-gray-600 hover:text-gray-900 px-6 py-3.5 rounded-xl border border-gray-200
                              hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>

        </div>{{-- /#tin-result-card --}}
    </div>{{-- /#panel-tin --}}


    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- MANUAL ENTRY PANEL                                      --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div id="panel-manual" class="hidden">

        <form id="manual-form" method="POST" action="{{ route('landlord.tenants.store') }}">
            @csrf
            <input type="hidden" name="mode" value="manual">
            @if($fromProperty) <input type="hidden" name="from_property" value="{{ $fromProperty }}"> @endif
            @if($fromUnit)     <input type="hidden" name="from_unit"     value="{{ $fromUnit }}"> @endif
            @if($fromLease)    <input type="hidden" name="from_lease"    value="{{ $fromLease }}"> @endif

            {{-- Personal Information --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5 mb-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                    <div class="w-7 h-7 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800">Personal Information</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            First Name <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                               class="input-field" placeholder="e.g. Khadija">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Last Name <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                               class="input-field" placeholder="e.g. Mohamed">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email Address <span class="text-red-400">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="input-field" placeholder="tenant@example.com">
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="input-field" placeholder="+255 700 000 000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date of Birth</label>
                        <div class="relative">
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   class="input-field pr-10">
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                        <div class="relative">
                            <select name="gender" class="input-field appearance-none pr-10">
                                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select gender</option>
                                <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nationality</label>
                        <select name="nationality" class="input-field">
                            <option value="">Select nationality…</option>
                            @php $nationalities = ['Afghan','Albanian','Algerian','American','Andorran','Angolan','Antiguan','Argentine','Armenian','Australian','Austrian','Azerbaijani','Bahamian','Bahraini','Bangladeshi','Barbadian','Belarusian','Belgian','Belizean','Beninese','Bhutanese','Bolivian','Bosnian','Botswanan','Brazilian','British','Bruneian','Bulgarian','Burkinabe','Burundian','Cambodian','Cameroonian','Canadian','Cape Verdean','Central African','Chadian','Chilean','Chinese','Colombian','Comoran','Congolese','Costa Rican','Croatian','Cuban','Cypriot','Czech','Danish','Djiboutian','Dominican','Dutch','East Timorese','Ecuadorian','Egyptian','Emirati','Equatorial Guinean','Eritrean','Estonian','Eswatini','Ethiopian','Fijian','Finnish','French','Gabonese','Gambian','Georgian','German','Ghanaian','Greek','Grenadian','Guatemalan','Guinean','Guinea-Bissauan','Guyanese','Haitian','Honduran','Hungarian','Icelandic','Indian','Indonesian','Iranian','Iraqi','Irish','Israeli','Italian','Ivorian','Jamaican','Japanese','Jordanian','Kazakhstani','Kenyan','Kiribatian','Kuwaiti','Kyrgyzstani','Laotian','Latvian','Lebanese','Lesothan','Liberian','Libyan','Liechtensteiner','Lithuanian','Luxembourger','Macedonian','Malagasy','Malawian','Malaysian','Maldivian','Malian','Maltese','Marshallese','Mauritanian','Mauritian','Mexican','Micronesian','Moldovan','Monegasque','Mongolian','Montenegrin','Moroccan','Mozambican','Namibian','Nauruan','Nepalese','New Zealander','Nicaraguan','Nigerian','Nigerien','North Korean','Norwegian','Omani','Pakistani','Palauan','Palestinian','Panamanian','Papua New Guinean','Paraguayan','Peruvian','Filipino','Polish','Portuguese','Qatari','Romanian','Russian','Rwandan','Saint Lucian','Salvadoran','Samoan','Saudi Arabian','Senegalese','Serbian','Seychellois','Sierra Leonean','Singaporean','Slovak','Slovenian','Solomon Islander','Somali','South African','South Korean','South Sudanese','Spanish','Sri Lankan','Sudanese','Surinamese','Swedish','Swiss','Syrian','São Toméan','Taiwanese','Tajikistani','Tanzanian','Thai','Togolese','Tongan','Trinidadian','Tunisian','Turkish','Turkmen','Tuvaluan','Ugandan','Ukrainian','Uruguayan','Uzbekistani','Vanuatuan','Venezuelan','Vietnamese','Yemeni','Zambian','Zimbabwean']; @endphp
                            @foreach($nationalities as $nat)
                            <option value="{{ $nat }}" {{ old('nationality') === $nat ? 'selected' : '' }}>{{ $nat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100 mb-5">
                    <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 10h16M4 14h10"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800">Notes</h2>
                </div>
                <textarea name="notes" rows="3" class="input-field resize-none"
                          placeholder="Any relevant notes about the tenant (references, special conditions, etc.)…">{{ old('notes') }}</textarea>
            </div>

            {{-- Invitation Delivery Channel --}}
            @include('landlord.tenants._channel_picker', ['formId' => 'manual'])

            {{-- Save / Cancel --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col sm:flex-row gap-3">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600
                               text-white font-bold px-6 py-3.5 rounded-xl text-sm transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Tenant
                </button>
                <a href="{{ route('landlord.tenants.index') }}"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center text-sm font-semibold
                          text-gray-600 hover:text-gray-900 px-6 py-3.5 rounded-xl border border-gray-200
                          hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>

    </div>{{-- /#panel-manual --}}

</div>

<script>
const verifyUrl = '{{ route('landlord.tenants.verify-tin') }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let tinVerified = false;

// ── Mode switching ────────────────────────────────────────────────
function setMode(mode) {
    const isTin = mode === 'tin';
    document.getElementById('panel-tin').classList.toggle('hidden', !isTin);
    document.getElementById('panel-manual').classList.toggle('hidden', isTin);

    const activeClass   = 'border-primary-500 bg-primary-50 text-primary-700';
    const inactiveClass = 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50';

    const btnTin    = document.getElementById('mode-btn-tin');
    const btnManual = document.getElementById('mode-btn-manual');

    btnTin.classList.remove(...(isTin ? inactiveClass : activeClass).split(' '));
    btnTin.classList.add(   ...(isTin ? activeClass   : inactiveClass).split(' '));
    btnManual.classList.remove(...(isTin ? activeClass : inactiveClass).split(' '));
    btnManual.classList.add(   ...(isTin ? inactiveClass : activeClass).split(' '));
}

// ── Feedback helpers ──────────────────────────────────────────────
const feedbackCfg = {
    success: { wrap: 'bg-emerald-50 border-emerald-200 text-emerald-700', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    error:   { wrap: 'bg-red-50 border-red-200 text-red-700',             icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
    warning: { wrap: 'bg-amber-50 border-amber-200 text-amber-700',       icon: 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z' },
};

function showFeedback(type, message) {
    const el  = document.getElementById('tin-feedback');
    const cfg = feedbackCfg[type] || feedbackCfg.error;
    el.className = `flex items-start gap-2.5 ${cfg.wrap} border rounded-xl px-4 py-3 text-sm mt-3`;
    el.innerHTML = `
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${cfg.icon}"/>
        </svg>
        <span>${message}</span>`;
    el.classList.remove('hidden');
}

function hideFeedback() {
    document.getElementById('tin-feedback').classList.add('hidden');
}

function resetTinState() {
    tinVerified = false;
    hideFeedback();
    document.getElementById('tin-result-card').classList.add('hidden');
    document.getElementById('result-vat-badge').classList.add('hidden');
    document.getElementById('result-vrn-wrap').classList.add('hidden');
}

// ── TIN verification via AJAX ─────────────────────────────────────
async function verifyTin() {
    const tin = document.getElementById('tin-input').value.trim();
    if (!tin) { showFeedback('warning', 'Please enter a TIN number before verifying.'); return; }

    // Show loading state
    document.getElementById('verify-icon').classList.add('hidden');
    document.getElementById('verify-spinner').classList.remove('hidden');
    document.getElementById('verify-label').textContent = 'Verifying…';
    document.getElementById('verify-btn').disabled = true;
    hideFeedback();
    document.getElementById('tin-result-card').classList.add('hidden');

    try {
        const res  = await fetch(verifyUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body:    JSON.stringify({ tin }),
        });
        const json = await res.json();

        if (json.success) {
            populateResult(tin, json.data);
            showFeedback('success', 'Taxpayer verified. Fill in the contact details below, then click <strong>Confirm &amp; Add Tenant</strong>.');
        } else {
            showFeedback('error', json.message || 'TIN verification failed. Please try again.');
        }
    } catch {
        showFeedback('error', 'Network error. Please check your connection and try again.');
    } finally {
        document.getElementById('verify-icon').classList.remove('hidden');
        document.getElementById('verify-spinner').classList.add('hidden');
        document.getElementById('verify-label').textContent = 'Verify';
        document.getElementById('verify-btn').disabled = false;
    }
}

function populateResult(tin, data) {
    const firstName = (data.first_name  || '').trim();
    const midName   = (data.middle_name || '').trim();
    const lastName  = (data.last_name   || '').trim();
    const fullName  = [firstName, midName, lastName].filter(Boolean).join(' ')
                   || data.taxpayer_name || 'Unknown';

    document.getElementById('result-name').textContent = fullName;
    document.getElementById('result-tin').textContent  = tin;

    if (data.has_vat) {
        document.getElementById('result-vat-badge').classList.remove('hidden');
        if (data.vrn) {
            document.getElementById('result-vrn-wrap').classList.remove('hidden');
            document.getElementById('result-vrn').textContent = data.vrn;
        }
    }

    // Populate hidden fields for the form submission
    document.getElementById('hidden-first-name').value = firstName || fullName;
    document.getElementById('hidden-last-name').value  = lastName;
    document.getElementById('hidden-tin').value         = tin;

    document.getElementById('tin-result-card').classList.remove('hidden');
    tinVerified = true;
}

// Guard: prevent TIN form submit if lookup not yet confirmed
document.getElementById('tin-form').addEventListener('submit', function (e) {
    if (!tinVerified) {
        e.preventDefault();
        showFeedback('warning', 'Please verify the TIN number first before confirming.');
        document.getElementById('tin-input').focus();
    }
});
</script>

@endsection
