@extends('layouts.tenant')

@section('title', 'Secure Checkout')

@section('content')

<div class="flex justify-center py-4">
<div class="w-full max-w-md">

    {{-- Errors --}}
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header --}}
        <div class="pt-8 pb-5 px-8 text-center border-b border-gray-100">
            <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS"
                 class="h-12 w-auto object-contain mx-auto mb-4">
            <h1 class="text-xl font-bold text-gray-900">Secure Checkout</h1>
            <p class="text-sm text-gray-400 mt-1">Choose your preferred payment method</p>
        </div>

        <div class="p-8">

            {{-- Payment method selector --}}
            <div class="flex gap-3 mb-6" id="payment-methods">

                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="method" value="card" class="sr-only peer" checked>
                    <div class="relative border-2 border-gray-200 peer-checked:border-blue-500 rounded-2xl p-3 flex items-center justify-center transition-all hover:border-gray-300">
                        <div class="absolute -top-2 -right-2 w-5 h-5 bg-blue-500 rounded-full items-center justify-center hidden peer-checked:flex">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <svg class="w-8 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </label>

                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="method" value="mixx" class="sr-only peer">
                    <div class="border-2 border-gray-200 peer-checked:border-blue-500 rounded-2xl p-3 flex items-center justify-center transition-all hover:border-gray-300 h-full">
                        <span class="text-sm font-bold text-gray-600 tracking-tight">Mixx<span class="text-blue-500">▶</span></span>
                    </div>
                </label>

                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="method" value="mpesa" class="sr-only peer">
                    <div class="border-2 border-gray-200 peer-checked:border-green-500 rounded-2xl p-3 flex flex-col items-center justify-center gap-0.5 transition-all hover:border-gray-300 h-full">
                        <span class="text-xs font-bold text-red-600">vodacom</span>
                        <span class="text-xs font-black text-green-600">M-PESA</span>
                    </div>
                </label>

                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="method" value="airtel" class="sr-only peer">
                    <div class="border-2 border-gray-200 peer-checked:border-red-500 rounded-2xl p-3 flex flex-col items-center justify-center gap-0.5 transition-all hover:border-gray-300 h-full">
                        <span class="text-xs font-black text-red-600">airtel</span>
                        <span class="text-[10px] font-semibold text-gray-500">money</span>
                    </div>
                </label>
            </div>

            {{-- Card form (shown when card method selected) --}}
            <form id="payment-form" method="POST" action="{{ route('tenant.payments.pay', $payment) }}"
                  class="space-y-4">
                @csrf

                {{-- Card fields (visible by default) --}}
                <div id="card-fields" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1.5">Cardholder Name</label>
                        <input type="text" name="cardholder_name" value="{{ Auth::user()->name }}"
                               class="input-field" placeholder="Full name">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1.5">Card Number</label>
                        <input type="text" name="card_number" value="**** **** **** 1111"
                               class="input-field tracking-widest" placeholder="**** **** **** ****"
                               maxlength="19">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1.5">Expiry date</label>
                            <input type="text" name="expiry" placeholder="MM/YY" maxlength="5"
                                   class="input-field">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1.5">CVV</label>
                            <input type="text" name="cvv" placeholder="CVV" maxlength="4"
                                   class="input-field">
                        </div>
                    </div>
                </div>

                {{-- Mobile money fields (hidden) --}}
                <div id="mobile-fields" class="space-y-4 hidden">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1.5">Phone Number</label>
                        <input type="text" name="mobile_number" value="{{ Auth::user()->phone ?? '' }}"
                               class="input-field" placeholder="+255 700 000 000">
                    </div>
                    <p class="text-xs text-gray-400">You'll receive a payment prompt on your phone.</p>
                </div>

                {{-- Amount display --}}
                <div class="bg-primary-50 rounded-2xl px-5 py-4 flex items-center justify-between">
                    <span class="text-sm text-gray-600">Amount due</span>
                    <span class="text-lg font-bold text-primary-700">Tzs {{ number_format($payment->amount, 0) }}</span>
                </div>

                <input type="hidden" name="payment_method" id="selected-method" value="card">

                <button type="submit"
                        class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-4 rounded-2xl text-base transition-colors">
                    Pay now
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('tenant.dashboard') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 underline">Go Back</a>
            </div>
        </div>
    </div>

</div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    const methodInputs = document.querySelectorAll('input[name="method"]');
    const cardFields   = document.getElementById('card-fields');
    const mobileFields = document.getElementById('mobile-fields');
    const hiddenMethod = document.getElementById('selected-method');

    methodInputs.forEach(input => {
        input.addEventListener('change', function() {
            hiddenMethod.value = this.value;
            const isCard = this.value === 'card';
            cardFields.classList.toggle('hidden', !isCard);
            mobileFields.classList.toggle('hidden', isCard);
        });
    });

    // Format card number spacing
    const cardInput = document.querySelector('input[name="card_number"]');
    cardInput?.addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
        this.value = val;
    });
})();
</script>
@endpush
