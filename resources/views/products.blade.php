<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <input type="text" id="searchInput" placeholder="Search title or SKU..." class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm w-full max-w-md focus:ring-2 focus:ring-gray-900 focus:outline-none">
                    <button onclick="openModal()" class="bg-gray-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-800 transition-all shrink-0">
                        + Add Product
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="productsTable" class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3.5 font-semibold">Thumbnail</th>
                                <th class="px-4 py-3.5 font-semibold">SKU</th>
                                <th class="px-4 py-3.5 font-semibold">Title</th>
                                <th class="px-4 py-3.5 font-semibold">Price</th>
                                <th class="px-4 py-3.5 font-semibold">Images</th>
                                <th class="px-4 py-3.5 font-semibold">Supplier</th>
                                <th class="px-4 py-3.5 font-semibold">Availability</th>
                                <th class="px-4 py-3.5 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700"></tbody>
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

                <div class="mb-4 bg-gray-50 border border-gray-200 rounded-xl p-3.5 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Product Variations</label>
                        <span class="text-[11px] text-gray-400">Comma-separated</span>
                    </div>

                    <div>
                        <label for="productSizes" class="block text-xs font-medium text-gray-600 mb-1">Available Sizes</label>
                        <input type="text" id="productSizes" placeholder="e.g. S, M, L, XL or 30x40, 50x70" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:outline-none bg-white">
                        <p class="text-[11px] text-gray-400 mt-1">Separate sizes with commas (e.g. Small, Medium, Large)</p>
                    </div>

                    <div>
                        <label for="productColors" class="block text-xs font-medium text-gray-600 mb-1">Available Colors</label>
                        <input type="text" id="productColors" placeholder="e.g. Black, White, Navy, Heather Grey" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:outline-none bg-white">
                        <p class="text-[11px] text-gray-400 mt-1">Separate colors with commas (e.g. Black, White, Red)</p>
                    </div>

                    <input type="hidden" id="productCustomizations">
                    <p class="text-red-500 text-xs hidden error-msg" id="error-customizations"></p>
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
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <div class="font-semibold">${product.title}</div>
                        ${(() => {
                            const cust = product.customizations || {};
                            const s = Array.isArray(cust.sizes) ? cust.sizes.join(', ') : (cust.sizes || '');
                            const c = Array.isArray(cust.colors) ? cust.colors.join(', ') : (cust.colors || '');
                            if (!s && !c) return '';
                            return `<div class="flex flex-wrap gap-1 mt-1 text-[11px] text-gray-500">
                                ${s ? `<span class="bg-gray-100 px-1.5 py-0.5 rounded">Sizes: ${s}</span>` : ''}
                                ${c ? `<span class="bg-gray-100 px-1.5 py-0.5 rounded">Colors: ${c}</span>` : ''}
                            </div>`;
                        })()}
                    </td>
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
            document.getElementById('productSizes').value = '';
            document.getElementById('productColors').value = '';
            document.getElementById('productCustomizations').value = '';
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

            // Populate Sizes and Colors from customizations object
            const cust = product.customizations || {};
            let sizesStr = '';
            let colorsStr = '';

            if (Array.isArray(cust.sizes)) {
                sizesStr = cust.sizes.join(', ');
            } else if (typeof cust.sizes === 'string') {
                sizesStr = cust.sizes;
            } else if (cust.size) {
                sizesStr = Array.isArray(cust.size) ? cust.size.join(', ') : cust.size;
            }

            if (Array.isArray(cust.colors)) {
                colorsStr = cust.colors.join(', ');
            } else if (typeof cust.colors === 'string') {
                colorsStr = cust.colors;
            } else if (cust.color) {
                colorsStr = Array.isArray(cust.color) ? cust.color.join(', ') : cust.color;
            }

            document.getElementById('productSizes').value = sizesStr;
            document.getElementById('productColors').value = colorsStr;

            document.getElementById('modalTitle').innerText = 'Edit Product';
            openModal();
        }

        async function saveProduct(e) {
            e.preventDefault();
            const id = document.getElementById('productId').value;

            // Build structured customizations JSON from sizes & colors inputs
            const sizesRaw = document.getElementById('productSizes').value.trim();
            const colorsRaw = document.getElementById('productColors').value.trim();
            const custObj = {};

            if (sizesRaw) {
                custObj.sizes = sizesRaw.split(',')
                    .map(s => s.trim())
                    .filter(s => s.length > 0);
            }

            if (colorsRaw) {
                custObj.colors = colorsRaw.split(',')
                    .map(c => c.trim())
                    .filter(c => c.length > 0);
            }

            const customizations = Object.keys(custObj).length > 0 ? JSON.stringify(custObj) : '';

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
