<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="bg-white shadow-sm border-b border-gray-200 mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manajemen Pembelian</h1>
                    <p class="mt-1 text-sm text-gray-500">Kelola data pembelian barang dari vendor</p>
                </div>
                <div class="flex space-x-3">
                    <a href="<?= base_url('/purchases/create') ?>" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-plus mr-2"></i>Tambah Pembelian
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
            <input type="text" id="search" name="search" 
                   value="<?= esc($search ?? '') ?>"
                   placeholder="Cari nama vendor, pembeli..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
            <input type="date" id="start_date" name="start_date" 
                   value="<?= esc($start_date ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
            <input type="date" id="end_date" name="end_date" 
                   value="<?= esc($end_date ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="<?= base_url('/purchases') ?>" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Statistik -->
<?php if (isset($statistics)): ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Pembelian</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['total_purchases'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
                <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Nilai</p>
                <p class="text-2xl font-bold text-green-600">Rp <?= number_format($statistics['total_amount'] ?? 0, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-yellow-600"><?= number_format($statistics['pending_purchases'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-full">
                <i class="fas fa-truck text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Vendor</p>
                <p class="text-2xl font-bold text-purple-600"><?= number_format($statistics['total_vendors'] ?? 0) ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Pembelian -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Daftar Pembelian</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nilai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($purchases)): ?>
                    <?php foreach ($purchases as $index => $purchase): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= ($current_page - 1) * $per_page + $index + 1 ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= esc($purchase['vendor_name']) ?></div>
                                <div class="text-sm text-gray-500"><?= esc(substr($purchase['vendor_address'], 0, 50)) ?>...</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($purchase['buyer_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= $purchase['total_items'] ?? 0 ?> item
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Rp <?= number_format($purchase['total_amount'] ?? 0, 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status = $purchase['status'] ?? 'pending';
                                $statusClass = match($status) {
                                    'received' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800'
                                };
                                $statusText = match($status) {
                                    'received' => 'Diterima',
                                    'cancelled' => 'Dibatalkan',
                                    default => 'Pending'
                                };
                                ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?= base_url('/purchases/view/' . $purchase['id']) ?>" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors" 
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('/purchases/edit/' . $purchase['id']) ?>" 
                                       class="text-yellow-600 hover:text-yellow-900 transition-colors" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (session()->get('role') === 'admin'): ?>
                                        <button onclick="deletePurchase(<?= $purchase['id'] ?>, '<?= esc($purchase['vendor_name']) ?>')" 
                                                class="text-red-600 hover:text-red-900 transition-colors" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center py-8">
                                <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">Belum ada data pembelian</p>
                                <p class="text-sm">Tambah pembelian pertama dengan mengklik tombol di atas</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($pager)): ?>
        <div class="px-6 py-3 border-t border-gray-200">
            <?= $pager ?>
        </div>
    <?php endif; ?>
</div>

<script>
function deletePurchase(id, vendorName) {
    if (confirm(`Apakah Anda yakin ingin menghapus pembelian dari vendor "${vendorName}"?\n\nTindakan ini tidak dapat dibatalkan.`)) {
        window.location.href = `<?= base_url('/purchases/delete/') ?>${id}`;
    }
}

// Auto refresh setiap 30 detik
setInterval(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('search') && !urlParams.has('start_date') && !urlParams.has('end_date')) {
        location.reload();
    }
}, 30000);

// Real-time search
let searchTimeout;
document.getElementById('search').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        const form = e.target.closest('form');
        if (e.target.value.length >= 3 || e.target.value.length === 0) {
            form.submit();
        }
    }, 500);
});
</script>
<?= $this->endSection() ?>