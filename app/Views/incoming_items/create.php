<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Barang Masuk</h1>
        <p class="text-gray-600 mt-1">Catat transaksi barang masuk ke dalam inventory</p>
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
            <h3 class="text-lg font-medium text-gray-900 mb-6">Form Barang Masuk</h3>
            
            <form id="incomingForm" action="<?= base_url('/incoming-items/store') ?>" method="POST">
                <?= csrf_field() ?>
                
                <!-- Purchase Selection -->
                <div class="mb-6">
                    <label for="purchase_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Pembelian (Opsional)
                    </label>
                    <select id="purchase_id" 
                            name="purchase_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Manual Entry (Tanpa Pembelian)</option>
                        <?php if (!empty($purchases)): ?>
                            <?php foreach ($purchases as $purchase): ?>
                                <option value="<?= $purchase['id'] ?>" 
                                        <?= old('purchase_id') == $purchase['id'] ? 'selected' : '' ?>>
                                    PO #<?= $purchase['id'] ?> - <?= esc($purchase['vendor_name']) ?> 
                                    (<?= date('d M Y', strtotime($purchase['purchase_date'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Pilih pembelian jika barang masuk berasal dari purchase order
                    </p>
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
                                        <?= old('product_id') == $product['id'] ? 'selected' : '' ?>>
                                    <?= esc($product['code']) ?> - <?= esc($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($validation) && $validation->getError('product_id')): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('product_id') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Purchase Items (shown when purchase is selected) -->
                <div id="purchaseItems" class="mb-6 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Item dari Pembelian
                    </label>
                    <div id="purchaseItemsList" class="space-y-2">
                        <!-- Items will be loaded here via AJAX -->
                    </div>
                </div>

                <!-- Date -->
                <div class="mb-6">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="<?= old('date', date('Y-m-d')) ?>"
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
                               value="<?= old('quantity') ?>"
                               step="0.01"
                               min="0.01"
                               required
                               placeholder="0.00"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-green-500 <?= isset($validation) && $validation->getError('quantity') ? 'border-red-500' : '' ?>">
                        <span id="unitDisplay" class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md text-gray-700">
                            Unit
                        </span>
                    </div>
                    <div id="quantityWarning" class="text-orange-600 text-xs mt-1 hidden"></div>
                    <div id="quantityError" class="text-red-600 text-xs mt-1 hidden"></div>
                    <?php if (isset($validation) && $validation->getError('quantity')): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('quantity') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="3"
                              placeholder="Tambahkan catatan mengenai barang masuk ini..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 <?= isset($validation) && $validation->getError('notes') ? 'border-red-500' : '' ?>"><?= old('notes') ?></textarea>
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
                        Simpan Barang Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Information Panel -->
    <div class="lg:col-span-1">
        <!-- Product Info -->
        <div id="productInfo" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Produk</h3>
            <div id="productDetails">
                <!-- Product details will be loaded here -->
            </div>
        </div>

        <!-- Guidelines -->
        <div class="bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-blue-900 mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                Panduan Penggunaan
            </h3>
            <div class="space-y-3 text-sm text-blue-800">
                <div class="flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-600"></i>
                    <span>Pilih pembelian jika barang masuk dari purchase order</span>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-600"></i>
                    <span>Pastikan jumlah barang masuk sesuai dengan yang dibeli</span>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-600"></i>
                    <span>Stok produk akan otomatis bertambah setelah transaksi</span>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-600"></i>
                    <span>Validasi otomatis untuk quantity yang melebihi pembelian</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const purchaseSelect = document.getElementById('purchase_id');
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const unitDisplay = document.getElementById('unitDisplay');
    const productInfo = document.getElementById('productInfo');
    const productDetails = document.getElementById('productDetails');
    const purchaseItems = document.getElementById('purchaseItems');
    const purchaseItemsList = document.getElementById('purchaseItemsList');
    const quantityWarning = document.getElementById('quantityWarning');
    const quantityError = document.getElementById('quantityError');
    const submitBtn = document.getElementById('submitBtn');

    let selectedPurchaseItems = {};
    let currentProductData = null;

    // Handle purchase selection
    purchaseSelect.addEventListener('change', function() {
        const purchaseId = this.value;
        
        if (purchaseId) {
            loadPurchaseItems(purchaseId);
            purchaseItems.classList.remove('hidden');
        } else {
            purchaseItems.classList.add('hidden');
            productSelect.disabled = false;
            clearProductSelection();
        }
    });

    // Handle product selection
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const unit = selectedOption.dataset.unit;
            const stock = selectedOption.dataset.stock;
            
            unitDisplay.textContent = unit || 'Unit';
            loadProductInfo(this.value);
            
            currentProductData = {
                unit: unit,
                stock: parseInt(stock) || 0
            };
        } else {
            unitDisplay.textContent = 'Unit';
            productInfo.classList.add('hidden');
            currentProductData = null;
        }
        
        validateQuantity();
    });

    // Handle quantity input
    quantityInput.addEventListener('input', validateQuantity);

    function loadPurchaseItems(purchaseId) {
        fetch(`<?= base_url('/incoming-items/get-purchase-items/') ?>${purchaseId}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    displayPurchaseItems(data);
                    restrictProductSelection(data);
                } else {
                    purchaseItemsList.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada item dalam pembelian ini</p>';
                    productSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error loading purchase items:', error);
                purchaseItemsList.innerHTML = '<p class="text-red-500 text-sm">Gagal memuat item pembelian</p>';
            });
    }

    function displayPurchaseItems(items) {
        purchaseItemsList.innerHTML = '';
        
        items.forEach(item => {
            const remainingQty = item.quantity - (item.received_quantity || 0);
            const isFullyReceived = remainingQty <= 0;
            
            const itemElement = document.createElement('div');
            itemElement.className = `p-3 border rounded-md ${isFullyReceived ? 'bg-gray-50 border-gray-200' : 'bg-green-50 border-green-200 cursor-pointer hover:bg-green-100'}`;
            
            itemElement.innerHTML = `
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <div class="font-medium text-sm">${item.product_name}</div>
                        <div class="text-xs text-gray-500">${item.product_code}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm">
                            <span class="text-gray-600">Dibeli:</span> 
                            <span class="font-medium">${item.quantity} ${item.unit}</span>
                        </div>
                        <div class="text-xs">
                            <span class="text-gray-600">Diterima:</span> 
                            <span class="text-green-600">${item.received_quantity || 0}</span>
                        </div>
                        <div class="text-xs">
                            <span class="text-gray-600">Sisa:</span> 
                            <span class="font-medium ${isFullyReceived ? 'text-gray-400' : 'text-blue-600'}">${remainingQty}</span>
                        </div>
                    </div>
                </div>
                ${isFullyReceived ? 
                    '<div class="text-xs text-gray-400 mt-1">Sudah diterima lengkap</div>' : 
                    '<div class="text-xs text-green-600 mt-1">Klik untuk pilih produk ini</div>'
                }
            `;
            
            if (!isFullyReceived) {
                itemElement.addEventListener('click', () => selectPurchaseItem(item));
            }
            
            purchaseItemsList.appendChild(itemElement);
        });
        
        selectedPurchaseItems = {};
        items.forEach(item => {
            selectedPurchaseItems[item.product_id] = item;
        });
    }

    function selectPurchaseItem(item) {
        // Set product selection
        productSelect.value = item.product_id;
        productSelect.dispatchEvent(new Event('change'));
        
        // Set max quantity to remaining quantity
        const remainingQty = item.quantity - (item.received_quantity || 0);
        quantityInput.max = remainingQty;
        quantityInput.value = remainingQty; // Auto-fill with remaining quantity
        
        validateQuantity();
    }

    function restrictProductSelection(items) {
        // Disable products not in purchase
        const purchaseProductIds = items.map(item => item.product_id.toString());
        
        Array.from(productSelect.options).forEach(option => {
            if (option.value && !purchaseProductIds.includes(option.value)) {
                option.disabled = true;
                option.style.color = '#ccc';
            } else {
                option.disabled = false;
                option.style.color = '';
            }
        });
    }

    function clearProductSelection() {
        productSelect.value = '';
        quantityInput.value = '';
        quantityInput.max = '';
        productInfo.classList.add('hidden');
        currentProductData = null;
        
        // Re-enable all product options
        Array.from(productSelect.options).forEach(option => {
            option.disabled = false;
            option.style.color = '';
        });
        
        clearQuantityMessages();
    }

    function loadProductInfo(productId) {
        fetch(`<?= base_url('/incoming-items/get-product-info/') ?>${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.found) {
                    displayProductInfo(data.product);
                    productInfo.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading product info:', error);
            });
    }

    function displayProductInfo(product) {
        productDetails.innerHTML = `
            <div class="space-y-3">
                <div>
                    <div class="text-sm font-medium text-gray-700">Nama Produk</div>
                    <div class="text-sm text-gray-900">${product.name}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Kode Produk</div>
                    <div class="text-sm text-gray-900">${product.code}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Kategori</div>
                    <div class="text-sm text-gray-900">${product.category_name || '-'}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Stok Saat Ini</div>
                    <div class="text-sm">
                        <span class="font-medium text-blue-600">${product.stock}</span>
                        <span class="text-gray-600">${product.unit}</span>
                    </div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Stok Minimum</div>
                    <div class="text-sm">
                        <span class="font-medium ${product.stock <= product.min_stock ? 'text-red-600' : 'text-green-600'}">${product.min_stock || 0}</span>
                        <span class="text-gray-600">${product.unit}</span>
                    </div>
                </div>
            </div>
        `;
    }

    function validateQuantity() {
        clearQuantityMessages();
        
        const quantity = parseFloat(quantityInput.value);
        const purchaseId = purchaseSelect.value;
        const productId = productSelect.value;
        
        if (!quantity || !productId) {
            return;
        }
        
        // If from purchase, validate against purchase quantity
        if (purchaseId && selectedPurchaseItems[productId]) {
            const item = selectedPurchaseItems[productId];
            const remainingQty = item.quantity - (item.received_quantity || 0);
            
            if (quantity > remainingQty) {
                showQuantityError(`Jumlah melebihi sisa yang belum diterima (${remainingQty} ${item.unit})`);
                return false;
            } else if (quantity === remainingQty) {
                showQuantityWarning(`Ini akan melengkapi penerimaan untuk item ini`);
            }
        }
        
        // Additional validation via AJAX for real-time checking
        if (purchaseId) {
            validateQuantityAjax(productId, quantity, purchaseId);
        }
        
        return true;
    }

    function validateQuantityAjax(productId, quantity, purchaseId) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        formData.append('purchase_id', purchaseId);
        
        fetch('<?= base_url('/incoming-items/validate-quantity') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                showQuantityError(data.message);
            }
        })
        .catch(error => {
            console.error('Validation error:', error);
        });
    }

    function showQuantityWarning(message) {
        quantityWarning.textContent = message;
        quantityWarning.classList.remove('hidden');
        quantityError.classList.add('hidden');
    }

    function showQuantityError(message) {
        quantityError.textContent = message;
        quantityError.classList.remove('hidden');
        quantityWarning.classList.add('hidden');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    function clearQuantityMessages() {
        quantityWarning.classList.add('hidden');
        quantityError.classList.add('hidden');
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    // Form submission validation
    document.getElementById('incomingForm').addEventListener('submit', function(e) {
        if (!validateQuantity()) {
            e.preventDefault();
            alert('Mohon perbaiki kesalahan pada form sebelum menyimpan.');
        }
    });
});
</script>
<?= $this->endSection() ?>