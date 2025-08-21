<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="bg-white shadow-sm border-b border-gray-200 mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Pembelian</h1>
                    <p class="mt-1 text-sm text-gray-500">Update data pembelian dari vendor <?= esc($purchase['vendor_name']) ?></p>
                </div>
                <a href="<?= base_url('/purchases') ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if (session()->getFlashdata('errors')): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-1"></i>
            <div>
                <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan:</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Form Edit Pembelian -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="<?= base_url('/purchases/update/' . $purchase['id']) ?>" method="POST" id="editPurchaseForm">
        <?= csrf_field() ?>
        
        <!-- Informasi Vendor -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-truck mr-2"></i>Informasi Vendor
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="vendor_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Vendor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="vendor_name" 
                           name="vendor_name" 
                           value="<?= old('vendor_name', $purchase['vendor_name']) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukkan nama vendor"
                           required>
                </div>
                
                <div>
                    <label for="vendor_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon Vendor
                    </label>
                    <input type="tel" 
                           id="vendor_phone" 
                           name="vendor_phone" 
                           value="<?= old('vendor_phone', $purchase['vendor_phone'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: 08123456789">
                </div>
                
                <div class="md:col-span-2">
                    <label for="vendor_address" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat Vendor <span class="text-red-500">*</span>
                    </label>
                    <textarea id="vendor_address" 
                              name="vendor_address" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Masukkan alamat lengkap vendor"
                              required><?= old('vendor_address', $purchase['vendor_address']) ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Informasi Pembelian -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-shopping-cart mr-2"></i>Informasi Pembelian
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Pembelian <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="purchase_date" 
                           name="purchase_date" 
                           value="<?= old('purchase_date', date('Y-m-d', strtotime($purchase['purchase_date']))) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
                
                <div>
                    <label for="buyer_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Pembeli <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="buyer_name" 
                           name="buyer_name" 
                           value="<?= old('buyer_name', $purchase['buyer_name']) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Nama person yang melakukan pembelian"
                           required>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status Pembelian
                    </label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="pending" <?= ($purchase['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="received" <?= ($purchase['status'] ?? '') === 'received' ? 'selected' : '' ?>>Diterima</option>
                        <option value="cancelled" <?= ($purchase['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
                
                <div class="md:col-span-3">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Pembelian
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Catatan tambahan untuk pembelian ini"><?= old('notes', $purchase['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Detail Barang -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-boxes mr-2"></i>Detail Barang yang Dibeli
                </h3>
                <button type="button" 
                        onclick="addItem()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm flex items-center transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Item
                </button>
            </div>
            
            <div id="items-container">
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $index => $item): ?>
                        <div class="item-row bg-white rounded-lg border border-gray-200 p-4 mb-4">
                            <input type="hidden" name="item_id[]" value="<?= $item['id'] ?? '' ?>">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-gray-900">Item #<?= $index + 1 ?></h4>
                                <button type="button" 
                                        onclick="removeItem(this)" 
                                        class="text-red-600 hover:text-red-800 transition-colors"
                                        <?= count($items) <= 1 ? 'style="display: none;"' : '' ?>>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Barang <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="product_name[]" 
                                           value="<?= esc($item['product_name'] ?? '') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Nama barang yang dibeli"
                                           required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Jumlah <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" 
                                           name="quantity[]" 
                                           value="<?= esc($item['quantity'] ?? '') ?>"
                                           min="1"
                                           step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0"
                                           required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Harga Satuan
                                    </label>
                                    <input type="number" 
                                           name="unit_price[]" 
                                           value="<?= esc($item['unit_price'] ?? '') ?>"
                                           min="0"
                                           step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0"
                                           onchange="calculateItemTotal(this)">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Spesifikasi/Keterangan
                                    </label>
                                    <input type="text" 
                                           name="specification[]" 
                                           value="<?= esc($item['specification'] ?? '') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Spesifikasi atau keterangan tambahan">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Satuan
                                    </label>
                                    <select name="unit[]" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="pcs" <?= ($item['unit'] ?? '') === 'pcs' ? 'selected' : '' ?>>Pcs</option>
                                        <option value="kg" <?= ($item['unit'] ?? '') === 'kg' ? 'selected' : '' ?>>Kg</option>
                                        <option value="liter" <?= ($item['unit'] ?? '') === 'liter' ? 'selected' : '' ?>>Liter</option>
                                        <option value="meter" <?= ($item['unit'] ?? '') === 'meter' ? 'selected' : '' ?>>Meter</option>
                                        <option value="set" <?= ($item['unit'] ?? '') === 'set' ? 'selected' : '' ?>>Set</option>
                                        <option value="box" <?= ($item['unit'] ?? '') === 'box' ? 'selected' : '' ?>>Box</option>
                                        <option value="roll" <?= ($item['unit'] ?? '') === 'roll' ? 'selected' : '' ?>>Roll</option>
                                        <option value="pack" <?= ($item['unit'] ?? '') === 'pack' ? 'selected' : '' ?>>Pack</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Total Harga
                                    </label>
                                    <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900 font-medium item-total">
                                        Rp <?= number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Item kosong jika tidak ada data -->
                    <div class="item-row bg-white rounded-lg border border-gray-200 p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">Item #1</h4>
                            <button type="button" 
                                    onclick="removeItem(this)" 
                                    class="text-red-600 hover:text-red-800 transition-colors"
                                    style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Barang <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="product_name[]" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Nama barang yang dibeli"
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Jumlah <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="quantity[]" 
                                       min="1"
                                       step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0"
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga Satuan
                                </label>
                                <input type="number" 
                                       name="unit_price[]" 
                                       min="0"
                                       step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0"
                                       onchange="calculateItemTotal(this)">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Spesifikasi/Keterangan
                                </label>
                                <input type="text" 
                                       name="specification[]" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Spesifikasi atau keterangan tambahan">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Satuan
                                </label>
                                <select name="unit[]" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <option value="pcs">Pcs</option>
                                    <option value="kg">Kg</option>
                                    <option value="liter">Liter</option>
                                    <option value="meter">Meter</option>
                                    <option value="set">Set</option>
                                    <option value="box">Box</option>
                                    <option value="roll">Roll</option>
                                    <option value="pack">Pack</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Total Harga
                                </label>
                                <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900 font-medium item-total">
                                    Rp 0
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Total Keseluruhan -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 mt-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-lg font-semibold text-gray-900">Total Keseluruhan:</h4>
                    <div id="grand-total" class="text-2xl font-bold text-blue-600">
                        Rp <?php
                        $total = 0;
                        if (!empty($items)) {
                            foreach ($items as $item) {
                                $total += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                            }
                        }
                        echo number_format($total, 0, ',', '.');
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-600">
                <span class="text-red-500">*</span> Field wajib diisi
            </div>
            <div class="flex space-x-3">
                <a href="<?= base_url('/purchases') ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Pembelian
                </button>
            </div>
        </div>
    </form>
</div>


<script>
let itemCount = <?= !empty($items) ? count($items) : 1 ?>;

function addItem() {
    itemCount++;
    
    const template = `
        <div class="item-row bg-white rounded-lg border border-gray-200 p-4 mb-4">
            <input type="hidden" name="item_id[]" value="">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Item #${itemCount}</h4>
                <button type="button" 
                        onclick="removeItem(this)" 
                        class="text-red-600 hover:text-red-800 transition-colors">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Barang <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="product_name[]" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Nama barang yang dibeli"
                           required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="quantity[]" 
                           min="1"
                           step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0"
                           required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Harga Satuan
                    </label>
                    <input type="number" 
                           name="unit_price[]" 
                           min="0"
                           step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0"
                           onchange="calculateItemTotal(this)">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Spesifikasi/Keterangan
                    </label>
                    <input type="text" 
                           name="specification[]" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Spesifikasi atau keterangan tambahan">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Satuan
                    </label>
                    <select name="unit[]" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="pcs">Pcs</option>
                        <option value="kg">Kg</option>
                        <option value="liter">Liter</option>
                        <option value="meter">Meter</option>
                        <option value="set">Set</option>
                        <option value="box">Box</option>
                        <option value="roll">Roll</option>
                        <option value="pack">Pack</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Total Harga
                    </label>
                    <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900 font-medium item-total">
                        Rp 0
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('items-container').insertAdjacentHTML('beforeend', template);
    updateRemoveButtons();
}

function removeItem(button) {
    const itemRow = button.closest('.item-row');
    itemRow.remove();
    updateItemNumbers();
    updateRemoveButtons();
    calculateGrandTotal();
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
    const removeButtons = document.querySelectorAll('.item-row button[onclick*="removeItem"]');
    
    removeButtons.forEach(button => {
        button.style.display = items.length > 1 ? 'block' : 'none';
    });
}

function calculateItemTotal(input) {
    const row = input.closest('.item-row');
    const quantity = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
    const unitPrice = parseFloat(input.value) || 0;
    const total = quantity * unitPrice;
    
    const totalElement = row.querySelector('.item-total');
    totalElement.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    
    calculateGrandTotal();
}

function calculateGrandTotal() {
    const rows = document.querySelectorAll('.item-row');
    let grandTotal = 0;
    
    rows.forEach(row => {
        const quantity = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
        const unitPrice = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
        grandTotal += quantity * unitPrice;
    });
    
    document.getElementById('grand-total').textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
}

// Event listeners untuk perhitungan otomatis
document.addEventListener('change', function(e) {
    if (e.target.name === 'quantity[]' || e.target.name === 'unit_price[]') {
        calculateItemTotal(e.target);
    }
});

// Validasi form sebelum submit
document.getElementById('editPurchaseForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.item-row');
    let hasValidItem = false;
    
    items.forEach(item => {
        const productName = item.querySelector('input[name="product_name[]"]').value.trim();
        const quantity = item.querySelector('input[name="quantity[]"]').value;
        
        if (productName && quantity && parseFloat(quantity) > 0) {
            hasValidItem = true;
        }
    });
    
    if (!hasValidItem) {
        e.preventDefault();
        alert('Minimal harus ada satu item dengan nama barang dan jumlah yang valid!');
        return false;
    }
});

// Initialize saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
    calculateGrandTotal();
});
</script>
<?= $this->endSection() ?>