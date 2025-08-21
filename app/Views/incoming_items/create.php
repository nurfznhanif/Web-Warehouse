<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tambah Barang Masuk</h1>
                <p class="mt-2 text-gray-600">Catat penerimaan barang dari pembelian</p>
            </div>
            <a href="<?= base_url('/incoming-items') ?>"
                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Alert untuk validation errors -->
    <?php if (session()->getFlashdata('validation')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <h4 class="font-bold">Terdapat kesalahan:</h4>
            <ul class="mt-2">
                <?php foreach (session()->getFlashdata('validation')->getErrors() as $error): ?>
                    <li>• <?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Alert untuk error messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="<?= base_url('/incoming-items/store') ?>" method="POST" id="incomingForm">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Purchase Selection (WAJIB) -->
                <div class="md:col-span-2">
                    <label for="purchase_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Pembelian <span class="text-red-500">*</span>
                    </label>
                    <select id="purchase_id" name="purchase_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= session('errors.purchase_id') ? 'border-red-500' : '' ?>"
                        required onchange="loadPurchaseProducts()">
                        <option value="">Pilih Pembelian</option>
                        <?php foreach ($purchases as $purchase): ?>
                            <option value="<?= $purchase['id'] ?>" <?= old('purchase_id') == $purchase['id'] ? 'selected' : '' ?>>
                                PO-<?= str_pad($purchase['id'], 6, '0', STR_PAD_LEFT) ?> - <?= esc($purchase['vendor_name']) ?>
                                (<?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (session('errors.purchase_id')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= session('errors.purchase_id') ?></p>
                    <?php endif; ?>
                    <p class="text-gray-500 text-sm mt-1">Pilih pembelian terlebih dahulu untuk memuat produk yang tersedia</p>
                </div>

                <!-- Product Selection (Akan dimuat berdasarkan purchase) -->
                <div class="md:col-span-2">
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Produk <span class="text-red-500">*</span>
                    </label>
                    <select id="product_id" name="product_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= session('errors.product_id') ? 'border-red-500' : '' ?>"
                        required disabled onchange="updateQuantityInfo()">
                        <option value="">Pilih pembelian terlebih dahulu</option>
                    </select>
                    <?php if (session('errors.product_id')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= session('errors.product_id') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Quantity Info Panel -->
                <div class="md:col-span-2" id="quantityInfoPanel" style="display: none;">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-medium text-blue-800 mb-2">Informasi Kuantitas</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Jumlah Dibeli:</span>
                                <span id="purchasedQty" class="font-medium text-blue-800">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Sudah Diterima:</span>
                                <span id="receivedQty" class="font-medium text-green-600">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Sisa Belum Diterima:</span>
                                <span id="remainingQty" class="font-medium text-orange-600">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date" name="date"
                        value="<?= old('date', date('Y-m-d')) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= session('errors.date') ? 'border-red-500' : '' ?>"
                        required>
                    <?php if (session('errors.date')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= session('errors.date') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="quantity" name="quantity"
                        value="<?= old('quantity') ?>"
                        step="0.01" min="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= session('errors.quantity') ? 'border-red-500' : '' ?>"
                        required disabled readonly>
                    <?php if (session('errors.quantity')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= session('errors.quantity') ?></p>
                    <?php endif; ?>
                    <p class="text-gray-500 text-sm mt-1">Jumlah otomatis sesuai sisa yang belum diterima</p>
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= session('errors.notes') ? 'border-red-500' : '' ?>"
                        placeholder="Catatan tambahan (opsional)"><?= old('notes') ?></textarea>
                    <?php if (session('errors.notes')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= session('errors.notes') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 mt-6">
                <div class="text-sm text-gray-600">
                    <span class="text-red-500">*</span> Field wajib diisi
                </div>
                <div class="flex space-x-3">
                    <a href="<?= base_url('/incoming-items') ?>"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        <i class="fas fa-save mr-2"></i>Simpan Barang Masuk
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let purchaseProducts = {};
    let currentPurchaseData = {};

    // Load products when purchase is selected
    async function loadPurchaseProducts() {
        const purchaseId = document.getElementById('purchase_id').value;
        const productSelect = document.getElementById('product_id');
        const quantityInput = document.getElementById('quantity');
        const submitBtn = document.getElementById('submitBtn');

        // Reset form
        productSelect.innerHTML = '<option value="">Loading...</option>';
        productSelect.disabled = true;
        quantityInput.disabled = true;
        quantityInput.value = '';
        submitBtn.disabled = true;
        document.getElementById('quantityInfoPanel').style.display = 'none';

        if (!purchaseId) {
            productSelect.innerHTML = '<option value="">Pilih pembelian terlebih dahulu</option>';
            return;
        }

        try {
            // PERBAIKAN: Tambah headers yang diperlukan untuk AJAX
            const response = await fetch(`<?= base_url('/incoming-items/get-purchase-items/') ?>${purchaseId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                purchaseProducts = {};
                productSelect.innerHTML = '<option value="">Pilih Produk</option>';

                data.items.forEach(item => {
                    purchaseProducts[item.product_id] = item;
                    const option = document.createElement('option');
                    option.value = item.product_id;
                    option.textContent = `${item.product_code} - ${item.product_name} (${item.remaining_quantity} ${item.unit} tersisa)`;

                    // Disable jika sudah fully received
                    if (item.remaining_quantity <= 0) {
                        option.disabled = true;
                        option.textContent += ' - SUDAH LENGKAP';
                    }

                    productSelect.appendChild(option);
                });

                productSelect.disabled = false;
            } else {
                productSelect.innerHTML = '<option value="">Tidak ada produk tersedia</option>';
                alert(data.message || 'Gagal memuat produk');
            }
        } catch (error) {
            console.error('Error:', error);
            productSelect.innerHTML = '<option value="">Error memuat produk</option>';
            alert('Terjadi kesalahan saat memuat produk');
        }
    }

    // Update quantity info when product is selected
    function updateQuantityInfo() {
        const productId = document.getElementById('product_id').value;
        const quantityInput = document.getElementById('quantity');
        const submitBtn = document.getElementById('submitBtn');
        const panel = document.getElementById('quantityInfoPanel');

        if (!productId || !purchaseProducts[productId]) {
            panel.style.display = 'none';
            quantityInput.disabled = true;
            quantityInput.value = '';
            submitBtn.disabled = true;
            return;
        }

        const productData = purchaseProducts[productId];

        // Update info panel
        document.getElementById('purchasedQty').textContent = `${productData.quantity} ${productData.unit}`;
        document.getElementById('receivedQty').textContent = `${productData.received_quantity} ${productData.unit}`;
        document.getElementById('remainingQty').textContent = `${productData.remaining_quantity} ${productData.unit}`;

        panel.style.display = 'block';

        // AUTO-SET quantity sama dengan remaining quantity
        quantityInput.value = productData.remaining_quantity;
        quantityInput.disabled = false; // Enable tapi readonly

        // Enable submit button karena quantity sudah auto-set
        submitBtn.disabled = false;
    }

    // HAPUS function validateQuantity() karena tidak perlu lagi - quantity fixed

    // Form validation before submit
    document.getElementById('incomingForm').addEventListener('submit', function(e) {
        const purchaseId = document.getElementById('purchase_id').value;
        const productId = document.getElementById('product_id').value;
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;

        if (!purchaseId) {
            alert('Pembelian harus dipilih');
            e.preventDefault();
            return;
        }

        if (!productId) {
            alert('Produk harus dipilih');
            e.preventDefault();
            return;
        }

        if (!purchaseProducts[productId]) {
            alert('Data produk tidak valid');
            e.preventDefault();
            return;
        }

        if (quantity <= 0) {
            alert('Jumlah harus lebih dari 0');
            e.preventDefault();
            return;
        }

        // Show loading state
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    });

    // Initialize form
    document.addEventListener('DOMContentLoaded', function() {
        const purchaseId = document.getElementById('purchase_id').value;
        if (purchaseId) {
            loadPurchaseProducts();
        }
    });
</script>

<?= $this->endSection() ?>