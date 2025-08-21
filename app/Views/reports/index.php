<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Warehouse</h1>
            <p class="text-gray-600 mt-1">Kelola dan lihat berbagai laporan sistem warehouse</p>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <i class="fas fa-calendar-alt"></i>
            <span><?= date('d M Y, H:i') ?></span>
        </div>
    </div>
</div>

<!-- Report Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Laporan Barang Masuk -->
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-arrow-down text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Barang Masuk</h3>
                        <p class="text-sm text-gray-600">Laporan transaksi barang masuk</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Filter berdasarkan:</span>
                    <span class="text-green-600 font-medium">Rentang Tanggal</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Format:</span>
                    <span class="text-gray-900">Tabel & Export</span>
                </div>
            </div>

            <div class="mt-6">
                <a href="<?= base_url('/reports/incoming') ?>"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-chart-line mr-2"></i>
                    Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Laporan Barang Keluar -->
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-arrow-up text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Barang Keluar</h3>
                        <p class="text-sm text-gray-600">Laporan transaksi barang keluar</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Filter berdasarkan:</span>
                    <span class="text-red-600 font-medium">Rentang Tanggal</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Format:</span>
                    <span class="text-gray-900">Tabel & Export</span>
                </div>
            </div>

            <div class="mt-6">
                <a href="<?= base_url('/reports/outgoing') ?>"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-chart-line mr-2"></i>
                    Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Laporan Stok Barang -->
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-boxes text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Stok Barang</h3>
                        <p class="text-sm text-gray-600">Laporan stok terkini semua produk</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Data:</span>
                    <span class="text-blue-600 font-medium">Real-time</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Kategori:</span>
                    <span class="text-gray-900">Semua Produk</span>
                </div>
            </div>

            <div class="mt-6">
                <a href="<?= base_url('/reports/stock') ?>"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-chart-line mr-2"></i>
                    Lihat Laporan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Statistics -->
<div class="mt-8 bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Statistik Singkat</h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center">
            <div class="bg-purple-100 rounded-full p-4 w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-calendar-day text-purple-600 text-xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900" id="today-incoming">-</h3>
            <p class="text-sm text-gray-600">Barang Masuk Hari Ini</p>
        </div>

        <div class="text-center">
            <div class="bg-orange-100 rounded-full p-4 w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-calendar-day text-orange-600 text-xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900" id="today-outgoing">-</h3>
            <p class="text-sm text-gray-600">Barang Keluar Hari Ini</p>
        </div>

        <div class="text-center">
            <div class="bg-yellow-100 rounded-full p-4 w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900" id="low-stock">-</h3>
            <p class="text-sm text-gray-600">Produk Stok Rendah</p>
        </div>

        <div class="text-center">
            <div class="bg-gray-100 rounded-full p-4 w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                <i class="fas fa-box text-gray-600 text-xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900" id="total-products">-</h3>
            <p class="text-sm text-gray-600">Total Produk</p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Load quick statistics on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadQuickStats();
    });

    function loadQuickStats() {
        // Simulate loading statistics
        // In real implementation, this would be an AJAX call to get actual data
        setTimeout(() => {
            document.getElementById('today-incoming').textContent = '12';
            document.getElementById('today-outgoing').textContent = '8';
            document.getElementById('low-stock').textContent = '5';
            document.getElementById('total-products').textContent = '150';
        }, 500);
    }
</script>
<?= $this->endSection() ?>