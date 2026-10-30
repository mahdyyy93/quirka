<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-none border border-stone-300 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <input type="text" id="searchInput" placeholder="Search customer by name or email..." class="border border-stone-300 rounded-none px-4 py-2.5 text-sm w-full max-w-md focus:ring-1 focus:ring-stone-800 focus:border-stone-800 focus:outline-none">
                </div>

                <div class="overflow-x-auto border border-stone-300 rounded-none">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-stone-100 text-stone-700 uppercase text-xs tracking-wider border-b border-stone-300">
                            <tr>
                                <th class="px-6 py-3.5 font-semibold">Name</th>
                                <th class="px-6 py-3.5 font-semibold">Email</th>
                                <th class="px-6 py-3.5 font-semibold">Address</th>
                                <th class="px-6 py-3.5 font-semibold">Orders</th>
                                <th class="px-6 py-3.5 font-semibold">Total Spent</th>
                                <th class="px-6 py-3.5 font-semibold">Member Since</th>
                            </tr>
                        </thead>
                        <tbody id="customersBody" class="divide-y divide-stone-200 text-sm text-stone-800 bg-white"></tbody>
                    </table>

                    <p id="emptyState" class="hidden text-center text-stone-400 py-8 text-sm">No customers found.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allCustomers = [];

        async function fetchCustomers() {
            const res = await fetch('/admin/customers');
            allCustomers = await res.json();
            renderTable(allCustomers);
        }

        function renderTable(customers) {
            const tbody = document.getElementById('customersBody');
            const empty = document.getElementById('emptyState');
            tbody.innerHTML = '';

            if (customers.length === 0) {
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');

            customers.forEach(customer => {
                const total = parseFloat(customer.orders_sum_total_price ?? 0).toFixed(2);
                const date = new Date(customer.created_at).toLocaleDateString();
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${customer.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${customer.email}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">${customer.address ?? '—'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${customer.orders_count}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$${total}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">${date}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.getElementById('searchInput').addEventListener('input', e => {
            const q = e.target.value.toLowerCase();
            renderTable(allCustomers.filter(c =>
                c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q)
            ));
        });

        fetchCustomers();
    </script>
</x-app-layout>
