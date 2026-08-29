<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-900 leading-tight flex items-center gap-2">
                <span>🚚</span> Shipping & Delivery Policy
            </h2>
            <span class="text-xs bg-indigo-100 text-indigo-800 font-semibold px-3 py-1 rounded-full">
                Print-on-Demand Order Fulfillment
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Hero Notice Card -->
            <div class="bg-gradient-to-r from-gray-900 to-indigo-950 text-white rounded-2xl p-8 shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold mb-2">Estimated Delivery Window</h3>
                    <p class="text-indigo-200 text-sm max-w-2xl leading-relaxed">
                        Every framed poster is custom printed and hand-assembled upon order. You can expect your delivery within 
                        <strong class="text-white underline decoration-indigo-400">1 to 5 working days</strong> from order creation.
                    </p>
                </div>
                <div class="absolute -right-6 -bottom-6 text-indigo-950/40 text-9xl font-black select-none pointer-events-none">
                    1-5d
                </div>
            </div>

            <!-- Key Terms Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Business Days -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-2">
                    <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-lg mb-3">
                        📅
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">Business Operating Days</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Our production facility and fulfillment teams operate from <strong>Sunday through Thursday</strong>, excluding official public holidays. Orders placed on Friday or Saturday are queued for Sunday processing.
                    </p>
                </div>

                <!-- Production Time -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-2">
                    <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600 font-bold text-lg mb-3">
                        🖼️
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">Production Time</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Because each poster is printed on demand with museum-grade archival paper and custom framing, initial printing and framing takes <strong>1 to 2 business days</strong> before dispatch.
                    </p>
                </div>

                <!-- Transit Time -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-2">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 font-bold text-lg mb-3">
                        ⚡
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">Courier Transit Time</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Once handed over to local courier services, transit to your doorstep takes <strong>1 to 3 business days</strong> depending on your geographical zone and governorate.
                    </p>
                </div>

                <!-- Delivery Attempts -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-2">
                    <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600 font-bold text-lg mb-3">
                        📍
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">Delivery Attempts Policy</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Couriers will attempt delivery a <strong>maximum of three (3) times</strong>. If customer is unreachable or refuses contact after 3 attempts, the shipment is returned to our main facility.
                    </p>
                </div>
            </div>

            <!-- Detailed Policy breakdown -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-6 text-gray-700 text-sm">
                <h3 class="text-lg font-bold text-gray-900">Delivery Timeline Breakdown</h3>
                
                <div class="relative border-l-2 border-indigo-100 ml-3 pl-6 space-y-6">
                    <div class="relative">
                        <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full bg-indigo-600 border-4 border-white shadow"></div>
                        <h5 class="font-semibold text-gray-900">Step 1: Order Placement & Verification</h5>
                        <p class="text-xs text-gray-500 mt-1">Prepaid orders enter production immediately. Cash on Delivery (COD) orders undergo brief phone/SMS verification.</p>
                    </div>
                    <div class="relative">
                        <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full bg-indigo-600 border-4 border-white shadow"></div>
                        <h5 class="font-semibold text-gray-900">Step 2: Custom Printing & Framing (1–2 Days)</h5>
                        <p class="text-xs text-gray-500 mt-1">Archival pigment printing, matting, glass mounting, and frame assembly at our specialized facility.</p>
                    </div>
                    <div class="relative">
                        <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full bg-indigo-600 border-4 border-white shadow"></div>
                        <h5 class="font-semibold text-gray-900">Step 3: Courier Dispatch & Transit (1–3 Days)</h5>
                        <p class="text-xs text-gray-500 mt-1">Handed to shipping courier with live tracking updates dispatched via email/SMS.</p>
                    </div>
                </div>

                <hr class="border-gray-100">

                <div class="flex items-center justify-between text-xs text-gray-500 pt-2">
                    <span>Need help with an active shipment?</span>
                    <a href="mailto:support@quirka.com" class="text-indigo-600 font-semibold hover:underline">Contact Support &rarr;</a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
