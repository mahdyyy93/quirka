<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white px-6 py-4 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    🔒
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">Privacy Policy</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Data Protection & Privacy Terms</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-50 text-emerald-900 font-semibold px-3.5 py-1.5 rounded-full border border-emerald-200 self-start sm:self-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Official Store Policy
            </span>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-6 text-gray-700 text-sm leading-relaxed">
                
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Privacy & Data Protection Policy</h3>
                    <p class="text-gray-600">
                        At Quirka, we are committed to respecting your privacy and securing your personal information when you purchase from our pre-designed framed poster catalog.
                    </p>
                </div>

                <hr class="border-gray-100">

                <!-- Section 1: Information We Collect -->
                <div class="space-y-3">
                    <h4 class="font-bold text-gray-900 text-base">1. Information We Collect</h4>
                    <p>When you browse our catalog or place an order, we collect necessary contact and delivery details:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-600 pl-2">
                        <li><strong>Contact Details:</strong> Full Name, Email Address, Phone Number.</li>
                        <li><strong>Shipping Information:</strong> Physical Street Address, City, Governorate, Postal Code.</li>
                        <li><strong>Order Metadata:</strong> Selected pre-designed posters, frame colors, size dimensions, and payment preferences.</li>
                    </ul>
                </div>

                <hr class="border-gray-100">

                <!-- Section 2: CRITICAL - Third-Party Fulfillment Clause -->
                <div class="space-y-3 bg-emerald-50/60 p-6 border border-emerald-200 rounded-2xl">
                    <h4 class="font-bold text-emerald-950 text-base flex items-center gap-2">
                        <span>🏭</span> 2. Third-Party Manufacturing & Delivery Partner Sharing Clause
                    </h4>
                    <p class="text-emerald-900">
                        To fulfill and deliver your selected pre-designed catalog items, Quirka partners with specialized manufacturing facilities and courier networks.
                    </p>
                    <p class="text-emerald-900 font-medium">
                        <strong>Crucial Notice:</strong> Your shipping and contact details (recipient name, street address, phone number, and order item details) are <strong>securely shared with our third-party manufacturing partner (Printlet) and designated courier services</strong> strictly for the purpose of preparing, packaging, and delivering your order.
                    </p>
                    <p class="text-xs text-emerald-800">
                        Our fulfillment partners process your data strictly to execute delivery and are contractually prohibited from using your contact information for independent marketing.
                    </p>
                </div>

                <hr class="border-gray-100">

                <!-- Section 3: Data Usage & Security -->
                <div class="space-y-3">
                    <h4 class="font-bold text-gray-900 text-base">3. How We Use Your Data</h4>
                    <p>We utilize your personal data strictly for:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-600 pl-2">
                        <li>Processing and delivering your selected catalog artwork orders.</li>
                        <li>Sending order receipts, tracking notifications, and delivery updates.</li>
                        <li>Providing customer support and processing replacement claims.</li>
                    </ul>
                </div>

                <hr class="border-gray-100">

                <!-- Section 4: Your Data Rights -->
                <div class="space-y-3">
                    <h4 class="font-bold text-gray-900 text-base">4. Your Data Rights & Contact</h4>
                    <p>
                        You have the right to request access to or deletion of your store profile data. For privacy inquiries, please reach out to our team at:
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
