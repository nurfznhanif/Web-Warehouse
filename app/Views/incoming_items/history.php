<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Define format_quantity function inline since it's not defined globally
if (!function_exists('format_quantity')) {
    function format_quantity($number)
    {
        // Remove trailing zeros and format with thousands separator
        $formatted = number_format((float)$number, 2, '.', ',');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, '.');
        return $formatted;
    }
}
?>

<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Riwayat Barang Masuk</h1>
            <p class="mt-2 text-gray-600">Riwayat penerimaan untuk produk: <span class="font-semibold"><?= esc($product['name']) ?></span></p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= base_url('/incoming-items') ?>"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Product Info Card -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-6">
            <div class="flex items-center space-x-6">
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-box text-blue-600 text-2xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900"><?= esc($product['name']) ?></h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                        <div>
                            <span class="text-sm text-gray-500">Kode Produk</span>
                            <p class="font-medium text-gray-900"><?= esc($product['code']) ?></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Kategori</span>
                            <p class="font-medium text-gray-900"><?= esc($product['category_name'] ?? 'Tidak ada kategori') ?></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Stok Saat Ini</span>
                            <p class="font-medium text-green-600"><?= format_quantity($product['stock'] ?? 0) ?> <?= esc($product['unit']) ?></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Minimum Stok</span>
                            <p class="font-medium text-orange-600"><?= format_quantity($product['min_stock'] ?? 0) ?> <?= esc($product['unit']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <?php if (!empty($history)): ?>
        <?php
        $totalReceived = array_sum(array_column($history, 'quantity'));
        $totalTransactions = count($history);
        $lastReceived = $history[0]['date'] ?? null;
        $avgPerTransaction = $totalTransactions > 0 ? $totalReceived / $totalTransactions : 0;
        ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Diterima</p>
                        <p class="text-2xl font-bold text-gray-900"><?= format_quantity($totalReceived) ?></p>
                        <p class="text-sm text-gray-500"><?= esc($product['unit']) ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-arrow-down text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                        <p class="text-2xl font-bold text-gray-900"><?= format_quantity($totalTransactions) ?></p>
                        <p class="text-sm text-gray-500">transaksi</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-list text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Rata-rata per Transaksi</p>
                        <p class="text-2xl font-bold text-gray-900"><?= format_quantity($avgPerTransaction) ?></p>
                        <p class="text-sm text-gray-500"><?= esc($product['unit']) ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Terakhir Diterima</p>
                        <p class="text-lg font-bold text-gray-900">
                            <?= $lastReceived ? date('d/m/Y', strtotime($lastReceived)) : 'Belum ada' ?>
                        </p>
                        <p class="text-sm text-gray-500">
                            <?= $lastReceived ? date('H:i', strtotime($lastReceived)) : '' ?>
                        </p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- History Table -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Riwayat Penerimaan</h3>
            <p class="text-sm text-gray-600 mt-1">
                Daftar semua transaksi penerimaan untuk produk ini
            </p>
        </div>

        <?php if (empty($history)): ?>
            <div class="text-center py-12">
                <i class="fas fa-history text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada riwayat penerimaan</h3>
                <p class="text-gray-600">Produk ini belum pernah diterima melalui sistem.</p>
                <a href="<?= base_url('/incoming-items/create') ?>"
                    class="mt-4 inline-flex px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Barang Masuk
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transaksi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal & Waktu
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pembelian
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($history as $index => $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-green-100 rounded-full p-2 mr-3">
                                            <i class="fas fa-arrow-down text-green-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                #<?= str_pad($item['id'], 6, '0', STR_PAD_LEFT) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Transaksi ke-<?= $totalTransactions - $index ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?= date('d/m/Y', strtotime($item['date'])) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= date('H:i:s', strtotime($item['date'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-green-600">
                                        +<?= format_quantity($item['quantity']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= esc($product['unit']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($item['purchase_number'])): ?>
                                        <div class="text-sm text-blue-600">
                                            <a href="<?= base_url('/purchases/view/' . $item['purchase_number']) ?>"
                                                class="hover:underline">
                                                #<?= str_pad($item['purchase_number'], 6, '0', STR_PAD_LEFT) ?>
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= esc($item['vendor_name'] ?? 'Unknown Vendor') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">Manual Entry</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-gray-100 rounded-full p-1 mr-2">
                                            <i class="fas fa-user text-gray-600 text-xs"></i>
                                        </div>
                                        <span class="text-sm text-gray-900">
                                            <?= esc($item['user_name'] ?? 'Unknown') ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="<?= base_url('/incoming-items/view/' . $item['id']) ?>"
                                            class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('/incoming-items/receipt/' . $item['id']) ?>"
                                            class="text-green-600 hover:text-green-900" title="Cetak Bukti">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="<?= base_url('/incoming-items/edit/' . $item['id']) ?>"
                                            class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Timeline Summary -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <strong><?= format_quantity($totalTransactions) ?></strong> transaksi penerimaan tercatat
                        dengan total <strong><?= format_quantity($totalReceived) ?> <?= esc($product['unit']) ?></strong>
                    </div>
                    <div class="flex space-x-4 text-sm text-gray-500">
                        <?php if (!empty($history)): ?>
                            <span>Pertama: <?= date('d/m/Y', strtotime(end($history)['date'])) ?></span>
                            <span>Terakhir: <?= date('d/m/Y', strtotime($history[0]['date'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add any interactive features here if needed
        console.log('History page loaded for product: <?= esc($product['name']) ?>');
    });
</script>
<?= $this->endSection() ?>