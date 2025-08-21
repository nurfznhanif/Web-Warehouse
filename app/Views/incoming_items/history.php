<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Barang Masuk</h1>
        <p class="text-gray-600 mt-1"><?= esc($product['name']) ?> (<?= esc($product['code']) ?>)</p>
    </div>
    <div class="flex space-x-3">
        <a href="<?= base_url('/products/view/' . $product['id']) ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-eye mr-2"></i>
            Lihat Produk
        </a>
        <a href="<?= base_url('/incoming-items') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali
        </a>
    </div>
</div>

<!-- Product Summary -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Produk</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Nama Produk</label>
            <p class="text-sm text-gray-900"><?= esc($product['name']) ?></p>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Kode Produk</label>
            <p class="text-sm text-gray-900"><?= esc($product['code']) ?></p>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Kategori</label>
            <p class="text-sm text-gray-900"><?= esc($product['category_name'] ?? '-') ?></p>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Stok Saat Ini</label>
            <p class="text-sm">
                <span class="font-medium text-blue-600"><?= number_format($product['stock']) ?></span>
                <span class="text-gray-600"><?= esc($product['unit']) ?></span>
            </p>
        </div>
    </div>
</div>

<!-- History Statistics -->
<?php if (!empty($history)): ?>
    <?php
    $totalReceived = array_sum(array_column($history, 'quantity'));
    $totalTransactions = count($history);
    $avgPerTransaction = $totalTransactions > 0 ? $totalReceived / $totalTransactions : 0;
    $latestTransaction = reset($history);
    ?>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-arrow-down text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Diterima</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($totalReceived) ?></p>
                    <p class="text-xs text-gray-500"><?= esc($product['unit']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-list text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $totalTransactions ?></p>
                    <p class="text-xs text-gray-500">transaksi</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata per Transaksi</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($avgPerTransaction, 1) ?></p>
                    <p class="text-xs text-gray-500"><?= esc($product['unit']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <i class="fas fa-calendar text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Transaksi Terakhir</p>
                    <p class="text-lg font-bold text-gray-900"><?= date('d M Y', strtotime($latestTransaction['date'])) ?></p>
                    <p class="text-xs text-gray-500"><?= number_format($latestTransaction['quantity']) ?> <?= esc($product['unit']) ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- History Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Riwayat Transaksi Barang Masuk</h3>
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
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-history text-4xl mb-2"></i>
                            <p>Belum ada riwayat barang masuk untuk produk ini</p>
                            <a href="<?= base_url('/incoming-items/create') ?>" class="text-green-600 hover:text-green-800 mt-2 inline-block">
                                Tambah transaksi pertama
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($history as $index => $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $index + 1 ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= date('d M Y', strtotime($item['date'])) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= date('H:i', strtotime($item['date'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    +<?= number_format($item['quantity']) ?> <?= esc($product['unit']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($item['purchase_id'])): ?>
                                    <div class="text-sm text-gray-900">
                                        <a href="<?= base_url('/purchases/view/' . $item['purchase_id']) ?>"
                                            class="text-blue-600 hover:text-blue-800">
                                            PO #<?= $item['purchase_id'] ?>
                                        </a>
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
</div>

<!-- Export/Actions -->
<?php if (!empty($history)): ?>
    <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Menampilkan <?= count($history) ?> transaksi terakhir
        </div>
        <div class="flex space-x-3">
            <button onclick="window.print()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-print mr-2"></i>
                Print
            </button>
            <a href="<?= base_url('/incoming-items/create?product_id=' . $product['id']) ?>"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-plus mr-2"></i>
                Tambah Transaksi
            </a>
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

    // Print styling
    window.addEventListener('beforeprint', function() {
        document.body.classList.add('print-mode');
    });

    window.addEventListener('afterprint', function() {
        document.body.classList.remove('print-mode');
    });
</script>

<style>
    @media print {
        .print-mode {
            font-size: 12px;
        }

        .print-mode .flex.justify-between,
        .print-mode .bg-gray-600,
        .print-mode .bg-green-600,
        .print-mode button,
        .print-mode .fas.fa-edit,
        .print-mode .fas.fa-trash {
            display: none !important;
        }

        .print-mode .grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        .print-mode .shadow-sm {
            box-shadow: none !important;
            border: 1px solid #e5e7eb !important;
        }
    }
</style>

<?= $this->endSection() ?>