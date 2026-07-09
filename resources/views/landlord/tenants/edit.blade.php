@extends('layouts.landlord')
@section('title', 'Edit Tenant – ' . $user->name)

@section('content')

<div class="space-y-6 max-w-2xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('landlord.tenants.index') }}" class="hover:text-gray-700 transition-colors">Tenants</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('landlord.tenants.show', $user) }}" class="hover:text-gray-700 transition-colors">{{ $user->name }}</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Edit Profile</span>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-12 h-12 rounded-full bg-primary-500 flex items-center justify-center text-white text-base font-bold flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="text-sm text-gray-400">{{ $user->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('landlord.tenants.update', $user) }}" class="p-6 space-y-5">
            @csrf
            @method('PATCH')

            @if($errors->any())
                <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <ul class="space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Name fields (locked for TIN-verified tenants) --}}
            @if($user->tin)
            <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m0-6v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                </svg>
                <span>Name and TIN were verified via the TRA system and <strong>cannot be changed</strong>. You can still update the contact details below.</span>
            </div>
            @else
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        First Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="first_name"
                           value="{{ old('first_name', explode(' ', $user->name)[0]) }}"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                                  focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition
                                  @error('first_name') border-red-400 @enderror">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Last Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="last_name"
                           value="{{ old('last_name', implode(' ', array_slice(explode(' ', $user->name), 1))) }}"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                                  focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition
                                  @error('last_name') border-red-400 @enderror">
                </div>
            </div>
            @endif

            {{-- Section label for editable contact fields --}}
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide pt-1">Contact Details</p>

            {{-- Phone --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Phone Number</label>
                <input type="text" name="phone"
                       value="{{ old('phone', $user->phone) }}"
                       placeholder="+255 7XX XXX XXX"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
            </div>

            {{-- Gender / Nationality --}}
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Gender</label>
                    <select name="gender"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                                   focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition bg-white">
                        <option value="">— Not specified —</option>
                        <option value="male"   {{ old('gender', $user->gender) === 'male'   ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nationality</label>
                    @php $nationalities = ['Afghan','Albanian','Algerian','American','Andorran','Angolan','Antiguan','Argentine','Armenian','Australian','Austrian','Azerbaijani','Bahamian','Bahraini','Bangladeshi','Barbadian','Belarusian','Belgian','Belizean','Beninese','Bhutanese','Bolivian','Bosnian','Botswanan','Brazilian','British','Bruneian','Bulgarian','Burkinabe','Burundian','Cambodian','Cameroonian','Canadian','Cape Verdean','Central African','Chadian','Chilean','Chinese','Colombian','Comoran','Congolese','Costa Rican','Croatian','Cuban','Cypriot','Czech','Danish','Djiboutian','Dominican','Dutch','East Timorese','Ecuadorian','Egyptian','Emirati','Equatorial Guinean','Eritrean','Estonian','Eswatini','Ethiopian','Fijian','Finnish','French','Gabonese','Gambian','Georgian','German','Ghanaian','Greek','Grenadian','Guatemalan','Guinean','Guinea-Bissauan','Guyanese','Haitian','Honduran','Hungarian','Icelandic','Indian','Indonesian','Iranian','Iraqi','Irish','Israeli','Italian','Ivorian','Jamaican','Japanese','Jordanian','Kazakhstani','Kenyan','Kiribatian','Kuwaiti','Kyrgyzstani','Laotian','Latvian','Lebanese','Lesothan','Liberian','Libyan','Liechtensteiner','Lithuanian','Luxembourger','Macedonian','Malagasy','Malawian','Malaysian','Maldivian','Malian','Maltese','Marshallese','Mauritanian','Mauritian','Mexican','Micronesian','Moldovan','Monegasque','Mongolian','Montenegrin','Moroccan','Mozambican','Namibian','Nauruan','Nepalese','New Zealander','Nicaraguan','Nigerian','Nigerien','North Korean','Norwegian','Omani','Pakistani','Palauan','Palestinian','Panamanian','Papua New Guinean','Paraguayan','Peruvian','Filipino','Polish','Portuguese','Qatari','Romanian','Russian','Rwandan','Saint Lucian','Salvadoran','Samoan','Saudi Arabian','Senegalese','Serbian','Seychellois','Sierra Leonean','Singaporean','Slovak','Slovenian','Solomon Islander','Somali','South African','South Korean','South Sudanese','Spanish','Sri Lankan','Sudanese','Surinamese','Swedish','Swiss','Syrian','São Toméan','Taiwanese','Tajikistani','Tanzanian','Thai','Togolese','Tongan','Trinidadian','Tunisian','Turkish','Turkmen','Tuvaluan','Ugandan','Ukrainian','Uruguayan','Uzbekistani','Vanuatuan','Venezuelan','Vietnamese','Yemeni','Zambian','Zimbabwean']; @endphp
                    <select name="nationality"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                                   focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
                        <option value="">Select nationality…</option>
                        @foreach($nationalities as $nat)
                        <option value="{{ $nat }}" {{ old('nationality', $user->nationality) === $nat ? 'selected' : '' }}>{{ $nat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Read-only info --}}
            <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm text-gray-500">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Non-editable Fields</p>
                <div class="flex items-center justify-between">
                    <span>Email</span>
                    <span class="font-medium text-gray-700">{{ $user->email }}</span>
                </div>
                @if($user->tin)
                <div class="flex items-center justify-between">
                    <span>Full Name</span>
                    <span class="font-medium text-gray-700">{{ $user->name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>TIN</span>
                    <span class="font-medium text-gray-700 font-mono">{{ $user->tin }}</span>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('landlord.tenants.show', $user) }}"
                   class="text-sm font-semibold text-gray-600 hover:text-gray-800 px-5 py-2.5
                          border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                               font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
