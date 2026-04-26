@extends('layouts.landlord')

@section('title', 'Add Property')

@section('content')

<div class="max-w-2xl">

    <h1 class="text-xl font-bold text-gray-900 mb-6">Add property</h1>

    @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <p class="text-sm text-gray-500 text-center mb-6">Get started by adding your property's address and details below.</p>

        <form id="property-form" method="POST" action="{{ route('landlord.properties.store') }}" class="space-y-5">
            @csrf

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Property Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="input-field" placeholder="e.g. Kisutu Apartment">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Property address</label>
                    <input type="text" name="address" value="{{ old('address') }}" required
                           class="input-field" placeholder="Street address">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">County / Region</label>
                    <div class="relative">
                        <select name="county" class="input-field appearance-none pr-8">
                            <option value="">Select region</option>
                            <option value="Dar es Salaam" {{ old('county') == 'Dar es Salaam' ? 'selected' : '' }}>Dar es Salaam</option>
                            <option value="Arusha" {{ old('county') == 'Arusha' ? 'selected' : '' }}>Arusha</option>
                            <option value="Mwanza" {{ old('county') == 'Mwanza' ? 'selected' : '' }}>Mwanza</option>
                            <option value="Dodoma" {{ old('county') == 'Dodoma' ? 'selected' : '' }}>Dodoma</option>
                            <option value="Tanga" {{ old('county') == 'Tanga' ? 'selected' : '' }}>Tanga</option>
                            <option value="Zanzibar" {{ old('county') == 'Zanzibar' ? 'selected' : '' }}>Zanzibar</option>
                            <option value="Other" {{ old('county') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                    <div class="relative">
                        <select name="city" class="input-field appearance-none pr-8">
                            <option value="">Select city</option>
                            <option value="Dar es Salaam" {{ old('city') == 'Dar es Salaam' ? 'selected' : '' }}>Dar es Salaam</option>
                            <option value="Kariakoo" {{ old('city') == 'Kariakoo' ? 'selected' : '' }}>Kariakoo</option>
                            <option value="Masaki" {{ old('city') == 'Masaki' ? 'selected' : '' }}>Masaki</option>
                            <option value="Kinondoni" {{ old('city') == 'Kinondoni' ? 'selected' : '' }}>Kinondoni</option>
                            <option value="Ilala" {{ old('city') == 'Ilala' ? 'selected' : '' }}>Ilala</option>
                            <option value="Temeke" {{ old('city') == 'Temeke' ? 'selected' : '' }}>Temeke</option>
                            <option value="Arusha" {{ old('city') == 'Arusha' ? 'selected' : '' }}>Arusha</option>
                            <option value="Mwanza" {{ old('city') == 'Mwanza' ? 'selected' : '' }}>Mwanza</option>
                            <option value="Dodoma" {{ old('city') == 'Dodoma' ? 'selected' : '' }}>Dodoma</option>
                            <option value="Other" {{ old('city') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Total area <span class="text-gray-400 font-normal">(m²)</span></label>
                <input type="number" name="total_area" value="{{ old('total_area') }}" step="0.01" min="0"
                       class="input-field" placeholder="0.00">
            </div>

            {{-- Hidden defaults for required DB fields --}}
            <input type="hidden" name="type" value="apartment">
            <input type="hidden" name="bedrooms" value="1">
            <input type="hidden" name="bathrooms" value="1">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Monthly Rent <span class="text-gray-400 font-normal">(Tshs)</span></label>
                <input type="number" name="rent_amount" value="{{ old('rent_amount') }}" min="0" step="0.01" required
                       class="input-field" placeholder="300,000">
            </div>

        </form>
    </div>

    <div class="flex items-center justify-end gap-3 mt-5">
        <a href="{{ route('landlord.properties.index') }}"
           class="text-sm font-medium text-gray-500 hover:text-gray-700 px-8 py-2.5 rounded-xl border border-gray-200 hover:border-gray-300 transition-colors">
            Cancel
        </a>
        <button type="submit" form="property-form"
                class="bg-primary-100 hover:bg-primary-200 text-primary-800 font-semibold px-8 py-2.5 rounded-xl text-sm transition-colors">
            Save
        </button>
    </div>

</div>
@endsection
