@if(!empty($showFoRecommendation))
<div id="fo-recommendation-banner"
     class="relative overflow-hidden rounded-2xl border border-indigo-300 bg-gradient-to-br from-indigo-600 to-purple-700 p-6 shadow-md mb-6">

    {{-- decorative circle --}}
    <div class="pointer-events-none absolute -right-8 -top-8 h-36 w-36 rounded-full bg-white/10"></div>
    <div class="pointer-events-none absolute -right-2 bottom-0 h-20 w-20 rounded-full bg-white/5"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-center gap-5">

        {{-- icon --}}
        <div class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>

        {{-- text --}}
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-indigo-200 uppercase tracking-wide mb-0.5">Recommendation</p>
            <h3 class="text-base font-bold text-white">Appoint a Financial Officer</h3>
            <p class="text-sm text-indigo-100 mt-1 leading-snug">
                You have more than 3 active tenants. Delegate rent collection,
                control numbers, and payment verification to a dedicated Financial Officer.
            </p>
        </div>

        {{-- action buttons --}}
        <div class="flex flex-row sm:flex-col gap-2 flex-shrink-0">
            <a href="{{ route('landlord.fo.create') }}"
               class="inline-flex items-center justify-center gap-2 bg-white hover:bg-indigo-50
                      text-indigo-700 font-bold text-sm px-5 py-2.5 rounded-xl transition-colors shadow-sm whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Officer
            </a>

            <form method="POST" action="{{ route('landlord.fo.dismiss-recommendation') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center w-full gap-2 bg-white/15 hover:bg-white/25
                               text-white border border-white/30 font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Dismiss
                </button>
            </form>
        </div>

    </div>
</div>
@endif
