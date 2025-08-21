<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Data Barang</h1>
    <a href="<?= base_url('/products/create') ?>" 
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
        <i class="fas fa-plus mr-2"></i>Tambah Barang
    </a>
</div>

<!-- Filter dan Search -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="<?= base_url('/products') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cari Barang</label>
            <input type="text" name="search" value="<?= esc($search ?? '') ?>" 
                   placeholder="Nama atau kode barang..." 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
            <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Kategori</option>
                <?php if (isset($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($category_filter ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= esc($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status Stok</label>
            <select name="stock_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="in_stock" <?= ($stock_status ?? '') == 'in_stock' ? 'selected' : '' ?>>Stok Aman</option>
                <option value="low_stock" <?= ($stock_status ?? '') == 'low_stock' ? 'selected' : '' ?>>Stok Rendah</option>
                <option value="out_of_stock" <?= ($stock_status ?? '') == 'out_of_stock' ? 'selected' : '' ?>>Habis</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg mr-2 transition-colors">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="<?= base_url('/products') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Tabel Data Barang -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="<?= base_url('/products?sort=code&order=' . (($sort ?? '') == 'code' && ($order ?? '') == 'asc' ? 'desc' : 'asc')) ?>" 
                           class="hover:text-gray-700">
                            Kode Barang
                            <?php if (($sort ?? '') == 'code'): ?>
                                <i class="fas fa-sort-<?= ($order ?? '') == 'asc' ? 'up' : 'down' ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="<?= base_url('/products?sort=name&order=' . (($sort ?? '') == 'name' && ($order ?? '') == 'asc' ? 'desc' : 'asc')) ?>" 
                           class="hover:text-gray-700">
                            Nama Barang
                            <?php if (($sort ?? '') == 'name'): ?>
                                <i class="fas fa-sort-<?= ($order ?? '') == 'asc' ? 'up' : 'down' ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="<?= base_url('/products?sort=stock&order=' . (($sort ?? '') == 'stock' && ($order ?? '') == 'asc' ? 'desc' : 'asc')) ?>" 
                           class="hover:text-gray-700">
                            Stok
                            <?php if (($sort ?? '') == 'stock'): ?>
                                <i class="fas fa-sort-<?= ($order ?? '') == 'asc' ? 'up' : 'down' ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-box-open text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg mb-2">Tidak ada data barang</p>
                                <p class="text-sm">Silakan tambahkan barang baru</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php 
                        $stockStatus = 'in_stock';
                        $stockClass = 'text-green-600 bg-green-100';
                        $stockText = 'Stok Aman';
                        
                        if ($product['stock'] <= 0) {
                            $stockStatus = 'out_of_stock';
                            $stockClass = 'text-red-600 bg-red-100';
                            $stockText = 'Habis';
                        } elseif ($product['stock'] <= ($product['min_stock'] ?? 10)) {
                            $stockStatus = 'low_stock';
                            $stockClass = 'text-yellow-600 bg-yellow-100';
                            $stockText = 'Stok Rendah';
                        }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= esc($product['code']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?= esc($product['name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= esc($product['category_name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= esc($product['unit']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= number_format($product['stock'], 0, ',', '.') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $stockClass ?>">
                                    <?= $stockText ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="<?= base_url('/products/view/' . $product['id']) ?>" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors" 
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('/products/edit/' . $product['id']) ?>" 
                                       class="text-yellow-600 hover:text-yellow-900 transition-colors" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (session()->get('role') === 'admin'): ?>
                                        <button onclick="deleteProduct(<?= $product['id'] ?>, '<?= esc($product['name']) ?>')" 
                                                class="text-red-600 hover:text-red-900 transition-colors" 
                                                title="Hapus">
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
    
    <!-- Pagination jika diperlukan -->
    <?php if (isset($pager)): ?>
        <div class="px-6 py-3 border-t border-gray-200">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<!-- Statistik Stok -->
<?php if (!empty($products)): ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
                <i class="fas fa-boxes text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Produk</p>
                <p class="text-2xl font-bold text-gray-900"><?= count($products) ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Stok Aman</p>
                <p class="text-2xl font-bold text-green-600">
                    <?= count(array_filter($products, function($p) { return $p['stock'] > ($p['min_stock'] ?? 10); })) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Stok Rendah</p>
                <p class="text-2xl font-bold text-yellow-600">
                    <?= count(array_filter($products, function($p) { return $p['stock'] > 0 && $p['stock'] <= ($p['min_stock'] ?? 10); })) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
                <i class="fas fa-times-circle text-red-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Habis</p>
                <p class="text-2xl font-bold text-red-600">
                    <?= count(array_filter($products, function($p) { return $p['stock'] <= 0; })) ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function deleteProduct(id, name) {
    if (confirm('Apakah Anda yakin ingin menghapus produk "' + name + '"?\n\nPerhatian: Data transaksi yang terkait dengan produk ini juga akan terpengaruh.')) {
        window.location.href = '<?= base_url('/products/delete/') ?>' + id;
    }
}

// Auto submit form when filter changes
document.querySelectorAll('select[name="category_id"], select[name="stock_status"]').forEach(function(select) {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});

// Search with delay
let searchTimeout;
document.querySelector('input[name="search"]').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const form = this.form;
    searchTimeout = setTimeout(function() {
        if (form.querySelector('input[name="search"]').value.length >= 3 || form.querySelector('input[name="search"]').value.length === 0) {
            form.submit();
        }
    }, 500);
});
</script>
<?= $this->endSection() ?>