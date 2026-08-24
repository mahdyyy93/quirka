<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    <div class="flex justify-between items-center mb-4">
                        <input type="text" id="searchInput" placeholder="Search SKU or Title..." class="border border-gray-300 rounded p-2 w-full max-w-md">
                        <button onclick="openModal()" class="bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                            + Add Product
                        </button>
                    </div>

                    <table id="productsTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thumbnail</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
            <h3 id="modalTitle" class="text-lg font-bold mb-4">Add Product</h3>

            <form id="productForm" onsubmit="saveProduct(event)">
                <div id="global-error" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm whitespace-pre-line"></div>
                <input type="hidden" id="productId">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="productTitle" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    <p class="text-red-500 text-xs mt-1 hidden error-msg" id="error-title"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">SKU</label>
                    <input type="text" id="productSku" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    <p class="text-red-500 text-xs mt-1 hidden error-msg" id="error-sku"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number" id="productPrice" step="0.01" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    <p class="text-red-500 text-xs mt-1 hidden error-msg" id="error-price"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Supplier</label>
                    <select id="productSupplier" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">Select Supplier...</option>
                    </select>
                    <p class="text-red-500 text-xs mt-1 hidden error-msg" id="error-supplier_id"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Customizations (JSON)</label>
                    <textarea id="productCustomizations" rows="3" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 font-mono text-sm" placeholder='{"color": "red", "size": "L"}'></textarea>
                    <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500">Optional. Must be valid JSON.</p>
                        <p class="text-red-500 text-xs hidden error-msg" id="error-customizations"></p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Images</label>
                    <input type="file" id="productImages" multiple accept="image/*" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500">Select multiple images to upload. Existing images are kept.</p>
                        <p class="text-red-500 text-xs hidden error-msg" id="error-images"></p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global map to store product data for editing
        let currentProducts = {};
        let suppliers = [];

        async function fetchSuppliers() {
            const res = await fetch('/admin/suppliers');
            suppliers = await res.json();
            const select = document.getElementById('productSupplier');

            // Keep the first default option
            select.innerHTML = '<option value="">Select Supplier...</option>';
            suppliers.forEach(sup => {
                const opt = document.createElement('option');
                opt.value = sup.id;
                opt.textContent = sup.name;
                select.appendChild(opt);
            });
        }

        async function fetchProducts(search = '') {
            const res = await fetch(`/admin/products?search=${search}`);
            const data = await res.json();
            const tbody = document.querySelector('#productsTable tbody');
            tbody.innerHTML = '';
            currentProducts = {};

            data.data.forEach(product => {
                currentProducts[product.id] = product;

                const tr = document.createElement('tr');
                const firstImage = product.images && product.images.length ? product.images[0] : null;
                const thumbnail = firstImage
                    ? `<span class="relative inline-block group"><img src="/storage/${firstImage.path}" alt="${product.title}" class="h-12 w-12 rounded object-cover cursor-zoom-in"><img src="/storage/${firstImage.path}" alt="${product.title}" class="pointer-events-none invisible absolute left-0 top-0 z-50 h-auto w-auto max-h-64 max-w-64 rounded-lg object-contain opacity-0 shadow-2xl transition-opacity duration-200 group-hover:visible group-hover:opacity-100"></span>`
                    : '<span class="text-gray-400">No image</span>';
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${thumbnail}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.sku}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.title}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${product.price}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.images ? product.images.length : 0} img(s)</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.supplier ? product.supplier.name : 'None'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${product.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${product.is_available ? 'Active' : 'Hidden'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                        <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded hover:bg-gray-300 transition-colors" onclick="toggle(${product.id})">Toggle</button>
                        <button class="text-indigo-600 hover:text-indigo-900 px-2 py-1" onclick="editProduct(${product.id})">Edit</button>
                        <button class="text-red-600 hover:text-red-900 px-2 py-1" onclick="deleteProduct(${product.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function openModal() {
            clearErrors();
            document.getElementById('productModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('modalTitle').innerText = 'Add Product';
            clearErrors();
        }

        function clearErrors() {
            document.querySelectorAll('.error-msg').forEach(el => {
                el.classList.add('hidden');
                el.innerText = '';
            });
            const globalError = document.getElementById('global-error');
            globalError.classList.add('hidden');
            globalError.innerText = '';
        }

        function editProduct(id) {
            const product = currentProducts[id];
            if (!product) return;

            document.getElementById('productId').value = product.id;
            document.getElementById('productTitle').value = product.title;
            document.getElementById('productSku').value = product.sku;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productSupplier').value = product.supplier_id || '';

            document.getElementById('productCustomizations').value = product.customizations ? JSON.stringify(product.customizations, null, 2) : '';

            document.getElementById('modalTitle').innerText = 'Edit Product';
            openModal();
        }

        async function saveProduct(e) {
            e.preventDefault();
            const id = document.getElementById('productId').value;

            let customizations = '';
            const custText = document.getElementById('productCustomizations').value.trim();
            if (custText) {
                try {
                    JSON.parse(custText); // Just to validate
                    customizations = custText;
                } catch (err) {
                    const globalError = document.getElementById('global-error');
                    globalError.innerText = 'Invalid JSON in Customizations field.';
                    globalError.classList.remove('hidden');
                    return;
                }
            }

            const formData = new FormData();
            formData.append('title', document.getElementById('productTitle').value);
            formData.append('sku', document.getElementById('productSku').value);
            formData.append('price', document.getElementById('productPrice').value);
            formData.append('supplier_id', document.getElementById('productSupplier').value);

            if (customizations) {
                formData.append('customizations', customizations);
            }

            const files = document.getElementById('productImages').files;
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }

            // PHP does not parse multipart/form-data on PUT/PATCH requests correctly
            // So we always send a POST request, but spoof the method to PUT if editing
            if (id) {
                formData.append('_method', 'PUT');
            }

            const url = id ? `/admin/products/${id}` : '/admin/products';

            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (res.ok) {
                closeModal();
                fetchProducts(document.getElementById('searchInput').value);
            } else {
                const data = await res.json();

                if (data.errors) {
                    for (const [key, messages] of Object.entries(data.errors)) {
                        // Handle array validations (e.g., images.0 -> images)
                        const baseKey = key.split('.')[0];
                        const errorEl = document.getElementById(`error-${baseKey}`);

                        if (errorEl) {
                            errorEl.innerText = messages.join(' ');
                            errorEl.classList.remove('hidden');
                        } else {
                            const globalError = document.getElementById('global-error');
                            globalError.innerText += (globalError.innerText ? '\n' : '') + messages.join(' ');
                            globalError.classList.remove('hidden');
                        }
                    }
                } else {
                    const globalError = document.getElementById('global-error');
                    globalError.innerText = data.message || 'An error occurred while saving.';
                    globalError.classList.remove('hidden');
                }
            }
        }

        async function toggle(id) {
            await fetch(`/admin/products/${id}/toggle-availability`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            fetchProducts(document.getElementById('searchInput').value);
        }

        async function deleteProduct(id) {
            if (!confirm('Are you sure you want to delete this product?')) return;

            const res = await fetch(`/admin/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                fetchProducts(document.getElementById('searchInput').value);
            } else {
                alert('Failed to delete product.');
            }
        }

        document.getElementById('searchInput').addEventListener('input', (e) => {
            setTimeout(() => fetchProducts(e.target.value), 300);
        });

        // Initialize
        fetchSuppliers().then(() => fetchProducts());
    </script>
</x-app-layout>
