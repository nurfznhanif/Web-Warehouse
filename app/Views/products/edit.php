<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Edit Barang</h1>
        <p class="text-gray-600 mt-1">Perbarui informasi produk: <?= esc($product['name']) ?></p>
    </div>
    <div class="flex space-x-3">
        <a href="<?= base_url('/products/view/' . $product['id']) ?>" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
            <i class="fas fa-eye mr-2"></i>Lihat Detail
        </a>
        <a href="<?= base_url('/products') ?>" 
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Info Card -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
        <div>
            <h3 class="text-sm font-medium text-blue-900">Informasi Penting</h3>
            <p class="text-sm text-blue-700 mt-1">
                • Mengubah kode barang dapat mempengaruhi laporan yang sudah ada<br>
                • Stok saat ini: <strong><?= number_format($product['stock'], 0, ',', '.') ?> <?= esc($product['unit']) ?></strong><br>
                • Untuk mengubah stok, gunakan fitur Barang Masuk/Keluar
            </p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="<?= base_url('/products/update/' . $product['id']) ?>" method="POST" class="space-y-6" onsubmit="showLoading(this.querySelector('button[type=submit]'))">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kode Barang -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Barang <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" 
                           id="code" 
                           name="code" 
                           value="<?= old('code', $product['code']) ?>"
                           placeholder="Contoh: BRG001"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 <?= session('errors.code') ? 'border-red-500' : '' ?>"
                           required>
                    <?php if ($product['code'] !== old('code', $product['code'])): ?>
                        <span class="absolute right-2 top-2 bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">
                            Diubah
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (session('errors.code')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session('errors.code') ?></p>
                <?php endif; ?>
                <p class="text-gray-500 text-sm mt-1">Kode asli: <strong><?= esc($product['code']) ?></strong></p>
            </div>

            <!-- Nama Barang -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= old('name', $product['name']) ?>"
                       placeholder="Nama produk"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 <?= session('errors.name') ? 'border-red-500' : '' ?>"
                       required>
                <?php if (session('errors.name')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session('errors.name') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kategori -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <div class="flex space-x-2">
                    <select id="category_id" 
                            name="category_id" 
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 <?= session('errors.category_id') ? 'border-red-500' : '' ?>"
                            required>
                        <option value="">Pilih Kategori</option>
                        <?php if (isset($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= old('category_id', $product['category_id']) == $category['id'] ? 'selected' : '' ?>>
                                    <?= esc($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (session()->get('role') === 'admin'): ?>
                        <button type="button" 
                                onclick="showAddCategoryModal()" 
                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md transition-colors"
                                title="Tambah Kategori Baru">
                            <i class="fas fa-plus"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (session('errors.category_id')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session('errors.category_id') ?></p>
                <?php endif; ?>
                <p class="text-gray-500 text-sm mt-1">Kategori saat ini: <strong><?= esc($product['category_name'] ?? 'Tidak ada') ?></strong></p>
            </div>

            <!-- Satuan -->
            <div>
                <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                    Satuan <span class="text-red-500">*</span>
                </label>
                <select id="unit" 
                        name="unit" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 <?= session('errors.unit') ? 'border-red-500' : '' ?>"
                        required>
                    <option value="">Pilih Satuan</option>
                    <option value="pcs" <?= old('unit', $product['unit']) == 'pcs' ? 'selected' : '' ?>>Piece (pcs)</option>
                    <option value="kg" <?= old('unit', $product['unit']) == 'kg' ? 'selected' : '' ?>>Kilogram (kg)</option>
                    <option value="liter" <?= old('unit', $product['unit']) == 'liter' ? 'selected' : '' ?>>Liter</option>
                    <option value="meter" <?= old('unit', $product['unit']) == 'meter' ? 'selected' : '' ?>>Meter (m)</option>
                    <option value="box" <?= old('unit', $product['unit']) == 'box' ? 'selected' : '' ?>>Box</option>
                    <option value="pack" <?= old('unit', $product['unit']) == 'pack' ? 'selected' : '' ?>>Pack</option>
                    <option value="roll" <?= old('unit', $product['unit']) == 'roll' ? 'selected' : '' ?>>Roll</option>
                    <option value="set" <?= old('unit', $product['unit']) == 'set' ? 'selected' : '' ?>>Set</option>
                    <option value="unit" <?= old('unit', $product['unit']) == 'unit' ? 'selected' : '' ?>>Unit</option>
                </select>
                <?php if (session('errors.unit')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session('errors.unit') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Informasi Stok (Read Only) -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Informasi Stok</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Stok Saat Ini</label>
                    <div class="text-lg font-bold text-gray-900">
                        <?= number_format($product['stock'], 0, ',', '.') ?> <?= esc($product['unit']) ?>
                    </div>
                </div>
                <div>
                    <label for="min_stock" class="block text-sm font-medium text-gray-600 mb-1">Minimum Stok</label>
                    <input type="number" 
                           id="min_stock" 
                           name="min_stock" 
                           value="<?= old('min_stock', $product['min_stock'] ?? 10) ?>"
                           min="0"
                           step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Status Stok</label>
                    <?php 
                    $stockStatus = 'Stok Aman';
                    $stockClass = 'text-green-600 bg-green-100';
                    $minStock = $product['min_stock'] ?? 10;
                    
                    if ($product['stock'] <= 0) {
                        $stockStatus = 'Habis';
                        $stockClass = 'text-red-600 bg-red-100';
                    } elseif ($product['stock'] <= $minStock) {
                        $stockStatus = 'Stok Rendah';
                        $stockClass = 'text-yellow-600 bg-yellow-100';
                    }
                    ?>
                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-sm font-medium <?= $stockClass ?>">
                        <?= $stockStatus ?>
                    </span>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                Untuk mengubah stok, gunakan menu "Barang Masuk" atau "Barang Keluar"
            </p>
        </div>

        <!-- Informasi Audit -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Informasi Audit</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                    <strong>Dibuat:</strong> <?= date('d M Y H:i', strtotime($product['created_at'])) ?>
                </div>
                <?php if (isset($product['updated_at']) && $product['updated_at']): ?>
                <div>
                    <strong>Terakhir diubah:</strong> <?= date('d M Y H:i', strtotime($product['updated_at'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol Submit -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-600">
                <span class="text-red-500">*</span> Field wajib diisi
            </div>
            <div class="flex space-x-3">
                <a href="<?= base_url('/products') ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Barang
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal Tambah Kategori -->
<?php if (session()->get('role') === 'admin'): ?>
<div id="addCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tambah Kategori Baru</h3>
                <button onclick="hideAddCategoryModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form onsubmit="addCategory(event)">
                <div class="mb-4">
                    <label for="new_category_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Kategori
                    </label>
                    <input type="text" 
                           id="new_category_name" 
                           name="name"
                           placeholder="Nama kategori baru"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="hideAddCategoryModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Track original values for change detection
    const originalValues = {
        code: <?= json_encode($product['code']) ?>,
        name: <?= json_encode($product['name']) ?>,
        category_id: <?= json_encode($product['category_id']) ?>,
        unit: <?= json_encode($product['unit']) ?>,
        min_stock: <?= json_encode($product['min_stock'] ?? 10) ?>
    };

    // Modal functions
    window.showAddCategoryModal = function() {
        document.getElementById('addCategoryModal').classList.remove('hidden');
        document.getElementById('new_category_name').focus();
    };

    window.hideAddCategoryModal = function() {
        document.getElementById('addCategoryModal').classList.add('hidden');
        document.getElementById('new_category_name').value = '';
    };

    // Add category via AJAX
    window.addCategory = function(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const categoryName = formData.get('name');
        
        if (!categoryName.trim()) {
            alert('Nama kategori tidak boleh kosong');
            return;
        }
        
        // Tambahkan CSRF token ke formData
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        fetch(<?= json_encode(base_url('/categories/store')) ?>, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to select
                const select = document.getElementById('category_id');
                const option = new Option(categoryName, data.id || 'new');
                option.selected = true;
                select.add(option);
                
                hideAddCategoryModal();
                
                // Show success message
                showNotification('Kategori berhasil ditambahkan', 'success');
            } else {
                alert(data.message || 'Gagal menambahkan kategori');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambahkan kategori');
        });
    };

    // Real-time code validation - TAMBAHKAN EXCLUDE_ID UNTUK EDIT
    const codeInput = document.getElementById('code');
    if (codeInput) {
        let codeCheckTimeout;
        
        codeInput.addEventListener('input', function() {
            const code = this.value.trim();
            const productId = <?= json_encode($product['id']) ?>; // ID produk yang sedang diedit
            
            // Clear previous timeout
            if (codeCheckTimeout) {
                clearTimeout(codeCheckTimeout);
            }
            
            // Reset styling
            this.classList.remove('border-red-500', 'border-green-500');
            const feedback = document.getElementById('code-feedback');
            if (feedback) {
                feedback.remove();
            }
            
            if (code.length >= 3) {
                // Delay check for better UX
                codeCheckTimeout = setTimeout(() => {
                    checkCodeAvailability(code, productId);
                }, 500);
            }
        });
    }
    
    function checkCodeAvailability(code, excludeId) {
        const formData = new FormData();
        formData.append('code', code);
        formData.append('exclude_id', excludeId); // PENTING: Kirim ID produk untuk exclude
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        fetch(<?= json_encode(base_url('/api/products/check-code')) ?>, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const codeInput = document.getElementById('code');
            let feedback = document.getElementById('code-feedback');
            
            // Remove existing feedback
            if (feedback) {
                feedback.remove();
            }
            
            // Create new feedback element
            feedback = document.createElement('p');
            feedback.id = 'code-feedback';
            feedback.className = 'text-sm mt-1';
            
            if (data.exists) {
                codeInput.classList.add('border-red-500');
                codeInput.classList.remove('border-green-500');
                feedback.className += ' text-red-500';
                feedback.innerHTML = '<i class="fas fa-times-circle mr-1"></i>' + data.message;
            } else {
                codeInput.classList.add('border-green-500');
                codeInput.classList.remove('border-red-500');
                feedback.className += ' text-green-500';
                feedback.innerHTML = '<i class="fas fa-check-circle mr-1"></i>' + data.message;
            }
            
            codeInput.parentNode.appendChild(feedback);
        })
        .catch(error => {
            console.error('Error checking code:', error);
        });
    }

    // Show notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        const typeClasses = {
            success: 'bg-green-100 text-green-800 border-green-200',
            error: 'bg-red-100 text-red-800 border-red-200',
            info: 'bg-blue-100 text-blue-800 border-blue-200'
        };
        
        const iconClasses = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle'
        };
        
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 border ${typeClasses[type]}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${iconClasses[type]} mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Detect changes and warn user
    function hasChanges() {
        const currentValues = {
            code: document.getElementById('code').value,
            name: document.getElementById('name').value,
            category_id: document.getElementById('category_id').value,
            unit: document.getElementById('unit').value,
            min_stock: document.getElementById('min_stock').value
        };
        
        return Object.keys(originalValues).some(key => 
            String(currentValues[key]) !== String(originalValues[key])
        );
    }

    // Warn user before leaving if there are unsaved changes
    function beforeUnloadHandler(e) {
        if (hasChanges()) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
        }
    }

    window.addEventListener('beforeunload', beforeUnloadHandler);

    // Remove warning when form is submitted
    document.querySelector('form').addEventListener('submit', function() {
        window.removeEventListener('beforeunload', beforeUnloadHandler);
    });

    // Close modal when clicking outside
    document.getElementById('addCategoryModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            hideAddCategoryModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideAddCategoryModal();
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const code = document.getElementById('code').value.trim();
        const name = document.getElementById('name').value.trim();
        const categoryId = document.getElementById('category_id').value;
        const unit = document.getElementById('unit').value;
        const minStock = parseFloat(document.getElementById('min_stock').value) || 0;
        
        let errors = [];
        
        if (!code) {
            errors.push('Kode barang harus diisi');
        } else if (code.length < 3) {
            errors.push('Kode barang minimal 3 karakter');
        }
        
        if (!name) {
            errors.push('Nama barang harus diisi');
        } else if (name.length < 3) {
            errors.push('Nama barang minimal 3 karakter');
        }
        
        if (!categoryId) {
            errors.push('Kategori harus dipilih');
        }
        
        if (!unit) {
            errors.push('Satuan harus dipilih');
        }
        
        if (minStock < 0) {
            errors.push('Minimum stok tidak boleh negatif');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            alert('Terdapat kesalahan:\n\n' + errors.join('\n'));
            return false;
        }
        
        // Confirm if code is changed
        if (code !== originalValues.code) {
            if (!confirm('Anda mengubah kode barang. Ini dapat mempengaruhi laporan yang sudah ada.\n\nLanjutkan?')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Number input formatting
    document.querySelectorAll('input[type="number"]').forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });

    // Highlight changed fields
    document.querySelectorAll('input, select, textarea').forEach(function(element) {
        element.addEventListener('input', function() {
            const fieldName = this.name;
            if (originalValues.hasOwnProperty(fieldName)) {
                if (String(this.value) !== String(originalValues[fieldName])) {
                    this.classList.add('border-yellow-400', 'bg-yellow-50');
                } else {
                    this.classList.remove('border-yellow-400', 'bg-yellow-50');
                }
            }
        });
    });
});
</script>
<?= $this->endSection() ?>