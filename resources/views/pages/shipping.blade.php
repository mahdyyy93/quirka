<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white px-6 py-4 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    🚚
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">Shipping & Delivery Policy</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Pre-Designed Catalog Order Fulfillment</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs bg-indigo-50 text-indigo-800 font-semibold px-3.5 py-1.5 rounded-full border border-indigo-100 self-start sm:self-center">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Official Store Policy
            </span>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 padding-y-12">

            <!-- Hero Notice Card -->
            <div x-data="{ city: '{{ request('city_id', 'city_cairo') }}' }" class="bg-gradient-to-r from-gray-100 to-indigo-450 text-black rounded-2xl p-8 shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold mb-2">Estimated Delivery Window</h3>
                            <p class="text-black text-sm max-w-2xl leading-relaxed">
                                Our framed posters are pre-designed catalog items crafted for fast, premium fulfillment.
                                Usually orders are fulfilled and delivered within
                                <strong class="text-black underline decoration-indigo-400" x-text="['city_cairo', 'city_giza', 'city_qalyubia'].includes(city) ? '3-5 working days' : '4-8 working days'"></strong>
                                based on your selected city.
                            </p>
                        </div>
                        <label class="text-sm font-semibold text-gray-800 shrink-0">
                            <span class="block mb-1">Delivery city</span>
                            <select x-model="city" class="w-full sm:w-52 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-normal text-gray-700 focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(config('services.printlet.cities', []) as $cityId => $cityName)
                                    <option value="{{ $cityId }}">{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
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
                        Our fulfillment center and delivery partners operate from <strong>Sunday through Thursday</strong>, excluding official public holidays. Orders placed on Friday or Saturday are processed starting Sunday morning.
                    </p>
                </div>

                <!-- Processing Time -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-2">
                    <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600 font-bold text-lg mb-3">
                        🖼️
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">Processing & Packaging Time</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Preparing, framing, and quality-inspecting your selected pre-designed poster takes <strong>1 to 2 business days</strong> before being dispatched to our courier network.
                    </p>
                </div>

                <!-- Transit Time -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 space-y-2">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 font-bold text-lg mb-3">
                        ⚡
                    </div>
                    <h4 class="font-bold text-gray-900 text-base">Courier Transit Time</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Once handed over to local courier services, transit to your doorstep takes <strong>1 to 3 business days</strong> depending on your governorate and geographical zone.
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
                        <h5 class="font-semibold text-gray-900">Step 1: Order Confirmation & Verification</h5>
                        <p class="text-xs text-gray-500 mt-1">Prepaid orders enter processing immediately. Cash on Delivery (COD) orders undergo quick phone/SMS verification.</p>
                    </div>
                    <div class="relative">
                        <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full bg-indigo-600 border-4 border-white shadow"></div>
                        <h5 class="font-semibold text-gray-900">Step 2: Processing & Protective Packaging (1–2 Days)</h5>
                        <p class="text-xs text-gray-500 mt-1">Quality inspection, hand-framing assembly, and protective packaging at our fulfillment facility.</p>
                    </div>
                    <div class="relative">
                        <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full bg-indigo-600 border-4 border-white shadow"></div>
                        <h5 class="font-semibold text-gray-900">Step 3: Courier Dispatch & Delivery (1–3 Days)</h5>
                        <p class="text-xs text-gray-500 mt-1">Dispatched via local couriers with live status updates available on your account dashboard.</p>
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
