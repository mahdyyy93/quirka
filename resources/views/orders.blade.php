<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-none border border-stone-300 shadow-sm p-6 sm:p-8">

                    @if(auth()->user()?->can('orders.view'))
                    {{-- ───────────────── ADMIN / ARTIST ORDERS VIEW ───────────────── --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <input type="text" id="searchInput" placeholder="Search customer name, email, or order ID..." class="border border-stone-300 rounded-none px-4 py-2.5 text-sm w-full max-w-md focus:ring-1 focus:ring-stone-800 focus:border-stone-800 focus:outline-none">
                        <div class="flex items-center gap-3 shrink-0">
                            <select id="statusFilter" class="border border-stone-300 rounded-none px-4 py-2.5 text-sm font-medium text-stone-700 bg-white focus:ring-1 focus:ring-stone-800 focus:outline-none">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="pending_payment">Pending Payment</option>
                                <option value="pending_verification">Pending Verification</option>
                                <option value="processing">Processing</option>
                                <option value="in_production">In Production</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="completed">Completed</option>
                                <option value="on_hold">On Hold</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-stone-300 rounded-none">
                        <table id="ordersTable" class="w-full text-left border-collapse">
                            <thead class="bg-stone-100 text-stone-700 uppercase text-xs tracking-wider border-b border-stone-300">
                                <tr>
                                    <th class="px-6 py-3.5 font-semibold">#</th>
                                    <th class="px-6 py-3.5 font-semibold">Customer</th>
                                    <th class="px-6 py-3.5 font-semibold">Items</th>
                                    <th class="px-6 py-3.5 font-semibold">Total</th>
                                    <th class="px-6 py-3.5 font-semibold">Payment</th>
                                    <th class="px-6 py-3.5 font-semibold">Status</th>
                                    <th class="px-6 py-3.5 font-semibold">Printlet Status</th>
                                    <th class="px-6 py-3.5 font-semibold">Date</th>
                                    <th class="px-6 py-3.5 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 text-sm text-stone-800 bg-white" id="ordersBody"></tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-4 flex items-center justify-between text-sm text-gray-600"></div>

                    <!-- Order Detail Modal -->
                    <div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold">Order <span id="modalOrderId"></span></h3>
                                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                            </div>

                            <div class="mb-4 space-y-1">
                                <p class="text-sm text-gray-600"><span class="font-medium">Customer:</span> <span id="modalCustomer"></span></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Email:</span> <span id="modalEmail"></span></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Address:</span> <span id="modalAddress"></span></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Payment:</span> <span id="modalPayment"></span></p>
                                <p class="text-sm text-gray-600 hidden" id="modalReferenceContainer"><span class="font-medium">Payment Reference:</span> <span id="modalReference" class="font-mono bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-bold text-xs"></span></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Printlet Order ID:</span> <span id="modalPrintletId" class="font-mono bg-gray-100 px-1 py-0.5 rounded"></span></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Printlet Status:</span> <span id="modalPrintletStatus" class="font-semibold text-indigo-700"></span></p>
                                <p class="text-sm text-gray-600"><span class="font-medium">Notes:</span> <span id="modalNotes"></span></p>
                            </div>

                            <!-- COD Verification Action Banner -->
                            @can('orders.verify-cod')
                            <div id="codVerifyBanner" class="hidden mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center justify-between">
                                <div class="text-xs text-amber-800">
                                    <span class="font-bold block">Awaiting COD Verification</span>
                                    <span>Verify phone confirmation before committing to Printlet.</span>
                                </div>
                                <button onclick="verifyCod()" class="bg-amber-600 hover:bg-amber-700 text-white font-medium px-3 py-1.5 rounded text-xs transition-colors shadow-sm">
                                    ✓ Verify COD Order
                                </button>
                            </div>
                            @endcan

                            <!-- Instapay Verification Action Banner -->
                            @can('orders.verify-instapay')
                            <div id="instapayVerifyBanner" class="hidden mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded-lg flex items-center justify-between">
                                <div class="text-xs text-indigo-900">
                                    <span class="font-bold block">⚡ Awaiting Instapay Verification</span>
                                    <span>Sender / Ref: <strong id="bannerInstapayRef" class="font-mono font-bold"></strong></span>
                                </div>
                                <button onclick="verifyInstapay()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-3 py-1.5 rounded text-xs transition-colors shadow-sm">
                                    ✓ Verify Instapay Payment
                                </button>
                            </div>
                            @endcan

                            <div class="overflow-x-auto border border-stone-300 rounded-none mb-4">
                                <table class="min-w-full divide-y divide-stone-300 text-sm">
                                    <thead class="bg-stone-100 text-stone-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Product</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Qty</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Unit Price</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalItems" class="divide-y divide-stone-200 bg-white"></tbody>
                                </table>
                            </div>

                            <div class="flex justify-between items-center">
                                <p class="font-semibold text-gray-800">Total: <span id="modalTotal"></span></p>
                                <div class="flex items-center gap-2">
                                    <label class="text-sm font-medium text-gray-700">Status:</label>
                                    @can('orders.update-status')
                                        <select id="modalStatus" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                                            <option value="pending">Pending</option>
                                            <option value="pending_payment">Pending Payment</option>
                                            <option value="pending_verification">Pending Verification</option>
                                            <option value="processing">Processing</option>
                                            <option value="in_production">In Production</option>
                                            <option value="shipped">Shipped</option>
                                            <option value="delivered">Delivered</option>
                                            <option value="completed">Completed</option>
                                            <option value="on_hold">On Hold</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        <button onclick="saveStatus()" class="bg-gray-900 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-700">Save</button>
                                    @else
                                        <span id="readOnlyStatusBadge" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800"></span>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        let currentPage = 1;
                        let currentOrder = null;
                        let allOrders = {};

                        const statusColors = {
                            pending:              'bg-yellow-100 text-yellow-800',
                            pending_payment:      'bg-indigo-100 text-indigo-800',
                            pending_verification: 'bg-amber-100 text-amber-800',
                            processing:           'bg-blue-100 text-blue-800',
                            in_production:        'bg-cyan-100 text-cyan-800',
                            shipped:              'bg-purple-100 text-purple-800',
                            delivered:            'bg-green-100 text-green-800',
                            completed:            'bg-green-100 text-green-800',
                            on_hold:              'bg-orange-100 text-orange-800',
                            cancelled:            'bg-red-100 text-red-800',
                        };

                        async function fetchOrders(page = 1) {
                            currentPage = page;
                            const filter = document.getElementById('statusFilter')?.value ?? '';
                            const search = document.getElementById('searchInput')?.value ?? '';
                            let url = `/admin/orders?page=${page}`;
                            if (filter) url += `&status=${encodeURIComponent(filter)}`;
                            if (search) url += `&search=${encodeURIComponent(search)}`;
                            const res = await fetch(url);
                            const data = await res.json();
                            allOrders = {};

                            const tbody = document.getElementById('ordersBody');
                            tbody.innerHTML = '';

                            if (!data.data || data.data.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-8 text-center text-gray-500">No orders found.</td></tr>';
                                return;
                            }

                            data.data.forEach(order => {
                                allOrders[order.id] = order;
                                const customerName = order.customer?.name ?? '—';
                                const itemCount = order.items?.length ?? 0;
                                const color = statusColors[order.status] ?? 'bg-gray-100 text-gray-800';
                                const printletColor = statusColors[order.printlet_status] ?? 'bg-gray-100 text-gray-800';
                                const date = new Date(order.created_at).toLocaleString();

                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${order.id}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${customerName}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${itemCount} item(s)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(order.total_price).toFixed(2)}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 uppercase">${order.payment_method ?? '—'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${color}">
                                            ${order.status}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${printletColor}">
                                            ${order.printlet_status ?? '—'}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${date}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                        <button class="text-indigo-600 hover:text-indigo-900 font-semibold px-2 py-1" onclick="openModal(${order.id})">View</button>
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
                            document.getElementById('modalPrintletId').textContent = order.printlet_order_id ?? '—';
                            document.getElementById('modalPrintletStatus').textContent = order.printlet_status ?? '—';
                            document.getElementById('modalNotes').textContent = order.notes || '—';
                            document.getElementById('modalTotal').textContent = '$' + parseFloat(order.total_price).toFixed(2);
                            if (document.getElementById('modalStatus')) {
                                document.getElementById('modalStatus').value = order.status;
                            }
                            if (document.getElementById('readOnlyStatusBadge')) {
                                document.getElementById('readOnlyStatusBadge').textContent = order.status;
                                document.getElementById('readOnlyStatusBadge').className = 'px-2.5 py-1 rounded-full text-xs font-semibold ' + (statusColors[order.status] ?? 'bg-gray-100 text-gray-800');
                            }

                            // Display payment reference if present
                            const refContainer = document.getElementById('modalReferenceContainer');
                            if (order.payment_reference) {
                                refContainer.classList.remove('hidden');
                                document.getElementById('modalReference').textContent = order.payment_reference;
                            } else {
                                refContainer.classList.add('hidden');
                            }

                            // Toggle COD verification banner
                            const isCod = ['cod', 'cash_on_delivery'].includes(order.payment_method);
                            const needsCodVerify = ['pending_verification', 'pending'].includes(order.printlet_status) || ['pending_verification', 'pending'].includes(order.status);
                            const codBanner = document.getElementById('codVerifyBanner');
                            if (codBanner) {
                                if (isCod && needsCodVerify) {
                                    codBanner.classList.remove('hidden');
                                } else {
                                    codBanner.classList.add('hidden');
                                }
                            }

                            // Toggle Instapay verification banner
                            const isInstapay = order.payment_method === 'instapay';
                            const needsInstapayVerify = order.payment_status !== 'paid';
                            const instapayBanner = document.getElementById('instapayVerifyBanner');
                            if (instapayBanner) {
                                if (isInstapay && needsInstapayVerify) {
                                    instapayBanner.classList.remove('hidden');
                                    document.getElementById('bannerInstapayRef').textContent = order.payment_reference || 'Not provided';
                                } else {
                                    instapayBanner.classList.add('hidden');
                                }
                            }

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

                        async function verifyCod() {
                            if (!currentOrder) return;

                            const reference = prompt('Enter manual verification reference (optional):', 'PHONE-VERIFIED-' + Date.now());
                            if (reference === null) return; // User cancelled

                            const res = await fetch(`/admin/orders/${currentOrder.id}/verify-cod`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ reference }),
                            });

                            const data = await res.json();

                            if (res.ok) {
                                alert('COD Order verified successfully!');
                                closeModal();
                                fetchOrders(currentPage);
                            } else {
                                alert('Verification failed: ' + (data.error || 'Unknown error'));
                            }
                        }

                        async function verifyInstapay() {
                            if (!currentOrder) return;

                            if (!confirm(`Confirm that you have received payment for Order #${currentOrder.id} (Instapay Ref: ${currentOrder.payment_reference || 'N/A'})?`)) {
                                return;
                            }

                            const res = await fetch(`/admin/orders/${currentOrder.id}/verify-instapay`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                            });

                            const data = await res.json();

                            if (res.ok) {
                                alert('Instapay payment verified successfully!');
                                closeModal();
                                fetchOrders(currentPage);
                            } else {
                                alert('Verification failed: ' + (data.error || 'Unknown error'));
                            }
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

                        let searchDebounce;
                        document.getElementById('searchInput')?.addEventListener('input', () => {
                            clearTimeout(searchDebounce);
                            searchDebounce = setTimeout(() => fetchOrders(1), 300);
                        });
                        document.getElementById('statusFilter')?.addEventListener('change', () => fetchOrders(1));

                        fetchOrders();
                    </script>

                    @else
                    {{-- ───────────────── CUSTOMER VIEW ───────────────── --}}
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">My Orders</h3>
                        <select id="customerStatusFilter" class="border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="pending_payment">Pending Payment</option>
                            <option value="pending_verification">Pending Verification</option>
                            <option value="processing">Processing</option>
                            <option value="in_production">In Production</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto border border-stone-300 rounded-none">
                        <table class="min-w-full divide-y divide-stone-300">
                            <thead class="bg-stone-100 text-stone-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody id="myOrdersBody" class="bg-white divide-y divide-stone-200"></tbody>
                        </table>
                    </div>

                    <div id="myOrdersPagination" class="mt-4 flex items-center justify-between text-sm text-gray-600"></div>

                    <script>
                        let myCurrentPage = 1;

                        const myStatusColors = {
                            pending:              'bg-yellow-100 text-yellow-800',
                            pending_payment:      'bg-indigo-100 text-indigo-800',
                            pending_verification: 'bg-amber-100 text-amber-800',
                            processing:           'bg-blue-100 text-blue-800',
                            in_production:        'bg-cyan-100 text-cyan-800',
                            shipped:              'bg-purple-100 text-purple-800',
                            delivered:            'bg-green-100 text-green-800',
                            completed:            'bg-green-100 text-green-800',
                            on_hold:              'bg-orange-100 text-orange-800',
                            cancelled:            'bg-red-100 text-red-800',
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
                                            <a href="/shop" class="mt-3 inline-block text-indigo-600 hover:underline font-semibold">Start Shopping</a>
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
                                        <a href="/my-orders/${order.id}" class="text-indigo-600 hover:text-indigo-900 font-semibold">View</a>
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

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
