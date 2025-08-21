<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded notification">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Barang Keluar</h1>
        <p class="text-gray-600">Ubah data transaksi barang keluar</p>
    </div>
    <a href="<?= base_url('/outgoing-items') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
    </a>
</div>

<!-- Current Transaction Info -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <div class="flex items-center">
        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
        <div>
            <h3 class="text-sm font-medium text-blue-900">Informasi Transaksi Saat Ini</h3>
            <p class="text-sm text-blue-700">
                <?= esc($outgoing_item['product_name']) ?> -
                Jumlah: <?= number_format($outgoing_item['quantity']) ?> <?= esc($outgoing_item['unit']) ?> -
                Tanggal: <?= date('d/m/Y H:i', strtotime($outgoing_item['date'])) ?>
            </p>
        </div>
    </div>
</div>

<!-- Form -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="<?= base_url('/outgoing-items/update/' . $outgoing_item['id']) ?>" method="POST" id="editOutgoingForm">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Product Selection -->
            <div class="md:col-span-2">
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Produk <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <select name="product_id" id="product_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            onchange="updateProductInfo()">
                            <option value="">Pilih Produk</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"
                                    data-stock="<?= $product['stock'] ?>"
                                    data-unit="<?= $product['unit'] ?>"
                                    data-min-stock="<?= $product['min_stock'] ?>"
                                    data-name="<?= esc($product['name']) ?>"
                                    data-code="<?= esc($product['code']) ?>"
                                    <?= $outgoing_item['product_id'] == $product['id'] ? 'selected' : '' ?>>
                                    <?= esc($product['name']) ?> (<?= esc($product['code']) ?>) - Stok: <?= number_format($product['stock']) ?> <?= esc($product['unit']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($validation && $validation->hasError('product_id')): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $validation->getError('product_id') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Product Info Display -->
                    <div id="product_info" class="bg-gray-50 p-3 rounded-md">
                        <div class="text-sm">
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Stok Tersedia:</span>
                                <span id="current_stock" class="font-medium">-</span>
                            </div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Satuan:</span>
                                <span id="product_unit" class="font-medium">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Stok Minimum:</span>
                                <span id="min_stock" class="font-medium">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date -->
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date" id="date" value="<?= date('Y-m-d', strtotime($outgoing_item['date'])) ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <?php if ($validation && $validation->hasError('date')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('date') ?></p>
                <?php endif; ?>
            </div>

            <!-- Quantity -->
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                    Jumlah Keluar <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="quantity" id="quantity" step="0.01" min="0.01"
                        value="<?= $outgoing_item['quantity'] ?>" required
                        placeholder="Masukkan jumlah"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        oninput="validateQuantity()">
                    <div id="quantity_unit" class="absolute right-3 top-2 text-gray-500"></div>
                </div>
                <div id="quantity_warning" class="mt-1 text-sm text-red-600 hidden"></div>
                <div id="stock_warning" class="mt-1 text-sm text-yellow-600 hidden"></div>
                <?php if ($validation && $validation->hasError('quantity')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('quantity') ?></p>
                <?php endif; ?>
            </div>

            <!-- Recipient -->
            <div>
                <label for="recipient" class="block text-sm font-medium text-gray-700 mb-2">
                    Penerima
                </label>
                <input type="text" name="recipient" id="recipient" value="<?= esc($outgoing_item['recipient']) ?>"
                    placeholder="Nama penerima barang" maxlength="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <?php if ($validation && $validation->hasError('recipient')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('recipient') ?></p>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Keterangan
                </label>
                <textarea name="description" id="description" rows="3" maxlength="500"
                    placeholder="Keterangan tambahan untuk transaksi barang keluar..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"><?= esc($outgoing_item['description']) ?></textarea>
                <p class="mt-1 text-sm text-gray-500">Maksimal 500 karakter</p>
                <?php if ($validation && $validation->hasError('description')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $validation->getError('description') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stock Warning -->
        <div id="stock_alert" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md hidden">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Peringatan Stok!</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p id="stock_alert_message"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="mt-6 flex justify-end space-x-3">
            <a href="<?= base_url('/outgoing-items') ?>"
                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md flex items-center">
                <i class="fas fa-save mr-2"></i>
                Update Barang Keluar
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let currentStock = 0;
    let minStock = 0;
    let productUnit = '';
    let originalQuantity = <?= $outgoing_item['quantity'] ?>;

    function updateProductInfo() {
        const select = document.getElementById('product_id');
        const selectedOption = select.options[select.selectedIndex];
        const productInfo = document.getElementById('product_info');
        const currentStockElement = document.getElementById('current_stock');
        const productUnitElement = document.getElementById('product_unit');
        const minStockElement = document.getElementById('min_stock');
        const quantityUnit = document.getElementById('quantity_unit');

        if (selectedOption.value) {
            // For edit mode, we need to consider the original quantity when calculating available stock
            let rawStock = parseFloat(selectedOption.dataset.stock);
            if (selectedOption.value == <?= $outgoing_item['product_id'] ?>) {
                // If it's the same product, add back the original quantity
                currentStock = rawStock + originalQuantity;
            } else {
                currentStock = rawStock;
            }

            minStock = parseFloat(selectedOption.dataset.minStock);
            productUnit = selectedOption.dataset.unit;

            currentStockElement.textContent = `${currentStock.toLocaleString()} ${productUnit}`;
            productUnitElement.textContent = productUnit;
            minStockElement.textContent = `${minStock.toLocaleString()} ${productUnit}`;
            quantityUnit.textContent = productUnit;

            productInfo.classList.remove('hidden');

            // Reset quantity validation
            validateQuantity();
        } else {
            productInfo.classList.add('hidden');
            quantityUnit.textContent = '';
            hideStockAlerts();
        }
    }

    function validateQuantity() {
        const quantityInput = document.getElementById('quantity');
        const quantity = parseFloat(quantityInput.value) || 0;
        const warningElement = document.getElementById('quantity_warning');
        const stockWarningElement = document.getElementById('stock_warning');
        const stockAlert = document.getElementById('stock_alert');
        const stockAlertMessage = document.getElementById('stock_alert_message');
        const submitBtn = document.getElementById('submitBtn');

        // Clear previous warnings
        warningElement.classList.add('hidden');
        stockWarningElement.classList.add('hidden');
        stockAlert.classList.add('hidden');
        submitBtn.disabled = false;

        if (quantity > 0 && currentStock > 0) {
            // Check if quantity exceeds available stock
            if (quantity > currentStock) {
                warningElement.textContent = `Jumlah melebihi stok tersedia (${currentStock.toLocaleString()} ${productUnit})`;
                warningElement.classList.remove('hidden');
                submitBtn.disabled = true;
                return;
            }

            // Check if remaining stock will be below minimum
            const remainingStock = currentStock - quantity;
            if (remainingStock < minStock) {
                stockAlertMessage.textContent = `Setelah transaksi ini, stok akan menjadi ${remainingStock.toLocaleString()} ${productUnit}, di bawah batas minimum ${minStock.toLocaleString()} ${productUnit}.`;
                stockAlert.classList.remove('hidden');
            }

            // Warning if taking large portion of stock
            if (quantity > currentStock * 0.5) {
                stockWarningElement.textContent = `Anda akan mengeluarkan lebih dari 50% stok tersedia.`;
                stockWarningElement.classList.remove('hidden');
            }
        }
    }

    function hideStockAlerts() {
        document.getElementById('quantity_warning').classList.add('hidden');
        document.getElementById('stock_warning').classList.add('hidden');
        document.getElementById('stock_alert').classList.add('hidden');
    }

    // Form submission with loading state
    document.getElementById('editOutgoingForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');

        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengupdate...';
        submitBtn.disabled = true;
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateProductInfo();
    });
</script>
<?= $this->endSection() ?>