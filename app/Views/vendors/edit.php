<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Vendor</h1>
            <p class="text-gray-600 mt-2">Perbarui informasi vendor: <span class="font-semibold"><?= esc($vendor['name']) ?></span></p>
        </div>
        <a href="<?= base_url('/vendors') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center space-x-2">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Daftar Vendor</span>
        </a>
    </div>

    <!-- Success/Error Messages -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?= session()->getFlashdata('success') ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Vendor Info Summary -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-truck text-blue-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-blue-800"><?= esc($vendor['name']) ?></h3>
                <p class="text-blue-600 text-sm">
                    Vendor ID: #<?= $vendor['id'] ?> | 
                    Terdaftar: <?= date('d F Y', strtotime($vendor['created_at'])) ?>
                    <?php if (isset($vendor['purchase_count'])): ?>
                    | <?= $vendor['purchase_count'] ?> Transaksi Pembelian
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-edit mr-2 text-green-600"></i>
                Edit Informasi Vendor
            </h3>
            <p class="text-sm text-gray-600 mt-1">Perbarui informasi vendor sesuai kebutuhan</p>
        </div>

        <div class="p-6">
            <form action="<?= base_url('/vendors/update/' . $vendor['id']) ?>" method="POST" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Vendor Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Vendor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?= old('name', $vendor['name']) ?>"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent <?= isset($validation) && $validation->hasError('name') ? 'border-red-500' : '' ?>"
                           placeholder="Contoh: PT. Suplai Indonesia, CV. Mandiri Jaya, dll.">
                    
                    <?php if (isset($validation) && $validation->hasError('name')): ?>
                    <p class="text-red-500 text-sm mt-1">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <?= $validation->getError('name') ?>
                    </p>
                    <?php endif; ?>
                    
                    <p class="text-gray-500 text-sm mt-1">Masukkan nama lengkap vendor atau perusahaan supplier</p>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat
                    </label>
                    <textarea id="address" 
                              name="address" 
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent <?= isset($validation) && $validation->hasError('address') ? 'border-red-500' : '' ?>"
                              placeholder="Masukkan alamat lengkap vendor..."><?= old('address', $vendor['address']) ?></textarea>
                    
                    <?php if (isset($validation) && $validation->hasError('address')): ?>
                    <p class="text-red-500 text-sm mt-1">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <?= $validation->getError('address') ?>
                    </p>
                    <?php endif; ?>
                    
                    <p class="text-gray-500 text-sm mt-1">Alamat akan digunakan untuk pengiriman dan korespondensi</p>
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-phone text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="phone" 
                               name="phone" 
                               value="<?= old('phone', $vendor['phone']) ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent <?= isset($validation) && $validation->hasError('phone') ? 'border-red-500' : '' ?>"
                               placeholder="Contoh: 021-12345678, 0812-3456-7890">
                    </div>
                    
                    <?php if (isset($validation) && $validation->hasError('phone')): ?>
                    <p class="text-red-500 text-sm mt-1">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <?= $validation->getError('phone') ?>
                    </p>
                    <?php endif; ?>
                    
                    <p class="text-gray-500 text-sm mt-1">Nomor telepon untuk komunikasi dan konfirmasi pesanan</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?= old('email', $vendor['email']) ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent <?= isset($validation) && $validation->hasError('email') ? 'border-red-500' : '' ?>"
                               placeholder="contoh@vendor.com">
                    </div>
                    
                    <?php if (isset($validation) && $validation->hasError('email')): ?>
                    <p class="text-red-500 text-sm mt-1">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <?= $validation->getError('email') ?>
                    </p>
                    <?php endif; ?>
                    
                    <p class="text-gray-500 text-sm mt-1">Email untuk komunikasi resmi dan pengiriman dokumen</p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Field dengan tanda <span class="text-red-500">*</span> wajib diisi
                    </div>
                    
                    <div class="flex space-x-3">
                        <a href="<?= base_url('/vendors') ?>" 
                           class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200 flex items-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </a>
                        
                        <button type="submit" 
                                class="px-6 py-3 text-white bg-green-600 rounded-lg hover:bg-green-700 transition duration-200 flex items-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>Update Vendor</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Additional Information Section -->
    <?php if (isset($vendor['purchase_count']) && $vendor['purchase_count'] > 0): ?>
    <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-yellow-800 mb-3 flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Informasi Penting
        </h4>
        <div class="text-sm text-yellow-700">
            <p class="mb-2">
                Vendor ini memiliki <strong><?= $vendor['purchase_count'] ?> transaksi pembelian</strong> yang tercatat dalam sistem.
            </p>
            <p>
                <i class="fas fa-shield-alt mr-1"></i>
                Vendor dengan transaksi tidak dapat dihapus untuk menjaga integritas data.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Change Log Section -->
    <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-history mr-2"></i>
            Riwayat Perubahan
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
                <p><strong>Dibuat:</strong> <?= date('d F Y H:i', strtotime($vendor['created_at'])) ?></p>
            </div>
            <div>
                <p><strong>Terakhir Diupdate:</strong> <?= date('d F Y H:i', strtotime($vendor['updated_at'])) ?></p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-focus on name field when page loads
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('name').focus();
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        
        // Check required fields
        if (!name) {
            e.preventDefault();
            alert('Nama vendor harus diisi!');
            document.getElementById('name').focus();
            return;
        }
        
        if (name.length < 3) {
            e.preventDefault();
            alert('Nama vendor minimal 3 karakter!');
            document.getElementById('name').focus();
            return;
        }
        
        // Validate email format if provided
        if (email && !isValidEmail(email)) {
            e.preventDefault();
            alert('Format email tidak valid!');
            document.getElementById('email').focus();
            return;
        }
        
        // Confirm update
        if (!confirm('Apakah Anda yakin ingin memperbarui informasi vendor ini?')) {
            e.preventDefault();
            return;
        }
    });

    // Email validation function
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Real-time email validation
    document.getElementById('email').addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !isValidEmail(email)) {
            this.classList.add('border-red-500');
            this.classList.remove('border-gray-300');
        } else {
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-300');
        }
    });

    // Auto-format phone number (basic formatting)
    document.getElementById('phone').addEventListener('input', function() {
        let value = this.value.replace(/\D/g, ''); // Remove non-digits
        if (value.length > 0) {
            // Basic formatting for Indonesian phone numbers
            if (value.startsWith('62')) {
                value = '+' + value;
            } else if (value.startsWith('08')) {
                // Mobile format
                value = value.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
            } else if (value.startsWith('021') || value.startsWith('022') || value.startsWith('024')) {
                // Landline format
                value = value.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
            }
        }
        this.value = value;
    });

    // Highlight changes from original data
    const originalData = {
        name: <?= json_encode($vendor['name']) ?>,
        address: <?= json_encode($vendor['address']) ?>,
        phone: <?= json_encode($vendor['phone']) ?>,
        email: <?= json_encode($vendor['email']) ?>
    };

    function highlightChanges() {
        const fields = ['name', 'address', 'phone', 'email'];
        
        fields.forEach(field => {
            const element = document.getElementById(field);
            const currentValue = element.value.trim();
            const originalValue = originalData[field] || '';
            
            if (currentValue !== originalValue) {
                element.classList.add('border-orange-300', 'bg-orange-50');
            } else {
                element.classList.remove('border-orange-300', 'bg-orange-50');
            }
        });
    }

    // Add event listeners for change detection
    ['name', 'address', 'phone', 'email'].forEach(field => {
        document.getElementById(field).addEventListener('input', highlightChanges);
    });
</script>
<?= $this->endSection() ?>