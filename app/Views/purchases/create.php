<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Pembelian Baru</h1>
        <p class="text-gray-600 mt-1">Catat pembelian barang dari vendor</p>
    </div>
    <a href="<?= base_url('/purchases') ?>" 
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Kembali
    </a>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('errors')): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <div>
                <p class="font-medium">Terjadi kesalahan:</p>
                <ul class="list-disc list-inside mt-1">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="<?= base_url('/purchases/store') ?>" method="POST" class="space-y-6" id="purchaseForm">
        <?= csrf_field() ?>
        
        <!-- Informasi Vendor & Pembelian -->
        <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Vendor & Pembelian</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pilih Vendor -->
                <div>
                    <label for="vendor_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Vendor <span class="text-red-500">*</span>
                    </label>
                    <select id="vendor_id" 
                            name="vendor_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateVendorInfo()"
                            required>
                        <option value="">-- Pilih Vendor --</option>
                        <?php if (isset($vendors) && !empty($vendors)): ?>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= $vendor['id'] ?>" 
                                        data-name="<?= esc($vendor['name']) ?>"
                                        data-address="<?= esc($vendor['address']) ?>"
                                        data-phone="<?= esc($vendor['phone']) ?>"
                                        data-email="<?= esc($vendor['email']) ?>"
                                        <?= old('vendor_id') == $vendor['id'] ? 'selected' : '' ?>>
                                    <?= esc($vendor['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="flex items-center mt-2">
                        <a href="<?= base_url('/vendors/create') ?>" 
                           target="_blank" 
                           class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                            <i class="fas fa-plus mr-1"></i>Tambah Vendor Baru
                        </a>
                    </div>
                </div>

                <!-- Tanggal Pembelian -->
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Pembelian <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="purchase_date" 
                           name="purchase_date" 
                           value="<?= old('purchase_date', date('Y-m-d')) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>

                <!-- Info Vendor (readonly) -->
                <div class="md:col-span-2">
                    <div id="vendor-info" class="bg-gray-50 border border-gray-200 rounded-lg p-4" style="display: none;">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Vendor:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-600">Nama:</span>
                                <span id="vendor-name-display" class="text-gray-800"></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Telepon:</span>
                                <span id="vendor-phone-display" class="text-gray-800"></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Alamat:</span>
                                <span id="vendor-address-display" class="text-gray-800"></span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Email:</span>
                                <span id="vendor-email-display" class="text-gray-800"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nama Pembeli -->
                <div>
                    <label for="buyer_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Pembeli <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="buyer_name" 
                           name="buyer_name" 
                           value="<?= old('buyer_name', session()->get('full_name')) ?>"
                           placeholder="Nama pembeli"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>

                <!-- Catatan (optional) -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="3"
                              placeholder="Catatan tambahan untuk pembelian ini"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"><?= old('notes') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Detail Barang -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Detail Barang</h3>
                <button type="button" 
                        onclick="addItem()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Item
                </button>
            </div>

            <div id="itemsContainer" class="space-y-4">
                <!-- Item pertama -->
                <div class="item-row border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-700">Item #1</h4>
                        <button type="button" 
                                onclick="removeItem(this)" 
                                class="text-red-600 hover:text-red-800 transition-colors"
                                style="display: none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Pilih Produk -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Produk <span class="text-red-500">*</span>
                            </label>
                            <select name="product_id[]" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="">-- Pilih Produk --</option>
                                <?php if (isset($products) && !empty($products)): ?>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>">
                                            <?= esc($product['code']) ?> - <?= esc($product['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Jumlah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="quantity[]" 
                                   placeholder="0"
                                   min="1"
                                   step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info minimal satu item -->
            <p class="text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Minimal harus ada satu item untuk disimpan
            </p>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
            <a href="<?= base_url('/purchases') ?>" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-times mr-2"></i>Batal
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-save mr-2"></i>Simpan Pembelian
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let itemCount = 1;

// Update vendor info when vendor is selected
function updateVendorInfo() {
    const vendorSelect = document.getElementById('vendor_id');
    const selectedOption = vendorSelect.options[vendorSelect.selectedIndex];
    const vendorInfo = document.getElementById('vendor-info');
    
    if (selectedOption.value) {
        // Show vendor info
        document.getElementById('vendor-name-display').textContent = selectedOption.dataset.name || '-';
        document.getElementById('vendor-address-display').textContent = selectedOption.dataset.address || '-';
        document.getElementById('vendor-phone-display').textContent = selectedOption.dataset.phone || '-';
        document.getElementById('vendor-email-display').textContent = selectedOption.dataset.email || '-';
        vendorInfo.style.display = 'block';
    } else {
        // Hide vendor info
        vendorInfo.style.display = 'none';
    }
}

function addItem() {
    itemCount++;
    const container = document.getElementById('itemsContainer');
    const newItem = createItemElement(itemCount);
    container.appendChild(newItem);
    
    // Show remove button for all items if more than 1
    updateRemoveButtons();
}

function removeItem(button) {
    if (document.querySelectorAll('.item-row').length > 1) {
        button.closest('.item-row').remove();
        updateItemNumbers();
        updateRemoveButtons();
    }
}

function createItemElement(number) {
    const div = document.createElement('div');
    div.className = 'item-row border border-gray-200 rounded-lg p-4 bg-gray-50';
    div.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-medium text-gray-700">Item #${number}</h4>
            <button type="button" 
                    onclick="removeItem(this)" 
                    class="text-red-600 hover:text-red-800 transition-colors">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Produk <span class="text-red-500">*</span>
                </label>
                <select name="product_id[]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <option value="">-- Pilih Produk --</option>
                    <?php if (isset($products) && !empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>">
                                <?= esc($product['code']) ?> - <?= esc($product['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Jumlah <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="quantity[]" 
                       placeholder="0"
                       min="1"
                       step="0.01"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>
        </div>
    `;
    return div;
}

function updateItemNumbers() {
    const items = document.querySelectorAll('.item-row');
    items.forEach((item, index) => {
        const header = item.querySelector('h4');
        header.textContent = `Item #${index + 1}`;
    });
    itemCount = items.length;
}

function updateRemoveButtons() {
    const items = document.querySelectorAll('.item-row');
    const removeButtons = document.querySelectorAll('.item-row button[onclick="removeItem(this)"]');
    
    if (items.length > 1) {
        removeButtons.forEach(button => {
            button.style.display = 'block';
        });
    } else {
        removeButtons.forEach(button => {
            button.style.display = 'none';
        });
    }
}

// Form validation
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    const vendorSelect = document.getElementById('vendor_id');
    const productSelects = document.querySelectorAll('select[name="product_id[]"]');
    const quantities = document.querySelectorAll('input[name="quantity[]"]');
    
    // Check vendor selection
    if (!vendorSelect.value) {
        e.preventDefault();
        alert('Silakan pilih vendor terlebih dahulu!');
        vendorSelect.focus();
        return false;
    }
    
    // Check items
    let hasValidItem = false;
    
    for (let i = 0; i < productSelects.length; i++) {
        if (productSelects[i].value && quantities[i].value && parseFloat(quantities[i].value) > 0) {
            hasValidItem = true;
            break;
        }
    }
    
    if (!hasValidItem) {
        e.preventDefault();
        alert('Minimal harus ada satu item dengan produk dan jumlah yang valid!');
        return false;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
    
    // Update vendor info if there's already a selected vendor (for form with errors)
    const vendorSelect = document.getElementById('vendor_id');
    if (vendorSelect.value) {
        updateVendorInfo();
    }
});
</script>
<?= $this->endSection() ?>