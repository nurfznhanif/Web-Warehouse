<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Barang Masuk</h1>
        <p class="text-gray-600 mt-1">Kelola transaksi barang masuk dan stok inventory</p>
    </div>
    <a href="<?= base_url('/incoming-items/create') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-plus mr-2"></i>
        Tambah Barang Masuk
    </a>
</div>

<!-- Statistics Cards -->
<?php if (isset($statistics)): ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-arrow-down text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['total_transactions'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-boxes text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Quantity</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['total_quantity'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-calendar text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Hari Ini</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['today_count'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                <i class="fas fa-shopping-cart text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Dari Pembelian</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['from_purchase'] ?? 0) ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="<?= base_url('/incoming-items') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                Cari Produk/Kode/Vendor
            </label>
            <input type="text" 
                   id="search" 
                   name="search" 
                   value="<?= esc($search ?? '') ?>"
                   placeholder="Masukkan kata kunci..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                Tanggal Mulai
            </label>
            <input type="date" 
                   id="start_date" 
                   name="start_date" 
                   value="<?= esc($start_date ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                Tanggal Akhir
            </label>
            <input type="date" 
                   id="end_date" 
                   name="end_date" 
                   value="<?= esc($end_date ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center">
                <i class="fas fa-search mr-2"></i>
                Filter
            </button>
            <a href="<?= base_url('/incoming-items') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Incoming Items Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">
            Daftar Barang Masuk
            <?php if (isset($total_items) && $total_items > 0): ?>
                <span class="text-sm text-gray-500">(<?= number_format($total_items) ?> transaksi)</span>
            <?php endif; ?>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Produk
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Quantity
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Dari Pembelian
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Penanggung Jawab
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Catatan
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($incoming_items)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Belum ada transaksi barang masuk</p>
                            <a href="<?= base_url('/incoming-items/create') ?>" class="text-green-600 hover:text-green-800 mt-2 inline-block">
                                Tambah transaksi pertama
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = isset($current_page) && isset($per_page) ? 
                          (($current_page - 1) * $per_page) + 1 : 1;
                    ?>
                    <?php foreach ($incoming_items as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $no++ ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= date('d M Y', strtotime($item['date'])) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= date('H:i', strtotime($item['date'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-box text-green-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?= esc($item['product_name']) ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?= esc($item['product_code']) ?> • <?= esc($item['category_name'] ?? '-') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    +<?= number_format($item['quantity']) ?> <?= esc($item['unit']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($item['purchase_number'])): ?>
                                    <div class="text-sm text-gray-900">
                                        PO #<?= esc($item['purchase_number']) ?>
                                    </div>
                                    <?php if (!empty($item['vendor_name'])): ?>
                                        <div class="text-xs text-gray-500">
                                            <?= esc($item['vendor_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">Manual Entry</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($item['user_name'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($item['notes'])): ?>
                                    <div class="text-sm text-gray-600 max-w-xs truncate" title="<?= esc($item['notes']) ?>">
                                        <?= esc($item['notes']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?= base_url('/incoming-items/edit/' . $item['id']) ?>" 
                                       class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (session()->get('role') === 'admin'): ?>
                                        <button onclick="deleteItem(<?= $item['id'] ?>)" 
                                                class="text-red-600 hover:text-red-900" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (isset($pager) && !empty($incoming_items)): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <?= $pager ?>
        </div>
    <?php endif; ?>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div id="success-alert" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span><?= session()->getFlashdata('success') ?></span>
            <button onclick="document.getElementById('success-alert').remove()" class="ml-4 text-green-700 hover:text-green-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div id="error-alert" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span><?= session()->getFlashdata('error') ?></span>
            <button onclick="document.getElementById('error-alert').remove()" class="ml-4 text-red-700 hover:text-red-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function deleteItem(id) {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok produk akan dikurangi sesuai dengan quantity yang dihapus.')) {
        window.location.href = '<?= base_url('/incoming-items/delete/') ?>' + id;
    }
}

// Auto hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('#success-alert, #error-alert');
    alerts.forEach(alert => {
        if (alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    });
}, 5000);
</script>
<?= $this->endSection() ?>