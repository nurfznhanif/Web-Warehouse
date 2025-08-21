<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Vendor Baru</h1>
            <p class="text-gray-600 mt-2">Masukkan informasi vendor baru ke dalam sistem</p>
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

    <!-- Main Form Card -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-truck mr-2 text-blue-600"></i>
                Informasi Vendor
            </h3>
            <p class="text-sm text-gray-600 mt-1">Lengkapi formulir di bawah ini dengan informasi vendor yang valid</p>
        </div>

        <div class="p-6">
            <form action="<?= base_url('/vendors/store') ?>" method="POST" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Vendor Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Vendor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?= old('name') ?>"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($validation) && $validation->hasError('name') ? 'border-red-500' : '' ?>"
                           placeholder="Contoh: PT. Suplai Indonesia, CV. Mandiri Jaya, dll.">
                    
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
                               value="<?= old('email') ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($validation) && $validation->hasError('email') ? 'border-red-500' : '' ?>"
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
                                class="px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>Simpan Vendor</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Section -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
            <i class="fas fa-lightbulb mr-2"></i>
            Tips Menambahkan Vendor
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700">
            <div>
                <h5 class="font-semibold mb-2">Informasi Wajib:</h5>
                <ul class="space-y-1">
                    <li><i class="fas fa-check mr-2 text-green-600"></i>Nama vendor harus jelas dan lengkap</li>
                    <li><i class="fas fa-check mr-2 text-green-600"></i>Minimal 3 karakter untuk nama vendor</li>
                </ul>
            </div>
            <div>
                <h5 class="font-semibold mb-2">Informasi Tambahan:</h5>
                <ul class="space-y-1">
                    <li><i class="fas fa-info mr-2 text-blue-600"></i>Alamat membantu proses pengiriman</li>
                    <li><i class="fas fa-info mr-2 text-blue-600"></i>Kontak diperlukan untuk komunikasi</li>
                </ul>
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
</script>
<?= $this->endSection() ?>