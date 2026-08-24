<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suppliers Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-bold">Suppliers List</h3>
                        <button onclick="openModal()" class="bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                            + Add Supplier
                        </button>
                    </div>

                    <table id="suppliersTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="supplierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-96 shadow-xl">
            <h3 id="modalTitle" class="text-lg font-bold mb-4">Add Supplier</h3>

            <form id="supplierForm" onsubmit="saveSupplier(event)">
                <input type="hidden" id="supplierId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="supplierName" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="supplierEmail" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        async function fetchSuppliers() {
            const res = await fetch('/admin/suppliers');
            const data = await res.json();
            const tbody = document.querySelector('#suppliersTable tbody');
            tbody.innerHTML = '';

            data.forEach(supplier => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${supplier.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${supplier.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium gap-2 flex">
                        <button class="text-indigo-600 hover:text-indigo-900" onclick="editSupplier(${supplier.id}, '${supplier.name.replace(/'/g, "\\'")}', '${supplier.email}')">Edit</button>
                        <button class="text-red-600 hover:text-red-900" onclick="deleteSupplier(${supplier.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
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

            const method = id ? 'PUT' : 'POST';
            const url = id ? `/admin/suppliers/${id}` : '/admin/suppliers';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name, email })
            });

            if (res.ok) {
                closeModal();
                fetchSuppliers();
            } else {
                const data = await res.json();
                alert(data.message || 'An error occurred');
            }
        }

        async function deleteSupplier(id) {
            if (!confirm('Are you sure you want to delete this supplier?')) return;

            const res = await fetch(`/admin/suppliers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                fetchSuppliers();
            } else {
                alert('Failed to delete supplier.');
            }
        }

        fetchSuppliers();
    </script>
</x-app-layout>
