<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Terima Barang dari Pembelian</h1>
            <p class="mt-2 text-gray-600">Proses penerimaan barang berdasarkan pesanan pembelian</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= base_url('/purchases') ?>"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Pembelian
            </a>
            <a href="<?= base_url('/purchases/view/' . $purchase['id']) ?>"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-eye mr-2"></i>
                Lihat Detail Pembelian
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= session()->getFlashdata('warning') ?>
        </div>
    <?php endif; ?>

    <!-- Purchase Information -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Pembelian</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Pembelian</label>
                    <p class="text-xl font-bold text-blue-600">#<?= str_pad($purchase['id'], 6, '0', STR_PAD_LEFT) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Vendor</label>
                    <p class="text-lg font-semibold text-gray-900"><?= esc($purchase['vendor_name']) ?></p>
                    <p class="text-sm text-gray-600"><?= esc($purchase['vendor_address']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Pembelian</label>
                    <p class="text-lg font-semibold text-gray-900"><?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></p>
                    <p class="text-sm text-gray-600">Pembeli: <?= esc($purchase['buyer_name']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full 
                        <?= $purchase['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($purchase['status'] === 'received' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') ?>">
                        <?= ucfirst($purchase['status']) ?>
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Pembelian</label>
                    <p class="text-lg font-bold text-gray-900">Rp <?= number_format($purchase['total_amount'], 0, ',', '.') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Jumlah Item</label>
                    <p class="text-lg font-semibold text-gray-900"><?= count($unreceived_items) ?> produk</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Receiving Form -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Barang yang Belum Diterima</h3>
            <p class="text-sm text-gray-600 mt-1">Pilih produk yang akan diterima dan masukkan jumlahnya</p>
        </div>

        <?php if (empty($unreceived_items)): ?>
            <div class="text-center py-12">
                <i class="fas fa-check-circle text-green-400 text-5xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Semua Barang Sudah Diterima</h3>
                <p class="text-gray-600 mb-4">Semua produk dalam pembelian ini sudah diterima lengkap.</p>
                <a href="<?= base_url('/purchases/view/' . $purchase['id']) ?>"
                    class="inline-flex px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-eye mr-2"></i>
                    Lihat Detail Pembelian
                </a>
            </div>
        <?php else: ?>
            <form id="receiveForm" action="<?= base_url('/incoming-items/bulk-receive') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="purchase_id" value="<?= $purchase['id'] ?>">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Produk
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dipesan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sudah Diterima
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sisa
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah Terima
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Harga Satuan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($unreceived_items as $index => $item): ?>
                                <?php
                                $remainingQty = $item['quantity'] - ($item['received_quantity'] ?? 0);
                                ?>
                                <tr class="hover:bg-gray-50 item-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                            name="items[<?= $index ?>][selected]"
                                            value="1"
                                            data-product-id="<?= $item['product_id'] ?>"
                                            data-remaining="<?= $remainingQty ?>"
                                            class="item-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <input type="hidden" name="items[<?= $index ?>][product_id]" value="<?= $item['product_id'] ?>">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 rounded-full p-2 mr-3">
                                                <i class="fas fa-box text-blue-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= esc($item['product_name']) ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?= esc($item['product_code']) ?> • <?= esc($item['category_name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= number_format($item['quantity'], 0) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= esc($item['unit']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-green-600">
                                            <?= number_format($item['received_quantity'] ?? 0, 0) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= esc($item['unit']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-orange-600">
                                            <?= number_format($remainingQty, 0) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= esc($item['unit']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <input type="number"
                                                name="items[<?= $index ?>][quantity]"
                                                min="1"
                                                max="<?= $remainingQty ?>"
                                                value="<?= $remainingQty ?>"
                                                disabled
                                                class="quantity-input w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <span class="text-sm text-gray-500"><?= esc($item['unit']) ?></span>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            Max: <?= number_format($remainingQty, 0) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            per <?= esc($item['unit']) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div id="selectionSummary" class="text-sm text-gray-600">
                            Belum ada item yang dipilih
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" id="selectAllBtn"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500">
                                <i class="fas fa-check-square mr-2"></i>
                                Pilih Semua
                            </button>
                            <button type="submit" id="submitBtn" disabled
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                <i class="fas fa-check mr-2"></i>
                                Terima Barang Terpilih
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const selectAllBtn = document.getElementById('selectAllBtn');
        const submitBtn = document.getElementById('submitBtn');
        const selectionSummary = document.getElementById('selectionSummary');
        const form = document.getElementById('receiveForm');

        let itemCheckboxes = document.querySelectorAll('.item-checkbox');
        let quantityInputs = document.querySelectorAll('.quantity-input');

        // Select All functionality
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                toggleQuantityInput(checkbox);
            });
            updateSummary();
        });

        // Individual checkbox functionality
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleQuantityInput(this);
                updateSelectAllState();
                updateSummary();
            });
        });

        // Quantity input validation
        quantityInputs.forEach(input => {
            input.addEventListener('input', function() {
                const max = parseFloat(this.getAttribute('max'));
                const value = parseFloat(this.value);

                if (value > max) {
                    this.value = max;
                }
                if (value < 1) {
                    this.value = 1;
                }

                updateSummary();
            });
        });

        function toggleQuantityInput(checkbox) {
            const row = checkbox.closest('.item-row');
            const quantityInput = row.querySelector('.quantity-input');

            if (checkbox.checked) {
                quantityInput.disabled = false;
                quantityInput.focus();
            } else {
                quantityInput.disabled = true;
                // Reset to max value when unchecked
                quantityInput.value = quantityInput.getAttribute('max');
            }
        }

        function updateSelectAllState() {
            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            const totalCount = itemCheckboxes.length;

            selectAllCheckbox.checked = checkedCount === totalCount;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }

        function updateSummary() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            const count = checkedItems.length;

            let totalQuantity = 0;
            let totalValue = 0;

            checkedItems.forEach(checkbox => {
                const row = checkbox.closest('.item-row');
                const quantityInput = row.querySelector('.quantity-input');
                const quantity = parseFloat(quantityInput.value) || 0;
                const price = parseFloat(row.querySelector('td:nth-child(7) .text-sm').textContent.replace(/[^\d]/g, '')) || 0;

                totalQuantity += quantity;
                totalValue += quantity * price;
            });

            if (count === 0) {
                selectionSummary.textContent = 'Belum ada item yang dipilih';
                submitBtn.disabled = true;
            } else {
                selectionSummary.innerHTML = `
                <strong>${count}</strong> item dipilih | 
                Total kuantitas: <strong>${totalQuantity}</strong> | 
                Estimasi nilai: <strong>Rp ${totalValue.toLocaleString('id-ID')}</strong>
            `;
                submitBtn.disabled = false;
            }
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');

            if (checkedItems.length === 0) {
                e.preventDefault();
                alert('Pilih minimal satu item untuk diterima');
                return;
            }

            // Confirm submission
            const count = checkedItems.length;
            if (!confirm(`Apakah Anda yakin ingin menerima ${count} item yang dipilih?`)) {
                e.preventDefault();
                return;
            }

            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        });

        // Initialize summary
        updateSummary();
    });
</script>
<?= $this->endSection() ?>