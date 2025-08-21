<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Barang Masuk</h1>
        <p class="text-gray-600 mt-1">Edit transaksi barang masuk #<?= $incoming_item['id'] ?></p>
    </div>
    <a href="<?= base_url('/incoming-items') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
    </a>
</div>

<!-- Alert for validation errors -->
<?php if (isset($validation) && $validation->getErrors()): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Terdapat kesalahan pada form:</strong>
        </div>
        <ul class="mt-2 list-disc list-inside">
            <?php foreach ($validation->getErrors() as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Form -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Edit Data Barang Masuk</h3>
            
            <form id="incomingForm" action="<?= base_url('/incoming-items/update/' . $incoming_item['id']) ?>" method="POST">
                <?= csrf_field() ?>
                
                <!-- Original Transaction Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Informasi Transaksi Asli</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Tanggal:</span>
                            <span class="ml-2 font-medium"><?= date('d M Y H:i', strtotime($incoming_item['date'])) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">User:</span>
                            <span class="ml-2 font-medium"><?= esc($incoming_item['user_name'] ?? 'Unknown') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Purchase Selection (Read-only if from purchase) -->
                <div class="mb-6">
                    <label for="purchase_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Pembelian Terkait
                    </label>
                    <?php if ($incoming_item['purchase_id']): ?>
                        <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
                            PO #<?= $incoming_item['purchase_id'] ?> - <?= esc($incoming_item['vendor_name'] ?? 'Unknown Vendor') ?>
                        </div>
                        <input type="hidden" name="purchase_id" value="<?= $incoming_item['purchase_id'] ?>">
                        <p class="text-xs text-gray-500 mt-1">
                            Transaksi ini terkait dengan purchase order dan tidak dapat diubah
                        </p>
                    <?php else: ?>
                        <select id="purchase_id" 
                                name="purchase_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Manual Entry (Tanpa Pembelian)</option>
                            <?php if (!empty($purchases)): ?>
                                <?php foreach ($purchases as $purchase): ?>
                                    <option value="<?= $purchase['id'] ?>" 
                                            <?= old('purchase_id', $incoming_item['purchase_id']) == $purchase['id'] ? 'selected' : '' ?>>
                                        PO #<?= $purchase['id'] ?> - <?= esc($purchase['vendor_name']) ?> 
                                        (<?= date('d M Y', strtotime($purchase['purchase_date'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Product Selection -->
                <div class="mb-6">
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Produk <span class="text-red-500">*</span>
                    </label>
                    <select id="product_id" 
                            name="product_id" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 <?= isset($validation) && $validation->getError('product_id') ? 'border-red-500' : '' ?>">
                        <option value="">Pilih Produk</option>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" 
                                        data-unit="<?= esc($product['unit']) ?>"
                                        data-stock="<?= $product['stock'] ?>"
                                        <?= old('product_id', $incoming_item['product_id']) == $product['id'] ? 'selected' : '' ?>>
                                    <?= esc($product['code']) ?> - <?= esc($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($validation) && $validation->getError('product_id')): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('product_id') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Date -->
                <div class="mb-6">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="<?= old('date', date('Y-m-d', strtotime($incoming_item['date']))) ?>"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 <?= isset($validation) && $validation->getError('date') ? 'border-red-500' : '' ?>">
                    <?php if (isset($validation) && $validation->getError('date')): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('date') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Quantity -->
                <div class="mb-6">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <div class="flex">
                        <input type="number" 
                               id="quantity" 
                               name="quantity" 
                               value="<?= old('quantity', $incoming_item['quantity']) ?>"
                               step="0.01"
                               min="0.01"
                               required
                               placeholder="0.00"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-green-500 <?= isset($validation) && $validation->getError('quantity') ? 'border-red-500' : '' ?>">
                        <span id="unitDisplay" class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md text-gray-700">
                            <?= esc($incoming_item['unit'] ?? 'Unit') ?>
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Quantity asli: <?= number_format($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?>
                    </div>
                    <div id="quantityWarning" class="text-orange-600 text-xs mt-1 hidden"></div>
                    <div id="quantityError" class="text-red-600 text-xs mt-1 hidden"></div>
                    <?php if (isset($validation) && $validation->getError('quantity')): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('quantity') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Stock Impact Information -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-medium text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Dampak Perubahan Stok
                    </h4>
                    <div id="stockImpact" class="text-sm text-blue-800">
                        <!-- Stock impact will be calculated here -->
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="3"
                              placeholder="Tambahkan catatan mengenai perubahan ini..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 <?= isset($validation) && $validation->getError('notes') ? 'border-red-500' : '' ?>"><?= old('notes', $incoming_item['notes']) ?></textarea>
                    <?php if (isset($validation) && $validation->getError('notes')): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('notes') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="<?= base_url('/incoming-items') ?>" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" 
                            id="submitBtn"
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <i class="fas fa-save mr-2"></i>
                        Update Barang Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Information Panel -->
    <div class="lg:col-span-1">
        <!-- Current Product Info -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Produk Saat Ini</h3>
            <div class="space-y-3">
                <div>
                    <div class="text-sm font-medium text-gray-700">Nama Produk</div>
                    <div class="text-sm text-gray-900"><?= esc($incoming_item['product_name']) ?></div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Kode Produk</div>
                    <div class="text-sm text-gray-900"><?= esc($incoming_item['product_code']) ?></div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Kategori</div>
                    <div class="text-sm text-gray-900"><?= esc($incoming_item['category_name'] ?? '-') ?></div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Stok Saat Ini</div>
                    <div class="text-sm">
                        <span class="font-medium text-blue-600" id="currentStock"><?= number_format($incoming_item['current_stock'] ?? 0) ?></span>
                        <span class="text-gray-600"><?= esc($incoming_item['unit']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Guidelines -->
        <div class="bg-yellow-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-yellow-900 mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Peringatan Edit
            </h3>
            <div class="space-y-3 text-sm text-yellow-800">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-0.5 text-yellow-600"></i>
                    <span>Perubahan quantity akan mempengaruhi stok produk</span>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-0.5 text-yellow-600"></i>
                    <span>Pastikan perubahan sesuai dengan kondisi fisik barang</span>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-0.5 text-yellow-600"></i>
                    <span>Transaksi dari purchase order memiliki batasan edit</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const unitDisplay = document.getElementById('unitDisplay');
    const stockImpact = document.getElementById('stockImpact');
    const currentStockSpan = document.getElementById('currentStock');
    
    const originalQuantity = <?= $incoming_item['quantity'] ?>;
    const currentStock = <?= $incoming_item['current_stock'] ?? 0 ?>;
    const productUnit = '<?= esc($incoming_item['unit']) ?>';
    
    // Handle product selection change
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const unit = selectedOption.dataset.unit;
            unitDisplay.textContent = unit || 'Unit';
            calculateStockImpact();
        } else {
            unitDisplay.textContent = 'Unit';
            stockImpact.innerHTML = '';
        }
    });

    // Handle quantity change
    quantityInput.addEventListener('input', calculateStockImpact);

    function calculateStockImpact() {
        const newQuantity = parseFloat(quantityInput.value) || 0;
        const quantityDifference = newQuantity - originalQuantity;
        const newStock = currentStock + quantityDifference;
        
        let impactHTML = `
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Quantity Asli:</span>
                    <span>${originalQuantity.toLocaleString()} ${productUnit}</span>
                </div>
                <div class="flex justify-between">
                    <span>Quantity Baru:</span>
                    <span>${newQuantity.toLocaleString()} ${productUnit}</span>
                </div>
                <div class="flex justify-between font-medium ${quantityDifference >= 0 ? 'text-green-700' : 'text-red-700'}">
                    <span>Perubahan:</span>
                    <span>${quantityDifference >= 0 ? '+' : ''}${quantityDifference.toLocaleString()} ${productUnit}</span>
                </div>
                <hr class="border-blue-200">
                <div class="flex justify-between">
                    <span>Stok Sekarang:</span>
                    <span>${currentStock.toLocaleString()} ${productUnit}</span>
                </div>
                <div class="flex justify-between font-medium ${newStock >= 0 ? 'text-blue-700' : 'text-red-700'}">
                    <span>Stok Setelah Edit:</span>
                    <span>${newStock.toLocaleString()} ${productUnit}</span>
                </div>
            </div>
        `;
        
        if (newStock < 0) {
            impactHTML += `
                <div class="mt-3 p-2 bg-red-100 rounded text-red-700 text-xs">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Peringatan: Stok akan menjadi negatif!
                </div>
            `;
        }
        
        stockImpact.innerHTML = impactHTML;
    }

    // Initialize calculation
    calculateStockImpact();

    // Form validation
    document.getElementById('incomingForm').addEventListener('submit', function(e) {
        const newQuantity = parseFloat(quantityInput.value) || 0;
        const quantityDifference = newQuantity - originalQuantity;
        const newStock = currentStock + quantityDifference;
        
        if (newStock < 0) {
            e.preventDefault();
            if (!confirm('Perubahan ini akan menyebabkan stok menjadi negatif. Apakah Anda yakin ingin melanjutkan?')) {
                return false;
            }
        }
        
        if (Math.abs(quantityDifference) > 0) {
            if (!confirm(`Anda akan mengubah quantity dari ${originalQuantity} menjadi ${newQuantity} ${productUnit}. Ini akan ${quantityDifference > 0 ? 'menambah' : 'mengurangi'} stok sebesar ${Math.abs(quantityDifference)} ${productUnit}. Lanjutkan?`)) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
<?= $this->endSection() ?>