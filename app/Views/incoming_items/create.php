<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Tambah Barang Masuk</h1>
        <p class="mt-2 text-gray-600">Catat penerimaan barang dari pembelian</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        <form action="<?= base_url('/incoming-items/store') ?>" method="POST" id="incomingForm">
            <?= csrf_field() ?>

            <div class="p-6 space-y-6">
                <!-- Alert untuk error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <!-- Pilih Pembelian -->
                <div class="space-y-2">
                    <label for="purchase_id" class="block text-sm font-medium text-gray-700">
                        Pembelian <span class="text-red-500">*</span>
                    </label>
                    <select name="purchase_id" id="purchase_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Pembelian</option>
                        <?php foreach ($purchases as $purchase): ?>
                            <option value="<?= $purchase['id'] ?>"
                                data-vendor="<?= esc($purchase['vendor_name']) ?>"
                                data-date="<?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?>"
                                <?= old('purchase_id') == $purchase['id'] ? 'selected' : '' ?>>
                                Purchase #<?= $purchase['id'] ?> - <?= esc($purchase['vendor_name']) ?>
                                (<?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($validation) && $validation->hasError('purchase_id')): ?>
                        <p class="text-sm text-red-600"><?= $validation->getError('purchase_id') ?></p>
                    <?php endif; ?>
                    <p class="text-sm text-gray-500">Pilih pembelian yang akan diterima barangnya</p>
                </div>

                <!-- Info Pembelian -->
                <div id="purchaseInfo" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-medium text-blue-900 mb-2">Informasi Pembelian</h3>
                    <div class="text-sm text-blue-800">
                        <p><strong>Vendor:</strong> <span id="vendorName">-</span></p>
                        <p><strong>Tanggal Pembelian:</strong> <span id="purchaseDate">-</span></p>
                    </div>
                </div>

                <!-- Produk yang akan diterima -->
                <div id="productSection" class="hidden space-y-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Produk yang akan diterima
                    </label>
                    <div id="productList" class="space-y-3 border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto">
                        <!-- Akan diisi via JavaScript -->
                    </div>
                </div>

                <!-- Tanggal Penerimaan -->
                <div class="space-y-2">
                    <label for="date" class="block text-sm font-medium text-gray-700">
                        Tanggal Penerimaan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" id="date" required
                        value="<?= old('date', date('Y-m-d')) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <?php if (isset($validation) && $validation->hasError('date')): ?>
                        <p class="text-sm text-red-600"><?= $validation->getError('date') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Catatan -->
                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Catatan
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                        placeholder="Catatan tambahan (opsional)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= old('notes') ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('notes')): ?>
                        <p class="text-sm text-red-600"><?= $validation->getError('notes') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <a href="<?= base_url('/incoming-items') ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500">
                    Batal
                </a>
                <button type="submit" id="submitBtn" disabled
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Barang Masuk
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const purchaseSelect = document.getElementById('purchase_id');
        const purchaseInfo = document.getElementById('purchaseInfo');
        const productSection = document.getElementById('productSection');
        const productList = document.getElementById('productList');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('incomingForm');

        let selectedProducts = [];

        purchaseSelect.addEventListener('change', function() {
            const purchaseId = this.value;

            if (!purchaseId) {
                purchaseInfo.classList.add('hidden');
                productSection.classList.add('hidden');
                submitBtn.disabled = true;
                return;
            }

            // Show purchase info
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('vendorName').textContent = selectedOption.dataset.vendor;
            document.getElementById('purchaseDate').textContent = selectedOption.dataset.date;
            purchaseInfo.classList.remove('hidden');

            // Load purchase items
            loadPurchaseItems(purchaseId);
        });

        function loadPurchaseItems(purchaseId) {
            fetch(`<?= base_url('/incoming-items/get-purchase-items') ?>/${purchaseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        displayPurchaseItems(data);
                        productSection.classList.remove('hidden');
                    } else {
                        productList.innerHTML = '<p class="text-gray-500 text-center py-4">Tidak ada produk yang perlu diterima</p>';
                        productSection.classList.remove('hidden');
                        submitBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error loading purchase items:', error);
                    productList.innerHTML = '<p class="text-red-500 text-center py-4">Gagal memuat data produk</p>';
                    productSection.classList.remove('hidden');
                    submitBtn.disabled = true;
                });
        }

        function displayPurchaseItems(items) {
            selectedProducts = [];
            let html = '';

            items.forEach((item, index) => {
                const remainingQty = item.quantity - (item.received_quantity || 0);

                if (remainingQty > 0) {
                    html += `
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">${item.product_name}</h4>
                            <p class="text-sm text-gray-600">
                                Dibeli: ${parseInt(item.quantity)} ${item.unit} | 
                                Diterima: ${parseInt(item.received_quantity || 0)} ${item.unit} | 
                                Sisa: ${parseInt(remainingQty)} ${item.unit}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" 
                                   id="product_${item.product_id}" 
                                   data-product-id="${item.product_id}"
                                   data-max-qty="${remainingQty}"
                                   data-unit="${item.unit}"
                                   class="product-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="product_${item.product_id}" class="text-sm font-medium text-gray-700">
                                Terima
                            </label>
                        </div>
                    </div>
                `;
                }
            });

            if (html === '') {
                html = '<p class="text-gray-500 text-center py-4">Semua produk sudah diterima lengkap</p>';
                submitBtn.disabled = true;
            } else {
                html += `
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                        <p class="text-sm text-yellow-800">
                            Pilih produk yang akan diterima. Kuantitas akan otomatis disesuaikan dengan sisa yang belum diterima.
                        </p>
                    </div>
                </div>
            `;
            }

            productList.innerHTML = html;

            // Add event listeners for checkboxes
            document.querySelectorAll('.product-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedProducts);
            });
        }

        function updateSelectedProducts() {
            selectedProducts = [];
            const checkboxes = document.querySelectorAll('.product-checkbox:checked');

            checkboxes.forEach(checkbox => {
                selectedProducts.push({
                    product_id: checkbox.dataset.productId,
                    quantity: parseInt(checkbox.dataset.maxQty),
                    unit: checkbox.dataset.unit
                });
            });

            submitBtn.disabled = selectedProducts.length === 0;
        }

        form.addEventListener('submit', function(e) {
            if (selectedProducts.length === 0) {
                e.preventDefault();
                alert('Pilih minimal satu produk untuk diterima');
                return;
            }

            // Create hidden inputs for selected products
            selectedProducts.forEach(product => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_products[]';
                input.value = JSON.stringify(product);
                form.appendChild(input);
            });
        });
    });
</script>

<?= $this->endSection() ?>