<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <input type="text" id="searchInput" oninput="filterSuppliers()" placeholder="Search supplier by name or email..." class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm w-full max-w-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                    <button onclick="openModal()" class="bg-gray-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-800 transition-all shrink-0">
                        + Add Supplier
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="suppliersTable" class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3.5 font-semibold">Name</th>
                                <th class="px-6 py-3.5 font-semibold">Email</th>
                                <th class="px-6 py-3.5 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="supplierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96 shadow-xl">
            <h3 id="modalTitle" class="text-lg font-bold mb-4">Add Supplier</h3>

            <form id="supplierForm" onsubmit="saveSupplier(event)">
                <input type="hidden" id="supplierId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="supplierName" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="supplierEmail" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let allSuppliers = [];

        async function fetchSuppliers() {
            const res = await fetch('/admin/suppliers');
            allSuppliers = await res.json();
            renderSuppliers(allSuppliers);
        }

        function renderSuppliers(suppliers) {
            const tbody = document.querySelector('#suppliersTable tbody');
            tbody.innerHTML = '';

            if (suppliers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">No suppliers found.</td></tr>';
                return;
            }

            suppliers.forEach(supplier => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${supplier.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${supplier.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium gap-2 flex justify-end">
                        <button class="text-indigo-600 hover:text-indigo-900 px-2 py-1" onclick="editSupplier(${supplier.id}, '${supplier.name.replace(/'/g, "\\'")}', '${supplier.email}')">Edit</button>
                        <button class="text-red-600 hover:text-red-900 px-2 py-1" onclick="deleteSupplier(${supplier.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function filterSuppliers() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const filtered = allSuppliers.filter(s => 
                s.name.toLowerCase().includes(query) || s.email.toLowerCase().includes(query)
            );
            renderSuppliers(filtered);
        }

        function openModal() {
            document.getElementById('supplierModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('supplierModal').classList.add('hidden');
            document.getElementById('supplierForm').reset();
            document.getElementById('supplierId').value = '';
            document.getElementById('modalTitle').innerText = 'Add Supplier';
        }

        function editSupplier(id, name, email) {
            document.getElementById('supplierId').value = id;
            document.getElementById('supplierName').value = name;
            document.getElementById('supplierEmail').value = email;
            document.getElementById('modalTitle').innerText = 'Edit Supplier';
            openModal();
        }

        async function saveSupplier(e) {
            e.preventDefault();
            const id = document.getElementById('supplierId').value;
            const name = document.getElementById('supplierName').value;
            const email = document.getElementById('supplierEmail').value;

            const url = id ? `/admin/suppliers/${id}` : '/admin/suppliers';
            const method = id ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, email })
            });

            if (res.ok) {
                closeModal();
                fetchSuppliers();
            } else {
                alert('Error saving supplier');
            }
        }

        async function deleteSupplier(id) {
            if (!confirm('Are you sure you want to delete this supplier?')) return;

            const res = await fetch(`/admin/suppliers/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            if (res.ok) {
                fetchSuppliers();
            } else {
                alert('Error deleting supplier');
            }
        }

        fetchSuppliers();
    </script>
</x-app-layout>
