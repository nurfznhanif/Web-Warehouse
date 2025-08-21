<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Barang Baru</h1>
        <p class="text-gray-600 mt-1">Tambahkan produk baru ke dalam sistem inventory</p>
    </div>
    <a href="<?= base_url('/products') ?>" 
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Kembali
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="<?= base_url('/products/store') ?>" method="POST" class="space-y-6" onsubmit="showLoading(this.querySelector('button[type=submit]'))">
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
                           value="<?= old('code') ?>"
                           placeholder="Contoh: BRG001"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 <?= session('errors.code') ? 'border-red-500' : '' ?>"
                           required>
                    <button type="button" 
                            onclick="generateCode()" 
                            class="absolute right-2 top-2 bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition-colors">
                        Generate
                    </button>
                </div>
                <?php if (session('errors.code')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session('errors.code') ?></p>
                <?php endif; ?>
                <p class="text-gray-500 text-sm mt-1">Kode unik untuk identifikasi barang</p>
            </div>

            <!-- Nama Barang -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Barang <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= old('name') ?>"
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
                                <option value="<?= $category['id'] ?>" <?= old('category_id') == $category['id'] ? 'selected' : '' ?>>
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
                    <option value="pcs" <?= old('unit') == 'pcs' ? 'selected' : '' ?>>Piece (pcs)</option>
                    <option value="kg" <?= old('unit') == 'kg' ? 'selected' : '' ?>>Kilogram (kg)</option>
                    <option value="liter" <?= old('unit') == 'liter' ? 'selected' : '' ?>>Liter</option>
                    <option value="meter" <?= old('unit') == 'meter' ? 'selected' : '' ?>>Meter (m)</option>
                    <option value="box" <?= old('unit') == 'box' ? 'selected' : '' ?>>Box</option>
                    <option value="pack" <?= old('unit') == 'pack' ? 'selected' : '' ?>>Pack</option>
                    <option value="roll" <?= old('unit') == 'roll' ? 'selected' : '' ?>>Roll</option>
                    <option value="set" <?= old('unit') == 'set' ? 'selected' : '' ?>>Set</option>
                    <option value="unit" <?= old('unit') == 'unit' ? 'selected' : '' ?>>Unit</option>
                </select>
                <?php if (session('errors.unit')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session('errors.unit') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Stok Awal -->
            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                    Stok Awal
                </label>
                <input type="number" 
                       id="stock" 
                       name="stock" 
                       value="<?= old('stock', '0') ?>"
                       min="0"
                       step="0.01"
                       placeholder="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 <?= session('errors.stock') ? 'border-red-500' : '' ?>">
                <?php if (session('errors.stock')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session('errors.stock') ?></p>
                <?php endif; ?>
                <p class="text-gray-500 text-sm mt-1">Jumlah stok awal saat produk ditambahkan</p>
            </div>

            <!-- Minimum Stok -->
            <div>
                <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-2">
                    Minimum Stok
                </label>
                <input type="number" 
                       id="min_stock" 
                       name="min_stock" 
                       value="<?= old('min_stock', '10') ?>"
                       min="0"
                       step="0.01"
                       placeholder="10"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 <?= session('errors.min_stock') ? 'border-red-500' : '' ?>">
                <?php if (session('errors.min_stock')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session('errors.min_stock') ?></p>
                <?php endif; ?>
                <p class="text-gray-500 text-sm mt-1">Batas minimum stok untuk peringatan</p>
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
                    <i class="fas fa-save mr-2"></i>Simpan Barang
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
// Generate kode barang otomatis
function generateCode() {
    const timestamp = Date.now().toString().substr(-6);
    const randomNum = Math.floor(Math.random() * 100).toString().padStart(2, '0');
    const code = 'BRG' + timestamp + randomNum;
    document.getElementById('code').value = code;
}

// Auto-generate slug dari nama untuk kode
document.getElementById('name').addEventListener('input', function() {
    const codeField = document.getElementById('code');
    if (!codeField.value) {
        const name = this.value;
        const slug = name.toUpperCase()
                        .replace(/[^A-Z0-9]/g, '')
                        .substr(0, 3);
        if (slug.length >= 2) {
            const timestamp = Date.now().toString().substr(-4);
            codeField.value = slug + timestamp;
        }
    }
});

// Modal functions
function showAddCategoryModal() {
    document.getElementById('addCategoryModal').classList.remove('hidden');
    document.getElementById('new_category_name').focus();
}

function hideAddCategoryModal() {
    document.getElementById('addCategoryModal').classList.add('hidden');
    document.getElementById('new_category_name').value = '';
}

// Add category via AJAX
function addCategory(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const categoryName = formData.get('name');
    
    if (!categoryName.trim()) {
        alert('Nama kategori tidak boleh kosong');
        return;
    }
    
    fetch('<?= base_url('/categories/store') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?= csrf_token() ?>'
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
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
        type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
        'bg-blue-100 text-blue-800 border border-blue-200'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
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

// Close modal when clicking outside
document.getElementById('addCategoryModal').addEventListener('click', function(e) {
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
    const stock = parseFloat(document.getElementById('stock').value) || 0;
    
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
    
    if (stock < 0) {
        errors.push('Stok tidak boleh negatif');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Terdapat kesalahan:\n\n' + errors.join('\n'));
        return false;
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
</script>
<?= $this->endSection() ?>