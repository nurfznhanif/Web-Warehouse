<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Barang Masuk</h1>
        <p class="text-gray-600">Tambahkan transaksi barang masuk baru</p>
    </div>
    <a href="<?= base_url('/incoming-items') ?>"
        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
    </a>
</div>

<!-- Form -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="<?= base_url('/incoming-items/store') ?>" method="POST" id="incomingForm">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Product Selection -->
            <div class="md:col-span-2">
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Produk <span class="text-red-500">*</span>
                </label>
                <select id="product_id" name="product_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($validation) && $validation->hasError('product_id') ? 'border-red-500' : '' ?>">
                    <option value="">Pilih Produk</option>
                    <?php if (isset($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"
                                <?= old('product_id') == $product['id'] ? 'selected' : '' ?>
                                data-unit="<?= esc($product['unit']) ?>"
                                data-current-stock="<?= $product['stock'] ?>">
                                <?= esc($product['code']) ?> - <?= esc($product['name']) ?> (Stok: <?= number_format($product['stock']) ?> <?= esc($product['unit']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (isset($validation) && $validation->hasError('product_id')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('product_id') ?></p>
                <?php endif; ?>
            </div>

            <!-- Date -->
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" id="date" name="date" required
                    value="<?= old('date', date('Y-m-d')) ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($validation) && $validation->hasError('date') ? 'border-red-500' : '' ?>">
                <?php if (isset($validation) && $validation->hasError('date')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('date') ?></p>
                <?php endif; ?>
            </div>

            <!-- Quantity -->
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                    Kuantitas <span class="text-red-500">*</span>
                    <span id="unit-display" class="text-gray-500"></span>
                </label>
                <input type="number" id="quantity" name="quantity" step="0.01" min="0.01" required
                    value="<?= old('quantity') ?>"
                    placeholder="Masukkan jumlah barang"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($validation) && $validation->hasError('quantity') ? 'border-red-500' : '' ?>">
                <?php if (isset($validation) && $validation->hasError('quantity')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('quantity') ?></p>
                <?php endif; ?>
            </div>

            <!-- Purchase Order (Optional) -->
            <div class="md:col-span-2">
                <label for="purchase_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Purchase Order (Opsional)
                </label>
                <select id="purchase_id" name="purchase_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($validation) && $validation->hasError('purchase_id') ? 'border-red-500' : '' ?>">
                    <option value="">Pilih Purchase Order (Opsional)</option>
                    <?php if (isset($purchases)): ?>
                        <?php foreach ($purchases as $purchase): ?>
                            <option value="<?= $purchase['id'] ?>"
                                <?= old('purchase_id') == $purchase['id'] ? 'selected' : '' ?>
                                data-vendor="<?= esc($purchase['vendor_name']) ?>"
                                data-items="<?= esc(json_encode($purchase['items'] ?? [])) ?>">
                                PO-<?= $purchase['id'] ?> - <?= esc($purchase['vendor_name']) ?>
                                (<?= date('d M Y', strtotime($purchase['purchase_date'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (isset($validation) && $validation->hasError('purchase_id')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('purchase_id') ?></p>
                <?php endif; ?>
                <p class="mt-1 text-sm text-gray-500">Jika barang masuk dari purchase order, pilih PO yang sesuai</p>
            </div>

            <!-- Purchase Items Info (Will be populated by JavaScript) -->
            <div id="purchase-items-info" class="md:col-span-2 hidden">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Item dalam Purchase Order:</h4>
                    <div id="purchase-items-list" class="space-y-2"></div>
                </div>
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan
                </label>
                <textarea id="notes" name="notes" rows="3"
                    placeholder="Catatan tambahan untuk transaksi ini..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($validation) && $validation->hasError('notes') ? 'border-red-500' : '' ?>"><?= old('notes') ?></textarea>
                <?php if (isset($validation) && $validation->hasError('notes')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('notes') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Current Stock Info -->
        <div id="current-stock-info" class="mt-6 p-4 bg-gray-50 rounded-lg hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Stok Saat Ini:</p>
                    <p id="current-stock-value" class="text-lg font-bold text-gray-900"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Stok Setelah Penambahan:</p>
                    <p id="new-stock-value" class="text-lg font-bold text-green-600"></p>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
            <a href="<?= base_url('/incoming-items') ?>"
                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium flex items-center">
                <i class="fas fa-save mr-2"></i>
                Simpan Barang Masuk
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.getElementById('product_id');
        const quantityInput = document.getElementById('quantity');
        const unitDisplay = document.getElementById('unit-display');
        const currentStockInfo = document.getElementById('current-stock-info');
        const currentStockValue = document.getElementById('current-stock-value');
        const newStockValue = document.getElementById('new-stock-value');
        const purchaseSelect = document.getElementById('purchase_id');
        const purchaseItemsInfo = document.getElementById('purchase-items-info');
        const purchaseItemsList = document.getElementById('purchase-items-list');

        // Update unit display and stock info when product changes
        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {
                const unit = selectedOption.dataset.unit;
                const currentStock = parseFloat(selectedOption.dataset.currentStock) || 0;

                unitDisplay.textContent = `(${unit})`;
                currentStockValue.textContent = `${currentStock.toLocaleString()} ${unit}`;
                currentStockInfo.classList.remove('hidden');

                updateNewStock();
            } else {
                unitDisplay.textContent = '';
                currentStockInfo.classList.add('hidden');
            }
        });

        // Update new stock calculation when quantity changes
        quantityInput.addEventListener('input', updateNewStock);

        function updateNewStock() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const quantity = parseFloat(quantityInput.value) || 0;

            if (selectedOption.value && quantity > 0) {
                const currentStock = parseFloat(selectedOption.dataset.currentStock) || 0;
                const unit = selectedOption.dataset.unit;
                const newStock = currentStock + quantity;

                newStockValue.textContent = `${newStock.toLocaleString()} ${unit}`;
            } else {
                newStockValue.textContent = '';
            }
        }

        // Handle purchase order selection
        purchaseSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {
                try {
                    const items = JSON.parse(selectedOption.dataset.items || '[]');

                    if (items.length > 0) {
                        purchaseItemsList.innerHTML = '';
                        items.forEach(item => {
                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'flex justify-between items-center text-sm';
                            itemDiv.innerHTML = `
                            <span class="text-blue-800">${item.product_name}</span>
                            <span class="font-medium">${item.quantity} ${item.unit}</span>
                        `;
                            purchaseItemsList.appendChild(itemDiv);
                        });
                        purchaseItemsInfo.classList.remove('hidden');
                    } else {
                        purchaseItemsInfo.classList.add('hidden');
                    }
                } catch (e) {
                    console.error('Error parsing purchase items:', e);
                    purchaseItemsInfo.classList.add('hidden');
                }
            } else {
                purchaseItemsInfo.classList.add('hidden');
            }
        });

        // Initialize if there's a selected product
        if (productSelect.value) {
            productSelect.dispatchEvent(new Event('change'));
        }

        // Initialize if there's a selected purchase
        if (purchaseSelect.value) {
            purchaseSelect.dispatchEvent(new Event('change'));
        }

        // Form validation
        document.getElementById('incomingForm').addEventListener('submit', function(e) {
            const product = productSelect.value;
            const quantity = parseFloat(quantityInput.value);
            const date = document.getElementById('date').value;

            if (!product) {
                e.preventDefault();
                alert('Silakan pilih produk terlebih dahulu');
                productSelect.focus();
                return;
            }

            if (!quantity || quantity <= 0) {
                e.preventDefault();
                alert('Silakan masukkan kuantitas yang valid');
                quantityInput.focus();
                return;
            }

            if (!date) {
                e.preventDefault();
                alert('Silakan pilih tanggal');
                document.getElementById('date').focus();
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            submitBtn.disabled = true;

            // Re-enable button after 5 seconds as fallback
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    });
</script>

<?= $this->endSection() ?>