<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Orders</h2>
            <a href="{{ route('shop.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">← Continue Shopping</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Status filter --}}
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500" id="orderCount"></p>
                <select id="statusFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            {{-- Orders list --}}
            <div id="ordersList" class="space-y-4"></div>

            {{-- Pagination --}}
            <div id="pagination" class="flex items-center justify-between text-sm text-gray-500 pt-2"></div>

        </div>
    </div>

    <script>
        const STATUS_COLORS = {
            pending:    { bg: 'bg-yellow-100', text: 'text-yellow-800' },
            processing: { bg: 'bg-blue-100',   text: 'text-blue-800'   },
            shipped:    { bg: 'bg-indigo-100',  text: 'text-indigo-800' },
            delivered:  { bg: 'bg-green-100',   text: 'text-green-800'  },
            cancelled:  { bg: 'bg-red-100',     text: 'text-red-800'    },
        };

        const STATUS_ICONS = {
            pending:    '🕐',
            processing: '⚙️',
            shipped:    '🚚',
            delivered:  '✅',
            cancelled:  '✖️',
        };

        let currentPage = 1;

        function statusBadge(status) {
            const c = STATUS_COLORS[status] ?? { bg: 'bg-gray-100', text: 'text-gray-700' };
            const icon = STATUS_ICONS[status] ?? '•';
            return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold ${c.bg} ${c.text}">
                        ${icon} ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>`;
        }

        function formatDate(iso) {
            return new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function formatPayment(method) {
            return method ? method.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '—';
        }

        function renderEmpty() {
            return `<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                        <div class="text-6xl mb-4">📦</div>
                        <p class="text-gray-500 text-lg font-medium mb-1">No orders found</p>
                        <p class="text-gray-400 text-sm mb-6">Looks like you haven't placed any orders yet.</p>
                        <a href="${'{{ route("shop.index") }}'}" class="inline-block bg-gray-900 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-gray-700 transition-colors">
                            Start Shopping
                        </a>
                    </div>`;
        }

        function renderOrderCard(order) {
            const itemSummary = (order.items ?? [])
                .map(i => `${i.product?.title ?? 'Item'} ×${i.quantity}`)
                .join(', ');

            return `<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="px-6 py-4 border-b border-gray-50 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-semibold text-gray-800">#${order.id}</span>
                                ${statusBadge(order.status)}
                            </div>
                            <span class="text-sm text-gray-400">${formatDate(order.created_at)}</span>
                        </div>

                        <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm text-gray-600 truncate max-w-xs" title="${itemSummary}">
                                    ${itemSummary || 'No items'}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">${formatPayment(order.payment_method)}</p>
                            </div>

                            <div class="flex items-center gap-6 shrink-0">
                                <span class="text-lg font-bold text-gray-900">$${parseFloat(order.total_price).toFixed(2)}</span>
                                <a href="/my-orders/${order.id}"
                                   class="text-sm font-medium text-gray-700 border border-gray-200 px-4 py-1.5 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                    View Details →
                                </a>
                            </div>
                        </div>
                    </div>`;
        }

        function renderPagination(data) {
            const el = document.getElementById('pagination');
            if (data.last_page <= 1) { el.innerHTML = ''; return; }

            const from = data.from ?? 0, to = data.to ?? 0, total = data.total ?? 0;
            let html = `<span>Showing ${from}–${to} of ${total} orders</span><div class="flex gap-1">`;

            for (let p = 1; p <= data.last_page; p++) {
                const active = p === data.current_page
                    ? 'bg-gray-900 text-white'
                    : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50';
                html += `<button class="px-3 py-1.5 rounded-lg text-sm font-medium ${active}" onclick="fetchOrders(${p})">${p}</button>`;
            }

            html += '</div>';
            el.innerHTML = html;
        }

        async function fetchOrders(page = 1) {
            currentPage = page;
            const status = document.getElementById('statusFilter').value;
            const url = `/my-orders/data?page=${page}${status ? '&status=' + status : ''}`;

            const list = document.getElementById('ordersList');
            list.innerHTML = '<p class="text-center text-gray-400 py-12">Loading…</p>';

            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Request failed');
                const data = await res.json();

                const count = data.total ?? 0;
                document.getElementById('orderCount').textContent = count === 0 ? '' : `${count} order${count !== 1 ? 's' : ''}`;

                if (!data.data || data.data.length === 0) {
                    list.innerHTML = renderEmpty();
                    document.getElementById('pagination').innerHTML = '';
                    return;
                }

                list.innerHTML = data.data.map(renderOrderCard).join('');
                renderPagination(data);
            } catch (e) {
                list.innerHTML = `<div class="bg-red-50 border border-red-100 rounded-2xl p-8 text-center text-red-600">
                                      Failed to load orders. Please refresh the page.
                                  </div>`;
            }
        }

        document.getElementById('statusFilter').addEventListener('change', () => fetchOrders(1));

        fetchOrders();
    </script>
</x-app-layout>
