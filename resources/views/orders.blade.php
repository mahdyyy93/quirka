<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if(auth()->user()?->role === 'admin')
                {{ __('Orders Management') }}
            @else
                {{ __('My Orders') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    @if(auth()->user()?->role === 'admin')
                    {{-- ───────────────── ADMIN VIEW ───────────────── --}}
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">All Orders</h3>
                        <div class="flex items-center gap-3">
                            <select id="statusFilter" class="border border-gray-300 rounded px-3 py-2 text-sm">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <table id="ordersTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="ordersBody"></tbody>
                    </table>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-4 flex items-center justify-between text-sm text-gray-600"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Order <span id="modalOrderId"></span></h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600"><span class="font-medium">Customer:</span> <span id="modalCustomer"></span></p>
                <p class="text-sm text-gray-600 mt-1"><span class="font-medium">Email:</span> <span id="modalEmail"></span></p>
                <p class="text-sm text-gray-600 mt-1"><span class="font-medium">Address:</span> <span id="modalAddress"></span></p>
                <p class="text-sm text-gray-600 mt-1"><span class="font-medium">Payment:</span> <span id="modalPayment"></span></p>
                <p class="text-sm text-gray-600 mt-1"><span class="font-medium">Notes:</span> <span id="modalNotes"></span></p>
            </div>

            <table class="min-w-full divide-y divide-gray-200 mb-4 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="modalItems" class="divide-y divide-gray-200"></tbody>
            </table>

            <div class="flex justify-between items-center">
                <p class="font-semibold text-gray-800">Total: <span id="modalTotal"></span></p>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Status:</label>
                    <select id="modalStatus" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button onclick="saveStatus()" class="bg-gray-900 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-700">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentOrder = null;
        let allOrders = [];

        const statusColors = {
            pending:    'bg-yellow-100 text-yellow-800',
            processing: 'bg-blue-100 text-blue-800',
            shipped:    'bg-indigo-100 text-indigo-800',
            delivered:  'bg-green-100 text-green-800',
            cancelled:  'bg-red-100 text-red-800',
        };

        async function fetchOrders(page = 1) {
            currentPage = page;
            const filter = document.getElementById('statusFilter').value;
            const url = `/admin/orders?page=${page}${filter ? '&status=' + filter : ''}`;
            const res = await fetch(url);
            const data = await res.json();
            allOrders = {};

            const tbody = document.getElementById('ordersBody');
            tbody.innerHTML = '';

            data.data.forEach(order => {
                allOrders[order.id] = order;
                const customerName = order.customer?.name ?? '—';
                const itemCount = order.items?.length ?? 0;
                const color = statusColors[order.status] ?? 'bg-gray-100 text-gray-800';
                const date = new Date(order.created_at).toLocaleString();

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${order.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${customerName}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${itemCount} item(s)</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(order.total_price).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.payment_method ?? '—'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${color}">
                            ${order.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-indigo-600 hover:text-indigo-900" onclick="openModal(${order.id})">View</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            renderPagination(data);
        }

        function renderPagination(data) {
            const el = document.getElementById('pagination');
            const from = data.from ?? 0;
            const to = data.to ?? 0;
            const total = data.total ?? 0;

            let buttons = `<span>Showing ${from}–${to} of ${total} orders</span><div class="flex gap-1">`;

            for (let p = 1; p <= data.last_page; p++) {
                const active = p === data.current_page
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200';
                buttons += `<button class="px-3 py-1 rounded text-sm ${active}" onclick="fetchOrders(${p})">${p}</button>`;
            }

            buttons += '</div>';
            el.innerHTML = buttons;
        }

        function openModal(id) {
            const order = allOrders[id];
            if (!order) return;
            currentOrder = order;

            document.getElementById('modalOrderId').textContent = '#' + order.id;
            document.getElementById('modalCustomer').textContent = order.customer?.name ?? '—';
            document.getElementById('modalEmail').textContent = order.customer?.email ?? '—';
            document.getElementById('modalAddress').textContent = order.customer?.address ?? '—';
            document.getElementById('modalPayment').textContent = (order.payment_method ?? '—') + ' / ' + (order.payment_status ?? '—');
            document.getElementById('modalNotes').textContent = order.notes || '—';
            document.getElementById('modalTotal').textContent = '$' + parseFloat(order.total_price).toFixed(2);
            document.getElementById('modalStatus').value = order.status;

            const tbody = document.getElementById('modalItems');
            tbody.innerHTML = '';
            (order.items ?? []).forEach(item => {
                const name = item.product?.title ?? 'Unknown product';
                const subtotal = (item.quantity * item.unit_price).toFixed(2);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-3 py-2 text-gray-900">${name}</td>
                    <td class="px-3 py-2 text-gray-600">${item.quantity}</td>
                    <td class="px-3 py-2 text-gray-600">$${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td class="px-3 py-2 text-gray-900">$${subtotal}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('orderModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('orderModal').classList.add('hidden');
            currentOrder = null;
        }

        async function saveStatus() {
            if (!currentOrder) return;
            const status = document.getElementById('modalStatus').value;

            const res = await fetch(`/admin/orders/${currentOrder.id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status }),
            });

            if (res.ok) {
                closeModal();
                fetchOrders(currentPage);
            } else {
                alert('Failed to update status.');
            }
        }

        document.getElementById('statusFilter').addEventListener('change', () => fetchOrders(1));

        fetchOrders();
    </script>

    @else
    {{-- ───────────────── CUSTOMER VIEW ───────────────── --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">My Orders</h3>
                        <select id="customerStatusFilter" class="border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody id="myOrdersBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>

                    <div id="myOrdersPagination" class="mt-4 flex items-center justify-between text-sm text-gray-600"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let myCurrentPage = 1;

        const myStatusColors = {
            pending:    'bg-yellow-100 text-yellow-800',
            processing: 'bg-blue-100 text-blue-800',
            shipped:    'bg-indigo-100 text-indigo-800',
            delivered:  'bg-green-100 text-green-800',
            cancelled:  'bg-red-100 text-red-800',
        };

        async function fetchMyOrders(page = 1) {
            myCurrentPage = page;
            const filter = document.getElementById('customerStatusFilter').value;
            const url = `/my-orders/data?page=${page}${filter ? '&status=' + filter : ''}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            const tbody = document.getElementById('myOrdersBody');
            tbody.innerHTML = '';

            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                            <div class="text-4xl mb-3">📦</div>
                            <p>You haven't placed any orders yet.</p>
                            <a href="/shop" class="mt-3 inline-block text-indigo-600 hover:underline">Start Shopping</a>
                        </td>
                    </tr>`;
                return;
            }

            data.data.forEach(order => {
                const itemCount = order.items?.length ?? 0;
                const color = myStatusColors[order.status] ?? 'bg-gray-100 text-gray-800';
                const date = new Date(order.created_at).toLocaleDateString();
                const payment = (order.payment_method ?? '—').replace(/_/g, ' ');

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${order.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${itemCount} item(s)</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(order.total_price).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">${payment}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${color}">${order.status}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="/my-orders/${order.id}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            renderMyPagination(data);
        }

        function renderMyPagination(data) {
            const el = document.getElementById('myOrdersPagination');
            const from = data.from ?? 0, to = data.to ?? 0, total = data.total ?? 0;
            let buttons = `<span>Showing ${from}–${to} of ${total} orders</span><div class="flex gap-1">`;
            for (let p = 1; p <= data.last_page; p++) {
                const active = p === data.current_page ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200';
                buttons += `<button class="px-3 py-1 rounded text-sm ${active}" onclick="fetchMyOrders(${p})">${p}</button>`;
            }
            buttons += '</div>';
            el.innerHTML = buttons;
        }

        document.getElementById('customerStatusFilter').addEventListener('change', () => fetchMyOrders(1));
        fetchMyOrders();
    </script>
    @endif

</x-app-layout>
