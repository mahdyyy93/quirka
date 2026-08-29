<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-900 leading-tight flex items-center gap-2">
                <span>🔒</span> Privacy Policy
            </h2>
            <span class="text-xs bg-emerald-100 text-emerald-900 font-semibold px-3 py-1 rounded-full">
                Data Protection & Privacy Terms
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-6 text-gray-700 text-sm leading-relaxed">
                
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Privacy & Data Protection Policy</h3>
                    <p class="text-gray-600">
                        At Quirka, we are committed to respecting your personal privacy and protecting the security of your contact and shipping information. This policy outlines how we collect, store, use, and share data.
                    </p>
                </div>

                <hr class="border-gray-100">

                <!-- Section 1: Information We Collect -->
                <div class="space-y-3">
                    <h4 class="font-bold text-gray-900 text-base">1. Information We Collect</h4>
                    <p>When you browse our storefront or place an order, we collect the following necessary personal information:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-600 pl-2">
                        <li><strong>Contact Details:</strong> Full Name, Email Address, Phone Number.</li>
                        <li><strong>Shipping Information:</strong> Physical Street Address, City, Region/Governorate, Postal Code.</li>
                        <li><strong>Order Metadata:</strong> Selected poster designs, print sizes, frame finishes, and payment preferences.</li>
                    </ul>
                </div>

                <hr class="border-gray-100">

                <!-- Section 2: CRITICAL - Third-Party Fulfillment Clause -->
                <div class="space-y-3 bg-emerald-50/60 p-6 border border-emerald-200 rounded-2xl">
                    <h4 class="font-bold text-emerald-950 text-base flex items-center gap-2">
                        <span>🏭</span> 2. Third-Party Manufacturing & Delivery Partner Sharing Clause
                    </h4>
                    <p class="text-emerald-900">
                        To fulfill your order, Quirka partners with specialized print-on-demand manufacturing facilities and national logistics couriers.
                    </p>
                    <p class="text-emerald-900 font-medium">
                        <strong>Crucial Notice:</strong> Your shipping details (recipient name, physical address, phone number, and order item configuration) are <strong>securely transmitted to our third-party manufacturing partner (Printlet) and designated courier services</strong> strictly for the sole purpose of producing, packaging, and delivering your custom order.
                    </p>
                    <p class="text-xs text-emerald-800">
                        Our fulfillment partners are contractually bound to process your data strictly for order execution and are prohibited from selling, sharing, or using your contact information for independent marketing.
                    </p>
                </div>

                <hr class="border-gray-100">

                <!-- Section 3: Data Usage & Security -->
                <div class="space-y-3">
                    <h4 class="font-bold text-gray-900 text-base">3. How We Use Your Data</h4>
                    <p>We utilize your personal information exclusively for:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-600 pl-2">
                        <li>Processing, printing, and delivering your custom framed poster orders.</li>
                        <li>Sending order confirmation receipts, tracking updates, and delivery notifications.</li>
                        <li>Providing customer support and processing legitimate replacement requests.</li>
                    </ul>
                </div>

                <hr class="border-gray-100">

                <!-- Section 4: Your Data Rights -->
                <div class="space-y-3">
                    <h4 class="font-bold text-gray-900 text-base">4. Your Data Rights & Contact</h4>
                    <p>
                        You have the right to request access to the personal data we hold about you, or request deletion of your store account information. For privacy inquiries or data requests, please contact our Data Protection team at:
                    </p>
                    <div class="p-4 bg-gray-50 rounded-xl text-gray-900 font-medium flex items-center justify-between">
                        <span>Email: <a href="mailto:privacy@quirka.com" class="text-indigo-600 hover:underline">privacy@quirka.com</a></span>
                        <span class="text-xs text-gray-500">Response within 48 business hours</span>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
