<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customers') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">All Customers</h3>
                        <input type="text" id="searchInput" placeholder="Search name or email..."
                            class="border border-gray-300 rounded px-3 py-2 text-sm w-64">
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Since</th>
                            </tr>
                        </thead>
                        <tbody id="customersBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>

                    <p id="emptyState" class="hidden text-center text-gray-400 py-8">No customers found.</p>
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
