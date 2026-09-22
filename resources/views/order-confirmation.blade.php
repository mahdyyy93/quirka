<x-app-layout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Success Header Banner -->
            <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm text-center space-y-3">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl font-bold shadow-sm">
                    ✓
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Order #{{ $order->id }}</h1>
                <p class="text-gray-500 text-sm max-w-md mx-auto">
                    Thank you for your order! We've received your custom framed poster request and sent a confirmation email to <strong class="text-gray-800">{{ $order->customer?->email }}</strong>.
                </p>

                <div class="pt-2 flex flex-wrap items-center justify-center gap-3 text-xs">
                    <span class="bg-gray-100 text-gray-700 font-medium px-3 py-1 rounded-full">
                        Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}
                    </span>

                    @php
                        $statusColors = [
                            'pending'              => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'pending_verification' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'processing'           => 'bg-blue-100 text-blue-800 border-blue-200',
                            'in_production'        => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            'shipped'              => 'bg-purple-100 text-purple-800 border-purple-200',
                            'delivered'            => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'cancelled'            => 'bg-red-100 text-red-800 border-red-200',
                        ];
                        $badgeClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                    @endphp

                    <span class="font-bold px-3 py-1 rounded-full border {{ $badgeClass }} capitalize">
                        Status: {{ str_replace('_', ' ', $order->status) }}
                    </span>
                </div>
            </div>

            @if($order->payment_method === 'instapay')
                <!-- Instapay Transfer & Reference Card -->
                <div x-data="{
                    copied: false,
                    editingRef: {{ $order->payment_reference ? 'false' : 'true' }},
                    copyHandle(text) {
                        navigator.clipboard.writeText(text);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2500);
                    }
                }" class="bg-gradient-to-br from-indigo-50/90 via-white to-purple-50/50 rounded-2xl border-2 border-indigo-200/90 shadow-md p-6 sm:p-8 space-y-6">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-indigo-100">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold mb-2">
                                <span>⚡</span> Instapay Payment Instructions
                            </div>
                            <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                                Complete Your Transfer for Order #{{ $order->id }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">
                                Your order has been placed and secured! Please transfer the exact amount below to proceed with printing and framing.
                            </p>
                        </div>
                        <div class="sm:text-right bg-white px-5 py-3 rounded-xl border border-indigo-100 shadow-xs shrink-0">
                            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider block">Total to Transfer</span>
                            <span class="text-3xl font-black text-gray-900 block">${{ number_format($order->total_price, 2) }}</span>
                        </div>
                    </div>

                    {{-- IPA Handle & Channels (Mobile Link & Desktop QR) --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-stretch">
                        
                        {{-- 1. Instapay Address (IPA) --}}
                        <div class="bg-white rounded-xl p-5 border border-indigo-100/90 shadow-2xs flex flex-col justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-1">1. Instapay Address (IPA)</span>
                                <div class="font-mono font-extrabold text-gray-900 text-base break-all select-all mt-1">
                                    {{ $instapayConfig['handle'] ?? config('services.instapay.handle', 'quirka@instapay') }}
                                </div>
                                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                                    Account: <strong>{{ $instapayConfig['account_name'] ?? config('services.instapay.account_name', 'Quirka Art Prints') }}</strong>
                                </p>
                            </div>
                            <button type="button" @click="copyHandle('{{ $instapayConfig['handle'] ?? config('services.instapay.handle', 'quirka@instapay') }}')"
                                class="mt-4 inline-flex items-center justify-center gap-2 w-full py-2 px-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg text-xs transition-colors cursor-pointer">
                                <span x-show="!copied">📋 Copy Instapay Address</span>
                                <span x-show="copied" class="text-emerald-600 font-extrabold">✓ Copied to Clipboard!</span>
                            </button>
                        </div>

                        {{-- 2. For Mobile Users (Payment Link) --}}
                        <div class="bg-white rounded-xl p-5 border border-indigo-100/90 shadow-2xs flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-base">📱</span>
                                    <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wide">2. For Mobile Users</h4>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed mb-4">
                                    Tap below to launch your Instapay transfer link directly on this phone.
                                </p>
                            </div>
                            <a href="{{ $instapayConfig['payment_url'] ?? config('services.instapay.payment_url', 'https://ipn.eg/') }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 w-full bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-xs font-bold py-2.5 px-3 rounded-lg shadow-xs transition-all">
                                <span>Open Instapay App Link</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>

                        {{-- 3. For Desktop Users (QR Code) --}}
                        <div class="bg-white rounded-xl p-5 border border-indigo-100/90 shadow-2xs flex items-center gap-3.5">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($instapayConfig['payment_url'] ?? config('services.instapay.payment_url', 'https://ipn.eg/')) }}"
                                alt="Instapay QR Code"
                                class="w-20 h-20 rounded-lg border border-gray-200 bg-white p-1 shrink-0 shadow-xs">
                            <div>
                                <div class="flex items-center gap-1 mb-1">
                                    <span class="text-base">💻</span>
                                    <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wide">3. Desktop Users</h4>
                                </div>
                                <p class="text-xs text-gray-500 leading-snug">
                                    Scan this QR code with your mobile camera or Instapay app scanner to transfer.
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- Step 2: Submit or View Instapay Reference --}}
                    <div class="bg-white rounded-xl p-5 border border-indigo-200 shadow-xs">
                        @if($order->payment_reference)
                            <div x-show="!editingRef" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                        ✓ Reference Number Received
                                    </span>
                                    <p class="text-sm font-semibold text-gray-900 pt-1">
                                        Sender / Ref: <code class="bg-gray-100 text-indigo-700 px-2 py-0.5 rounded font-mono font-bold">{{ $order->payment_reference }}</code>
                                    </p>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        Our team is cross-checking this reference with our Instapay account. Once confirmed, your order status will automatically change to <strong>Processing</strong>.
                                    </p>
                                </div>
                                <button type="button" @click="editingRef = true" class="text-xs text-gray-600 hover:text-gray-900 underline font-semibold shrink-0 cursor-pointer">
                                    Edit Reference
                                </button>
                            </div>
                        @endif

                        <div x-show="editingRef" class="space-y-3">
                            <form method="POST" action="{{ route('order.instapay-reference', $order) }}" class="space-y-3">
                                @csrf
                                <label for="payment_reference_input" class="block text-sm font-bold text-gray-900">
                                    Submit Your Instapay Transaction Reference # or Sender Name <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs text-gray-500">
                                    After sending the money, paste the <strong>transaction reference number</strong> or the <strong>account holder name</strong> from your Instapay receipt below.
                                </p>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <input type="text" id="payment_reference_input" name="payment_reference" required
                                        value="{{ old('payment_reference', $order->payment_reference) }}"
                                        placeholder="e.g. Ref #1294821 or Mohamed Ahmed"
                                        class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 @error('payment_reference') border-red-400 @enderror">
                                    <button type="submit"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow-xs transition-colors shrink-0 cursor-pointer">
                                        I Have Paid — Submit Reference
                                    </button>
                                </div>
                                @error('payment_reference') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                @if($order->payment_reference)
                                    <button type="button" @click="editingRef = false" class="text-xs text-gray-500 hover:text-gray-700 underline mt-1 cursor-pointer">Cancel</button>
                                @endif
                            </form>
                        </div>
                    </div>

                </div>
            @endif

            <!-- Order Status Lifecycle Progress Bar -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-900 text-base">Fulfillment Progress</h3>
                
                @php
                    $steps = [
                        'pending'       => ['label' => 'Order Placed', 'icon' => '📝'],
                        'in_production' => ['label' => 'Printing & Framing', 'icon' => '🖼️'],
                        'shipped'       => ['label' => 'In Transit', 'icon' => '🚚'],
                        'delivered'     => ['label' => 'Delivered', 'icon' => '🎉'],
                    ];
                    
                    $currentStepIndex = match($order->status) {
                        'pending', 'pending_verification' => 0,
                        'processing', 'in_production' => 1,
                        'shipped' => 2,
                        'delivered', 'completed' => 3,
                        default => 0,
                    };
                @endphp

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-2">
                    @foreach(array_values($steps) as $idx => $step)
                        @php
                            $isComplete = $idx <= $currentStepIndex;
                            $isCurrent = $idx === $currentStepIndex;
                        @endphp
                        <div class="p-4 rounded-xl border text-center transition-all {{ $isComplete ? 'bg-indigo-50/60 border-indigo-200 text-indigo-950' : 'bg-gray-50 border-gray-100 text-gray-400' }}">
                            <div class="text-2xl mb-1">{{ $step['icon'] }}</div>
                            <div class="text-xs font-bold {{ $isCurrent ? 'text-indigo-600 underline' : '' }}">{{ $step['label'] }}</div>
                            @if($isComplete)
                                <span class="text-[10px] text-indigo-700 font-semibold mt-1 block">✓ Complete</span>
                            @else
                                <span class="text-[10px] text-gray-400 mt-1 block">Pending</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Delivery & Payment Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Shipping Address -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-2 text-sm">
                    <h4 class="font-bold text-gray-900 text-base mb-3 flex items-center gap-2">
                        <span>📍</span> Shipping Address
                    </h4>
                    <p class="font-semibold text-gray-800">{{ $order->customer?->name }}</p>
                    <p class="text-gray-600 leading-relaxed">{{ $order->customer?->address }}</p>
                    <p class="text-gray-500 pt-1">Phone: <strong class="text-gray-700">{{ $order->customer?->phone ?? 'N/A' }}</strong></p>
                </div>

                <!-- Payment Details -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-2 text-sm">
                    <h4 class="font-bold text-gray-900 text-base mb-3 flex items-center gap-2">
                        <span>💳</span> Payment Information
                    </h4>
                    <div class="space-y-1.5 text-gray-600">
                        <p><span class="font-medium text-gray-900">Method:</span> <span class="capitalize">{{ str_replace('_', ' ', $order->payment_method ?? 'Cash on Delivery') }}</span></p>
                        <p><span class="font-medium text-gray-900">Payment Status:</span> <span class="capitalize font-semibold text-indigo-600">{{ $order->payment_status ?? 'Unpaid' }}</span></p>
                        @if($order->payment_reference)
                            <p><span class="font-medium text-gray-900">Instapay Reference:</span> <code class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-xs font-mono font-bold">{{ $order->payment_reference }}</code></p>
                        @endif
                        @if($order->printlet_order_id)
                            <p><span class="font-medium text-gray-900">Printlet Order Ref:</span> <code class="bg-gray-100 px-2 py-0.5 rounded text-xs text-gray-800 font-mono">{{ $order->printlet_order_id }}</code></p>
                        @endif
                        @if($order->payment_method === 'instapay' && $order->payment_status !== 'paid')
                            <div class="mt-3 p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-xs text-indigo-900">
                                <strong>⚡ Awaiting Instapay Verification:</strong> Our team is cross-checking your transaction reference with our account. Once verified, your order will automatically enter production.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Items Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6 space-y-4">
                <h3 class="font-bold text-gray-900 text-base">Ordered Items</h3>

                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        @php
                            $product = $item->product;
                            $firstImage = $product?->images->first();
                            $lineTotal = $item->quantity * $item->unit_price;
                        @endphp
                        <div class="py-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                @if($firstImage)
                                    <img src="{{ Storage::url($firstImage->path) }}" alt="{{ $product->title }}" class="w-16 h-16 object-cover rounded-xl border border-gray-100 shrink-0">
                                @else
                                    <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 shrink-0">
                                        🖼️
                                    </div>
                                @endif

                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm leading-snug">{{ $product?->title ?? 'Framed Art Print' }}</h4>
                                    <p class="text-xs text-gray-400 mt-0.5">Quantity: {{ $item->quantity }} &times; ${{ number_format($item->unit_price, 2) }}</p>
                                </div>
                            </div>

                            <div class="font-bold text-gray-900 text-sm">
                                ${{ number_format($lineTotal, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <hr class="border-gray-100">

                <!-- Totals Breakdown -->
                <div class="space-y-2 text-sm text-gray-600 max-w-xs ml-auto pt-2">
                    <div class="flex justify-between">
                        <span>Items Subtotal:</span>
                        <span class="font-semibold text-gray-900">${{ number_format($order->total_price - ($order->shipping_amount ?? 0), 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shipping & Delivery:</span>
                        <span class="font-semibold text-gray-900">${{ number_format($order->shipping_amount ?? 0, 2) }}</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-1">
                        <span>Total Paid/Due:</span>
                        <span class="text-xl text-gray-900">${{ number_format($order->total_price, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Action Links -->
            <div class="flex flex-wrap items-center justify-between gap-4 pt-4">
                <a href="{{ route('my-orders.index') }}" class="text-indigo-600 font-semibold text-sm hover:underline">
                    &larr; Return to My Orders
                </a>
                <a href="{{ route('shop.index') }}" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-all">
                    Continue Shopping
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
