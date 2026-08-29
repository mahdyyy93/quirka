<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white px-6 py-4 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    🔄
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">Return & Replacement Policy</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Pre-Designed Product Return & Quality Guarantee</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs bg-amber-50 text-amber-900 font-semibold px-3.5 py-1.5 rounded-full border border-amber-200 self-start sm:self-center">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Official Store Policy
            </span>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Policy Quality Guarantee Notice -->
            <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-6 shadow-sm flex items-start gap-4">
                <div class="text-3xl">⭐</div>
                <div class="space-y-1">
                    <h3 class="font-bold text-amber-900 text-lg">Product Quality & Replacement Guarantee</h3>
                    <p class="text-amber-800 text-sm leading-relaxed">
                        We take great pride in our pre-designed framed poster collection. If any product from our catalog arrives with physical damage, glass breakage, or print defects, 
                        <strong class="underline decoration-amber-500">we will immediately issue a free replacement within 14 days of delivery</strong>.
                    </p>
                </div>
            </div>

            <!-- Policy Terms Breakdown Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- 14-Day Claim Window -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-3">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-lg">
                        ⏱️
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">14-Day Claim Window</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        If your item arrives damaged or contains a manufacturing defect, you have <strong>exactly 14 calendar days</strong> from the official delivery date to contact customer support for a replacement.
                    </p>
                </div>

                <!-- Manufacturing & Transit Defects -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-3">
                    <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center font-bold text-lg">
                        🛠️
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">Manufacturing & Transit Defects Covered</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Free replacements are granted for verifiable manufacturing defects (e.g. ink smudges, misaligned matting) or severe frame and glass damage caused during transit.
                    </p>
                </div>

            </div>

            <!-- How to Request a Replacement -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-6">
                <h3 class="text-lg font-bold text-gray-900">How to Submit a Replacement Claim</h3>
                
                <div class="space-y-4 text-sm text-gray-600">
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                        <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</span>
                        <div>
                            <strong class="text-gray-900 block font-semibold mb-0.5">Take Photos of Defect or Damage</strong>
                            Take clear photos showing the physical defect or broken frame, as well as the outer shipping box label.
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                        <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</span>
                        <div>
                            <strong class="text-gray-900 block font-semibold mb-0.5">Email Customer Support</strong>
                            Send your order number and photos to <a href="mailto:support@quirka.com" class="text-indigo-600 font-bold hover:underline">support@quirka.com</a> within the 14-day delivery window.
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                        <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</span>
                        <div>
                            <strong class="text-gray-900 block font-semibold mb-0.5">Expedited Replacement Dispatch</strong>
                            Upon verification by our team, a brand new replacement item from our catalog will be packaged and delivered to your address at no additional cost.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
