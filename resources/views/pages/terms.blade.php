<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-900 leading-tight flex items-center gap-2">
                <span>📜</span> Terms of Service
            </h2>
            <span class="text-xs bg-gray-200 text-gray-800 font-semibold px-3 py-1 rounded-full">
                Legal Agreement & Store Terms
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-6 text-gray-700 text-sm leading-relaxed">
                
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Terms of Service Overview</h3>
                    <p class="text-gray-600">
                        By accessing, browsing, or purchasing from Quirka, you agree to be bound by the following terms, conditions, and operational policies governing custom artwork production and shipment.
                    </p>
                </div>

                <hr class="border-gray-100">

                <!-- Section 1: Order Cancellations -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 font-bold text-base">
                        <span class="text-indigo-600">1.</span> Order Cancellation Window
                    </div>
                    <p>
                        Because our store operates a print-on-demand fulfillment model, custom production begins rapidly upon order submission.
                    </p>
                    <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-xl text-indigo-950 font-medium">
                        ⚠️ <strong>Strict Cancellation Rule:</strong> Orders may only be canceled <strong>BEFORE</strong> the order status transitions to <em>"In Production"</em> or <em>"Printing"</em>. Once physical printing or frame assembly has commenced, cancellation is strictly impossible, and the order will be completed and billed.
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Section 2: Cash on Delivery (COD) & Abandonment Policy -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 font-bold text-base">
                        <span class="text-indigo-600">2.</span> Cash on Delivery (COD) & Package Abandonment Policy
                    </div>
                    <p>
                        Cash on Delivery is provided as a convenience to our valued customers. By placing a COD order, you enter into a binding agreement to accept delivery and pay the designated total amount upon courier arrival.
                    </p>
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl space-y-2 text-amber-950">
                        <strong class="font-bold block text-amber-900">Failed Delivery & Abandonment Terms:</strong>
                        <ul class="list-disc list-inside space-y-1 text-xs leading-relaxed text-amber-900">
                            <li><strong>3 Delivery Attempts:</strong> The courier will make up to three (3) contact/delivery attempts. If the package is refused or the recipient is unreachable, the shipment will be returned to our facility.</li>
                            <li><strong>60-Day Facility Storage & Disposal:</strong> Returned custom COD items will be stored at our fulfillment facility for a maximum of <strong>60 days</strong>. If uncollected or unverified within 60 days, the item will be permanently disposed of.</li>
                            <li><strong>Revocation of COD Privileges:</strong> We reserve the strict right to blacklist accounts, addresses, and phone numbers that abandon COD orders, and to refuse future service or require prepaid payment methods for future orders.</li>
                        </ul>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Section 3: Intellectual Property & Custom Content -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 font-bold text-base">
                        <span class="text-indigo-600">3.</span> Intellectual Property & Reproduction Rights
                    </div>
                    <p>
                        All artwork, designs, typography, and product photography featured on Quirka are owned by or licensed to us. Unapproved reproduction, distribution, or commercial exploitation of any site content is strictly prohibited.
                    </p>
                </div>

                <hr class="border-gray-100">

                <!-- Section 4: Governing Law -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 font-bold text-base">
                        <span class="text-indigo-600">4.</span> Governing Law & Jurisdiction
                    </div>
                    <p>
                        These terms are governed by and construed in accordance with the applicable laws of Egypt. Any legal disputes arising out of or related to site purchases shall be subject to the exclusive jurisdiction of the competent courts in Cairo, Egypt.
                    </p>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
