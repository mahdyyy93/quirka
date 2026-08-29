<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-900 leading-tight flex items-center gap-2">
                <span>🔄</span> Return & Replacement Policy
            </h2>
            <span class="text-xs bg-amber-100 text-amber-900 font-semibold px-3 py-1 rounded-full">
                Custom Print-on-Demand Policy
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Critical Policy Notice -->
            <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-6 shadow-sm flex items-start gap-4">
                <div class="text-3xl">⚠️</div>
                <div class="space-y-1">
                    <h3 class="font-bold text-amber-900 text-lg">Custom On-Demand Print Policy</h3>
                    <p class="text-amber-800 text-sm leading-relaxed">
                        Every product in our store is manufactured individually upon purchase. Because each framed poster is custom-made to your chosen size and frame specifications, 
                        <strong class="underline decoration-amber-500">we do not accept returns or issue refunds for buyer's remorse, incorrect size selection, or color preference changes</strong>.
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
                        If your item arrives damaged or contains a defect, you have <strong>exactly 14 calendar days</strong> from the official delivery date to report the issue and request a replacement.
                    </p>
                </div>

                <!-- Manufacturing Defects Only -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-3">
                    <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center font-bold text-lg">
                        🛠️
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">Manufacturing & Transit Defects Only</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Free replacements are granted <strong>exclusively for verifiable manufacturing flaws</strong> (e.g. print blemishes, wrong dimensions sent) or severe frame damage caused during transit.
                    </p>
                </div>

            </div>

            <!-- How to Request a Replacement -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-6">
                <h3 class="text-lg font-bold text-gray-900">How to Submit a Defect Claim</h3>
                
                <div class="space-y-4 text-sm text-gray-600">
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                        <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</span>
                        <div>
                            <strong class="text-gray-900 block font-semibold mb-0.5">Gather Photographic Evidence</strong>
                            Take clear, high-resolution photos showing the physical defect or broken frame, along with a picture of the shipping box label.
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                        <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</span>
                        <div>
                            <strong class="text-gray-900 block font-semibold mb-0.5">Email Support Team</strong>
                            Send an email to <a href="mailto:support@quirka.com" class="text-indigo-600 font-bold hover:underline">support@quirka.com</a> with your order number and attached photos within the 14-day window.
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                        <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</span>
                        <div>
                            <strong class="text-gray-900 block font-semibold mb-0.5">Free Expedited Replacement</strong>
                            Once reviewed and approved by our quality control team, a replacement unit will be printed and shipped to you at zero additional cost.
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-red-50 text-red-900 border border-red-100 rounded-xl text-xs space-y-1">
                    <strong class="font-bold block">Non-Eligible Claims:</strong>
                    <ul class="list-disc list-inside space-y-0.5 text-red-800">
                        <li>Customer selected incorrect poster dimensions or frame color at checkout.</li>
                        <li>Customer changed mind or no longer wants the print.</li>
                        <li>Claim submitted after the 14-day post-delivery eligibility window.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
