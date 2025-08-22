<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Pembelian #<?= str_pad($purchase['id'], 4, '0', STR_PAD_LEFT) ?></h1>
                <p class="text-gray-600 mt-1">Perbarui informasi pesanan pembelian</p>
            </div>
            <a href="<?= base_url('/purchases/view/' . $purchase['id']) ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= base_url('/purchases/update/' . $purchase['id']) ?>" method="POST" id="purchaseForm" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Info Pembelian -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembelian</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vendor -->
                <div>
                    <label for="vendor_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Vendor <span class="text-red-500">*</span>
                    </label>
                    <select name="vendor_id" id="vendor_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Pilih Vendor</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?= $vendor['id'] ?>" <?= ($purchase['vendor_id'] == $vendor['id']) ? 'selected' : '' ?>>
                                <?= esc($vendor['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($validation) && $validation->hasError('vendor_id')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $validation->getError('vendor_id') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Tanggal Pembelian -->
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Pembelian <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="purchase_date" id="purchase_date" required
                        value="<?= $purchase['purchase_date'] ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (isset($validation) && $validation->hasError('purchase_date')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $validation->getError('purchase_date') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Nama Pembeli -->
                <div class="md:col-span-2">
                    <label for="buyer_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Pembeli <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="buyer_name" id="buyer_name" required
                        value="<?= esc($purchase['buyer_name']) ?>" placeholder="Masukkan nama pembeli"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (isset($validation) && $validation->hasError('buyer_name')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $validation->getError('buyer_name') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Item Pembelian -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Item Pembelian</h2>
                <button type="button" id="addItem"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-2 font-medium text-gray-700">Produk</th>
                            <th class="text-left py-3 px-2 font-medium text-gray-700">Kuantitas</th>
                            <th class="text-left py-3 px-2 font-medium text-gray-700">Harga</th>
                            <th class="text-left py-3 px-2 font-medium text-gray-700">Subtotal</th>
                            <th class="text-center py-3 px-2 font-medium text-gray-700 w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTable">
                        <!-- Items akan ditambahkan di sini via JavaScript -->
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 bg-gray-50">
                            <td colspan="3" class="py-3 px-2 text-right font-medium text-gray-700">Total:</td>
                            <td class="py-3 px-2 font-bold text-lg text-gray-900" id="grandTotal">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="noItems" class="text-center py-8 text-gray-500" style="display: none;">
                <i class="fas fa-box-open text-3xl mb-2"></i>
                <p>Belum ada item pembelian. Klik "Tambah Item" untuk menambahkan produk.</p>
            </div>
        </div>

        <!-- Tombol Submit -->
        <div class="flex justify-end space-x-4">
            <a href="<?= base_url('/purchases/view/' . $purchase['id']) ?>"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg transition-colors">
                <i class="fas fa-save mr-2"></i>Perbarui Pembelian
            </button>
        </div>
    </form>
</div>

<!-- Template untuk item row -->
<template id="itemRowTemplate">
    <tr class="item-row border-b border-gray-100">
        <td class="py-3 px-2">
            <select name="product_id[]" class="product-select w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                <option value="">Pilih Produk</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>" data-name="<?= esc($product['name']) ?>" data-unit="<?= esc($product['unit']) ?>">
                        <?= esc($product['code']) ?> - <?= esc($product['name']) ?> (<?= esc($product['unit']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="py-3 px-2">
            <input type="number" name="quantity[]" placeholder="0" min="1" step="1" required
                class="quantity-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </td>
        <td class="py-3 px-2">
            <input type="number" name="price[]" placeholder="0" min="1" step="1" required
                class="price-input w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </td>
        <td class="py-3 px-2">
            <span class="subtotal font-medium">Rp 0</span>
        </td>
        <td class="py-3 px-2 text-center">
            <button type="button" class="remove-item text-red-600 hover:text-red-800 p-1">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addItemBtn = document.getElementById('addItem');
        const itemsTable = document.getElementById('itemsTable');
        const noItems = document.getElementById('noItems');
        const submitBtn = document.getElementById('submitBtn');
        const grandTotal = document.getElementById('grandTotal');
        let itemCount = 0;

        // Load existing items
        const existingItems = <?= json_encode($items ?? []) ?>;

        if (existingItems.length > 0) {
            existingItems.forEach(item => {
                addItem(item);
            });
        } else {
            addItem(); // Add one empty item
        }

        addItemBtn.addEventListener('click', () => addItem());

        function addItem(itemData = null) {
            const template = document.getElementById('itemRowTemplate');
            const clone = template.content.cloneNode(true);

            if (itemData) {
                // Fill with existing data - format angka tanpa desimal
                clone.querySelector('.product-select').value = itemData.product_id;
                clone.querySelector('.quantity-input').value = parseFloat(itemData.quantity);
                clone.querySelector('.price-input').value = parseFloat(itemData.price);
                clone.querySelector('.subtotal').textContent = formatCurrency(itemData.total);
            }

            itemsTable.appendChild(clone);
            itemCount++;

            updateDisplay();
            bindItemEvents();
            calculateTotal();
        }

        function bindItemEvents() {
            // Event untuk remove item
            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.onclick = function() {
                    if (itemCount > 1) {
                        this.closest('.item-row').remove();
                        itemCount--;
                        updateDisplay();
                        calculateTotal();
                    }
                };
            });

            // Event untuk kalkulasi
            document.querySelectorAll('.quantity-input, .price-input').forEach(input => {
                input.addEventListener('input', calculateRowTotal);
            });
        }

        function calculateRowTotal() {
            const row = this.closest('.item-row');
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = quantity * price;

            row.querySelector('.subtotal').textContent = formatCurrency(subtotal);
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                total += quantity * price;
            });

            grandTotal.textContent = formatCurrency(total);

            // Enable/disable submit button
            submitBtn.disabled = total <= 0 || !isFormValid();
        }

        function isFormValid() {
            const vendorId = document.getElementById('vendor_id').value;
            const buyerName = document.getElementById('buyer_name').value;
            const purchaseDate = document.getElementById('purchase_date').value;

            if (!vendorId || !buyerName || !purchaseDate) {
                return false;
            }

            // Check if at least one item is filled
            let hasValidItem = false;
            document.querySelectorAll('.item-row').forEach(row => {
                const productId = row.querySelector('.product-select').value;
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;

                if (productId && quantity > 0 && price > 0) {
                    hasValidItem = true;
                }
            });

            return hasValidItem;
        }

        function updateDisplay() {
            if (itemCount > 0) {
                noItems.style.display = 'none';
                itemsTable.parentElement.parentElement.style.display = 'block';
            } else {
                noItems.style.display = 'block';
                itemsTable.parentElement.parentElement.style.display = 'none';
            }
        }

        function formatCurrency(amount) {
            // Format currency tanpa desimal jika bilangan bulat
            if (Math.floor(amount) === amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(amount);
            } else {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                }).format(amount);
            }
        }

        // Event listeners untuk form validation
        document.getElementById('vendor_id').addEventListener('change', calculateTotal);
        document.getElementById('buyer_name').addEventListener('input', calculateTotal);
        document.getElementById('purchase_date').addEventListener('change', calculateTotal);

        // Prevent double submission
        document.getElementById('purchaseForm').addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memperbarui...';
        });
    });
</script>
<?= $this->endSection() ?>