<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Terima Barang dari Purchase Order</h1>
        <p class="text-gray-600 mt-1">PO #<?= $purchase_id ?> - <?= esc($purchase['vendor_name']) ?></p>
    </div>
    <a href="<?= base_url('/incoming-items') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
    </a>
</div>

<!-- Purchase Information -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Purchase Order</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Purchase Order</label>
            <p class="text-sm text-gray-900">#<?= $purchase_id ?></p>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Vendor</label>
            <p class="text-sm text-gray-900"><?= esc($purchase['vendor_name']) ?></p>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Tanggal Pembelian</label>
            <p class="text-sm text-gray-900"><?= date('d M Y', strtotime($purchase['purchase_date'])) ?></p>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Pembeli</label>
            <p class="text-sm text-gray-900"><?= esc($purchase['buyer_name']) ?></p>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Status</label>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                <?= $purchase['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($purchase['status'] === 'received' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') ?>">
                <?= ucfirst($purchase['status']) ?>
            </span>
        </div>
        <?php if (!empty($purchase['notes'])): ?>
            <div>
                <label class="text-sm font-medium text-gray-700">Catatan</label>
                <p class="text-sm text-gray-900"><?= esc($purchase['notes']) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Items to Receive -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Item yang Belum Diterima</h3>
            <button id="receiveAllBtn"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-check-double mr-2"></i>
                Terima Semua
            </button>
        </div>
    </div>

    <form id="bulkReceiveForm">
        <?= csrf_field() ?>
        <input type="hidden" name="purchase_id" value="<?= $purchase_id ?>">

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Produk
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity Dibeli
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Sudah Diterima
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Sisa Belum Diterima
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity Diterima
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($unreceived_items)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl mb-2 text-green-400"></i>
                                <p>Semua item sudah diterima lengkap</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($unreceived_items as $index => $item): ?>
                            <tr class="hover:bg-gray-50" data-product-id="<?= $item['product_id'] ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox"
                                        name="items[<?= $index ?>][selected]"
                                        value="1"
                                        class="item-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    <input type="hidden" name="items[<?= $index ?>][product_id]" value="<?= $item['product_id'] ?>">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-box text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= esc($item['product_name']) ?></div>
                                            <div class="text-xs text-gray-500"><?= esc($item['product_code']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= number_format($item['quantity']) ?> <?= esc($item['unit']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    <?= number_format($item['received_quantity'] ?? 0) ?> <?= esc($item['unit']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <?= number_format($item['remaining_quantity']) ?> <?= esc($item['unit']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <input type="number"
                                            name="items[<?= $index ?>][quantity]"
                                            step="0.01"
                                            min="0"
                                            max="<?= $item['remaining_quantity'] ?>"
                                            placeholder="0"
                                            class="quantity-input w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <span class="text-xs text-gray-500"><?= esc($item['unit']) ?></span>
                                        <button type="button"
                                            class="fill-remaining-btn text-xs text-blue-600 hover:text-blue-800 underline"
                                            data-remaining="<?= $item['remaining_quantity'] ?>">
                                            Isi Semua
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button type="button"
                                        class="receive-single-btn text-green-600 hover:text-green-900"
                                        data-product-id="<?= $item['product_id'] ?>"
                                        data-product-name="<?= esc($item['product_name']) ?>"
                                        data-remaining="<?= $item['remaining_quantity'] ?>"
                                        data-unit="<?= esc($item['unit']) ?>">
                                        <i class="fas fa-plus-circle"></i> Terima
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($unreceived_items)): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <span id="selectedCount">0</span> item dipilih untuk diterima
                    </div>
                    <div class="space-x-3">
                        <button type="button"
                            id="clearAllBtn"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm">
                            Clear All
                        </button>
                        <button type="submit"
                            id="bulkReceiveBtn"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-check mr-2"></i>
                            Terima Item Terpilih
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Single Receive Modal -->
<div id="singleReceiveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Terima Item</h3>
            </div>

            <form id="singleReceiveForm" action="<?= base_url('/incoming-items/store') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="purchase_id" value="<?= $purchase_id ?>">
                <input type="hidden" id="modal_product_id" name="product_id">
                <input type="hidden" name="date" value="<?= date('Y-m-d') ?>">

                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Produk</label>
                        <p id="modal_product_name" class="text-sm text-gray-900"></p>
                    </div>

                    <div class="mb-4">
                        <label for="modal_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <div class="flex">
                            <input type="number"
                                id="modal_quantity"
                                name="quantity"
                                step="0.01"
                                min="0.01"
                                required
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <span id="modal_unit" class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md text-gray-700"></span>
                        </div>
                        <p id="modal_remaining_info" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    <div class="mb-4">
                        <label for="modal_notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea id="modal_notes"
                            name="notes"
                            rows="3"
                            placeholder="Catatan penerimaan..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button"
                        onclick="closeSingleReceiveModal()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>
                        Terima Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="messageContainer" class="fixed top-4 right-4 z-50"></div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const quantityInputs = document.querySelectorAll('.quantity-input');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkReceiveBtn = document.getElementById('bulkReceiveBtn');
        const bulkReceiveForm = document.getElementById('bulkReceiveForm');
        const receiveAllBtn = document.getElementById('receiveAllBtn');
        const clearAllBtn = document.getElementById('clearAllBtn');

        // Select All functionality
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                toggleQuantityInput(checkbox);
            });
            updateSelectedCount();
            updateBulkReceiveButton();
        });

        // Individual checkbox handling
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleQuantityInput(this);
                updateSelectedCount();
                updateBulkReceiveButton();
                updateSelectAllState();
            });
        });

        // Quantity input handling
        quantityInputs.forEach(input => {
            input.addEventListener('input', function() {
                const checkbox = this.closest('tr').querySelector('.item-checkbox');
                if (this.value > 0) {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                }
                updateSelectedCount();
                updateBulkReceiveButton();
                updateSelectAllState();
            });
        });

        // Fill remaining buttons
        document.querySelectorAll('.fill-remaining-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const remaining = this.dataset.remaining;
                const row = this.closest('tr');
                const quantityInput = row.querySelector('.quantity-input');
                const checkbox = row.querySelector('.item-checkbox');

                quantityInput.value = remaining;
                checkbox.checked = true;

                updateSelectedCount();
                updateBulkReceiveButton();
                updateSelectAllState();
            });
        });

        // Receive All button
        receiveAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.fill-remaining-btn').forEach(btn => btn.click());
        });

        // Clear All button
        clearAllBtn.addEventListener('click', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                toggleQuantityInput(checkbox);
            });
            selectAllCheckbox.checked = false;
            updateSelectedCount();
            updateBulkReceiveButton();
        });

        // Single receive buttons
        document.querySelectorAll('.receive-single-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                openSingleReceiveModal(
                    this.dataset.productId,
                    this.dataset.productName,
                    this.dataset.remaining,
                    this.dataset.unit
                );
            });
        });

        // Bulk receive form submission
        bulkReceiveForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const selectedItems = [];

            // Collect selected items with quantities
            itemCheckboxes.forEach((checkbox, index) => {
                if (checkbox.checked) {
                    const row = checkbox.closest('tr');
                    const quantityInput = row.querySelector('.quantity-input');
                    const quantity = parseFloat(quantityInput.value) || 0;

                    if (quantity > 0) {
                        selectedItems.push({
                            product_id: formData.get(`items[${index}][product_id]`),
                            quantity: quantity
                        });
                    }
                }
            });

            if (selectedItems.length === 0) {
                showMessage('Pilih minimal satu item dengan quantity > 0', 'error');
                return;
            }

            // Confirm bulk receive
            if (!confirm(`Anda akan menerima ${selectedItems.length} item. Lanjutkan?`)) {
                return;
            }

            // Submit via AJAX
            const submitData = new FormData();
            submitData.append('purchase_id', <?= $purchase_id ?>);
            submitData.append('items', JSON.stringify(selectedItems));

            fetch('<?= base_url('/incoming-items/bulk-receive') ?>', {
                    method: 'POST',
                    body: submitData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Terjadi kesalahan saat memproses permintaan', 'error');
                });
        });

        function toggleQuantityInput(checkbox) {
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('.quantity-input');

            if (checkbox.checked) {
                quantityInput.disabled = false;
                quantityInput.focus();
            } else {
                quantityInput.disabled = true;
                quantityInput.value = '';
            }
        }

        function updateSelectedCount() {
            let count = 0;
            itemCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const row = checkbox.closest('tr');
                    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                    if (quantity > 0) count++;
                }
            });
            selectedCountSpan.textContent = count;
        }

        function updateBulkReceiveButton() {
            let hasValidSelection = false;
            itemCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const row = checkbox.closest('tr');
                    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                    if (quantity > 0) hasValidSelection = true;
                }
            });

            bulkReceiveBtn.disabled = !hasValidSelection;
        }

        function updateSelectAllState() {
            const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
            selectAllCheckbox.checked = checkedCount === itemCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < itemCheckboxes.length;
        }

        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

            const alertDiv = document.createElement('div');
            alertDiv.className = `${alertClass} border px-4 py-3 rounded mb-4`;
            alertDiv.innerHTML = `
            <div class="flex items-center">
                <i class="${iconClass} mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 ${type === 'success' ? 'text-green-700 hover:text-green-900' : 'text-red-700 hover:text-red-900'}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

            messageContainer.appendChild(alertDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Initialize disabled state for quantity inputs
        quantityInputs.forEach(input => {
            input.disabled = true;
        });
    });

    // Single receive modal functions
    function openSingleReceiveModal(productId, productName, remaining, unit) {
        document.getElementById('modal_product_id').value = productId;
        document.getElementById('modal_product_name').textContent = productName;
        document.getElementById('modal_quantity').max = remaining;
        document.getElementById('modal_quantity').value = remaining;
        document.getElementById('modal_unit').textContent = unit;
        document.getElementById('modal_remaining_info').textContent = `Maksimal: ${remaining} ${unit}`;
        document.getElementById('modal_notes').value = '';

        document.getElementById('singleReceiveModal').classList.remove('hidden');
        document.getElementById('modal_quantity').focus();
    }

    function closeSingleReceiveModal() {
        document.getElementById('singleReceiveModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('singleReceiveModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSingleReceiveModal();
        }
    });

    // Handle single receive form submission
    document.getElementById('singleReceiveForm').addEventListener('submit', function(e) {
        const quantity = parseFloat(document.getElementById('modal_quantity').value);
        const maxQuantity = parseFloat(document.getElementById('modal_quantity').max);

        if (quantity > maxQuantity) {
            e.preventDefault();
            alert(`Quantity tidak boleh melebihi ${maxQuantity}`);
            return false;
        }

        if (quantity <= 0) {
            e.preventDefault();
            alert('Quantity harus lebih dari 0');
            return false;
        }
    });
</script>
<?= $this->endSection() ?>