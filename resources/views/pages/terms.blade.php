<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white px-6 py-4 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-gray-900 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    📜
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">Terms of Service</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Legal Agreement & Store Terms</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs bg-gray-100 text-gray-700 font-semibold px-3.5 py-1.5 rounded-full border border-gray-200 self-start sm:self-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Official Store Policy
            </span>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-6 text-gray-700 text-sm leading-relaxed">
                
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Terms of Service Overview</h3>
                    <p class="text-gray-600">
                        By accessing, browsing, or purchasing pre-designed framed art prints from Quirka, you agree to be bound by the following terms, conditions, and operational policies.
                    </p>
                </div>

                <hr class="border-gray-100">

                <!-- Section 1: Order Cancellations -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 font-bold text-base">
                        <span class="text-indigo-600">1.</span> Order Cancellation Policy
                    </div>
                    <p>
                        Our team begins packaging and preparing your pre-designed framed poster immediately after order confirmation to ensure fast delivery.
                    </p>
                    <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-xl text-indigo-950 font-medium">
                        ⚠️ <strong>Cancellation Window:</strong> Orders may only be canceled <strong>BEFORE</strong> the order status transitions to <em>"In Production"</em> or <em>"Processing"</em>. Once an order enters preparation or courier dispatch, cancellation is no longer possible.
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Section 2: Cash on Delivery (COD) & Abandonment Policy -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 font-bold text-base">
                        <span class="text-indigo-600">2.</span> Cash on Delivery (COD) & Package Abandonment Terms
                    </div>
                    <p>
                        Cash on Delivery is provided as a payment option for customer convenience. Placing a COD order represents a binding request to receive and pay for the selected items upon delivery.
                    </p>
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl space-y-2 text-amber-950">
                        <strong class="font-bold block text-amber-900">Failed Delivery & Abandonment Terms:</strong>
                        <ul class="list-disc list-inside space-y-1 text-xs leading-relaxed text-amber-900">
                            <li><strong>3 Delivery Attempts:</strong> Local couriers will attempt delivery up to three (3) times. If uncollected or unreachable, the package is returned to our fulfillment facility.</li>
                            <li><strong>60-Day Facility Storage:</strong> Returned COD packages will be held at our storage facility for up to <strong>60 days</strong>. Unclaimed packages will be disposed of after 60 days.</li>
                            <li><strong>Revocation of COD Privileges:</strong> We reserve the right to restrict future COD privileges or decline future orders for accounts or delivery addresses with abandoned COD history.</li>
                        </ul>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Section 3: Intellectual Property & Custom Content -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 font-bold text-base">
                        <span class="text-indigo-600">3.</span> Catalog & Artwork Rights
                    </div>
                    <p>
                        All pre-designed poster artwork, typography, and images featured in our catalog are protected by intellectual property laws. Unauthorized copying, redistribution, or commercial use is strictly prohibited.
                    </p>
                </div>

                <hr class="border-gray-100">

                <!-- Section 4: Governing Law -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 font-bold text-base">
                        <span class="text-indigo-600">4.</span> Governing Law & Jurisdiction
                    </div>
                    <p>
                        These terms are governed by the laws of Egypt. Any disputes arising out of purchases shall be resolved under the jurisdiction of Cairo courts.
                    </p>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
