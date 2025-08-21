<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded notification">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?= session()->getFlashdata('success') ?>
        </div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded notification">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Barang Keluar</h1>
        <p class="text-gray-600">Kelola transaksi barang keluar dari gudang</p>
    </div>
    <a href="<?= base_url('/outgoing-items/create') ?>" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-plus mr-2"></i>
        Tambah Barang Keluar
    </a>
</div>

<!-- Statistics Cards -->
<?php if (isset($statistics)): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-arrow-up text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Barang Keluar Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['today_count'] ?? 0) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-box text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Kuantitas Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['today_quantity'] ?? 0) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-calendar text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Bulan Ini</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['month_count'] ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="<?= base_url('/outgoing-items') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
            <input type="text" name="search" id="search" value="<?= esc($search ?? '') ?>"
                placeholder="Nama produk, kode, penerima..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
            <input type="date" name="start_date" id="start_date" value="<?= esc($start_date ?? '') ?>"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
            <input type="date" name="end_date" id="end_date" value="<?= esc($end_date ?? '') ?>"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
        </div>

        <div class="flex items-end space-x-2">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md flex items-center">
                <i class="fas fa-search mr-2"></i>
                Filter
            </button>
            <a href="<?= base_url('/outgoing-items') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Outgoing Items Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">
            Daftar Barang Keluar
            <?php if (isset($total_items)): ?>
                <span class="text-sm text-gray-500">(<?= number_format($total_items) ?> item)</span>
            <?php endif; ?>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($outgoing_items)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-arrow-up text-4xl mb-2 text-red-300"></i>
                            <p>Belum ada transaksi barang keluar</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $offset = ($current_page - 1) * $per_page;
                    foreach ($outgoing_items as $index => $item):
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $offset + $index + 1 ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>
                                    <div class="font-medium"><?= date('d/m/Y', strtotime($item['date'])) ?></div>
                                    <div class="text-gray-500"><?= date('H:i', strtotime($item['date'])) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?= esc($item['product_name']) ?></div>
                                    <div class="text-sm text-gray-500"><?= esc($item['product_code']) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= esc($item['category_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <span class="font-medium text-red-600"><?= number_format($item['quantity']) ?></span>
                                    <span class="ml-1 text-gray-500"><?= esc($item['unit']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($item['recipient'] ?: '-') ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                <div class="truncate" title="<?= esc($item['description']) ?>">
                                    <?= esc($item['description'] ?: '-') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc($item['user_name'] ?? 'System') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?= base_url('/outgoing-items/edit/' . $item['id']) ?>"
                                        class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('/outgoing-items/delete/' . $item['id']) ?>"
                                        onclick="return confirmDelete()"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (isset($pager) && $total_items > $per_page): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <?= $pager ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function confirmDelete() {
        return confirm('Apakah Anda yakin ingin menghapus transaksi barang keluar ini? Stok produk akan dikembalikan.');
    }
</script>
<?= $this->endSection() ?>