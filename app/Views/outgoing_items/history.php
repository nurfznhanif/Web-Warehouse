<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Barang Keluar</h1>
        <p class="text-gray-600"><?= esc($product['name']) ?> (<?= esc($product['code']) ?>)</p>
    </div>
    <div class="flex space-x-2">
        <a href="<?= base_url('/outgoing-items/create') ?>" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Tambah Barang Keluar
        </a>
        <a href="<?= base_url('/outgoing-items') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali
        </a>
    </div>
</div>

<!-- Product Info Card -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg mr-3">
                <i class="fas fa-box text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Stok Saat Ini</p>
                <p class="text-lg font-bold text-gray-900"><?= number_format($product['stock']) ?> <?= esc($product['unit']) ?></p>
            </div>
        </div>

        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Stok Minimum</p>
                <p class="text-lg font-bold text-gray-900"><?= number_format($product['min_stock']) ?> <?= esc($product['unit']) ?></p>
            </div>
        </div>

        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg mr-3">
                <i class="fas fa-tags text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Kategori</p>
                <p class="text-lg font-bold text-gray-900"><?= esc($product['category_name']) ?></p>
            </div>
        </div>

        <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg mr-3">
                <i class="fas fa-arrow-up text-red-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Riwayat</p>
                <p class="text-lg font-bold text-gray-900"><?= count($history) ?> Transaksi</p>
            </div>
        </div>
    </div>
</div>

<!-- History Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Riwayat Transaksi Barang Keluar</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Keluar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-history text-4xl mb-2 text-red-300"></i>
                            <p>Belum ada riwayat barang keluar untuk produk ini</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($history as $index => $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $index + 1 ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>
                                    <div class="font-medium"><?= date('d/m/Y', strtotime($item['date'])) ?></div>
                                    <div class="text-gray-500"><?= date('H:i:s', strtotime($item['date'])) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <span class="font-medium text-red-600"><?= number_format($item['quantity']) ?></span>
                                    <span class="ml-1 text-gray-500"><?= esc($product['unit']) ?></span>
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
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('/outgoing-items/delete/' . $item['id']) ?>"
                                        onclick="return confirmDelete()"
                                        class="text-red-600 hover:text-red-900"
                                        title="Hapus">
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
</div>

<!-- Summary Statistics -->
<?php if (!empty($history)): ?>
    <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-sum text-red-600 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm text-red-600">Total Barang Keluar</p>
                        <p class="text-xl font-bold text-red-800">
                            <?php
                            $totalQuantity = array_sum(array_column($history, 'quantity'));
                            echo number_format($totalQuantity);
                            ?> <?= esc($product['unit']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-calendar-day text-blue-600 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm text-blue-600">Transaksi Terakhir</p>
                        <p class="text-xl font-bold text-blue-800">
                            <?= !empty($history) ? date('d/m/Y', strtotime($history[0]['date'])) : '-' ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-yellow-600 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm text-yellow-600">Rata-rata per Transaksi</p>
                        <p class="text-xl font-bold text-yellow-800">
                            <?php
                            $avgQuantity = !empty($history) ? array_sum(array_column($history, 'quantity')) / count($history) : 0;
                            echo number_format($avgQuantity, 2);
                            ?> <?= esc($product['unit']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function confirmDelete() {
        return confirm('Apakah Anda yakin ingin menghapus transaksi barang keluar ini? Stok produk akan dikembalikan.');
    }
</script>
<?= $this->endSection() ?>