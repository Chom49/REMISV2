@extends('layouts.landlord')

@section('title', 'Add Tenant')

@section('content')

<div class="max-w-2xl">

    <h1 class="text-xl font-bold text-gray-900 mb-6">Add Tenant</h1>

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

        <form id="tenant-form" method="POST" action="{{ route('landlord.tenants.store') }}" class="space-y-5">
            @csrf

            {{-- Name row --}}
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                           class="input-field" placeholder="e.g. Khadija">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                           class="input-field" placeholder="e.g. Mohamed">
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="input-field" placeholder="tenant@example.com">
            </div>

            {{-- Phone + DOB --}}
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="input-field" placeholder="+255 700 000 000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Date of birth</label>
                    <div class="relative">
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="input-field pr-10">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                <textarea name="notes" rows="4" class="input-field resize-none"
                          placeholder="Any relevant notes about the tenant…">{{ old('notes') }}</textarea>
            </div>

        </form>
    </div>

    <div class="flex items-center justify-end gap-3 mt-5">
        <a href="{{ route('landlord.tenants.index') }}"
           class="text-sm text-gray-600 hover:text-gray-800 font-medium px-5 py-2.5">Cancel</a>
        <button type="submit" form="tenant-form"
                class="bg-primary-100 hover:bg-primary-200 text-primary-800 font-semibold px-8 py-2.5 rounded-xl text-sm transition-colors">
            Save
        </button>
    </div>

</div>
@endsection
