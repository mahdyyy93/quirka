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
                        @if($order->printlet_order_id)
                            <p><span class="font-medium text-gray-900">Printlet Order Ref:</span> <code class="bg-gray-100 px-2 py-0.5 rounded text-xs text-gray-800 font-mono">{{ $order->printlet_order_id }}</code></p>
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
