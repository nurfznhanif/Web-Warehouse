<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Pembelian #<?= str_pad($purchase['id'], 4, '0', STR_PAD_LEFT) ?></h1>
                <p class="text-gray-600 mt-1">Informasi lengkap pesanan pembelian</p>
            </div>
            <div class="flex space-x-2">
                <?php if ($purchase['status'] === 'pending'): ?>
                    <a href="<?= base_url('/purchases/edit/' . $purchase['id']) ?>"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('/purchases') ?>"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Pembelian -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Header Info -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembelian</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">ID Pembelian</label>
                        <div class="text-gray-900 font-medium">
                            #<?= str_pad($purchase['id'], 4, '0', STR_PAD_LEFT) ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                        <div>
                            <?php
                            $statusClass = '';
                            $statusText = '';
                            switch ($purchase['status']) {
                                case 'pending':
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    $statusText = 'Pending';
                                    break;
                                case 'received':
                                    $statusClass = 'bg-green-100 text-green-800';
                                    $statusText = 'Received';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-red-100 text-red-800';
                                    $statusText = 'Cancelled';
                                    break;
                            }
                            ?>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Pembelian</label>
                        <div class="text-gray-900 font-medium">
                            <?= date('d F Y', strtotime($purchase['purchase_date'])) ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Nama Pembeli</label>
                        <div class="text-gray-900 font-medium">
                            <?= esc($purchase['buyer_name']) ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Dibuat</label>
                        <div class="text-gray-900">
                            <?= date('d F Y H:i', strtotime($purchase['created_at'])) ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Terakhir Diperbarui</label>
                        <div class="text-gray-900">
                            <?= date('d F Y H:i', strtotime($purchase['updated_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Pembelian -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Item Pembelian</h2>

                <?php if (!empty($items)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-2 font-medium text-gray-700">Produk</th>
                                    <th class="text-center py-3 px-2 font-medium text-gray-700">Kuantitas</th>
                                    <th class="text-right py-3 px-2 font-medium text-gray-700">Harga</th>
                                    <th class="text-right py-3 px-2 font-medium text-gray-700">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 px-2">
                                            <div class="font-medium text-gray-900">
                                                <?= esc($item['product_name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Kode: <?= esc($item['product_code']) ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-2 text-center">
                                            <?= number_format($item['quantity'], 2) ?>
                                        </td>
                                        <td class="py-3 px-2 text-right">
                                            Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                        </td>
                                        <td class="py-3 px-2 text-right font-medium">
                                            Rp <?= number_format($item['total'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-300 bg-gray-50">
                                    <td colspan="3" class="py-4 px-2 text-right font-bold text-gray-900">
                                        Total Pembelian:
                                    </td>
                                    <td class="py-4 px-2 text-right font-bold text-xl text-gray-900">
                                        Rp <?= number_format($purchase['total_amount'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-box-open text-3xl mb-2"></i>
                        <p>Tidak ada item dalam pembelian ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Info Vendor -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Vendor</h3>

                <?php if (isset($vendor) && $vendor): ?>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Nama Vendor</label>
                            <div class="text-gray-900 font-medium">
                                <?= esc($vendor['name']) ?>
                            </div>
                        </div>

                        <?php if (!empty($vendor['address'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Alamat</label>
                                <div class="text-gray-900">
                                    <?= esc($vendor['address']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($vendor['phone'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Telepon</label>
                                <div class="text-gray-900">
                                    <a href="tel:<?= esc($vendor['phone']) ?>" class="text-blue-600 hover:text-blue-800">
                                        <?= esc($vendor['phone']) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($vendor['email'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                                <div class="text-gray-900">
                                    <a href="mailto:<?= esc($vendor['email']) ?>" class="text-blue-600 hover:text-blue-800">
                                        <?= esc($vendor['email']) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">Informasi vendor tidak tersedia</p>
                <?php endif; ?>
            </div>

            <!-- Ringkasan -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan</h3>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Item:</span>
                        <span class="font-medium"><?= count($items) ?> item</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Kuantitas:</span>
                        <span class="font-medium">
                            <?= number_format(array_sum(array_column($items, 'quantity')), 2) ?>
                        </span>
                    </div>

                    <div class="border-t pt-3">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total Nilai:</span>
                            <span class="text-green-600">
                                Rp <?= number_format($purchase['total_amount'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aksi -->
            <?php if ($purchase['status'] === 'pending'): ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>

                    <div class="space-y-3">
                        <a href="<?= base_url('/purchases/edit/' . $purchase['id']) ?>"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center px-4 py-2 rounded-lg transition-colors block">
                            <i class="fas fa-edit mr-2"></i>Edit Pembelian
                        </a>

                        <button onclick="confirmReceive()"
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-check mr-2"></i>Tandai Diterima
                        </button>

                        <button onclick="confirmCancel()"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Batalkan
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function confirmReceive() {
        if (confirm('Apakah Anda yakin ingin menandai pembelian ini sebagai diterima?')) {
            // Implementasi update status ke received
            window.location.href = '<?= base_url('/purchases/update-status/' . $purchase['id'] . '/received') ?>';
        }
    }

    function confirmCancel() {
        if (confirm('Apakah Anda yakin ingin membatalkan pembelian ini?')) {
            // Implementasi update status ke cancelled
            window.location.href = '<?= base_url('/purchases/update-status/' . $purchase['id'] . '/cancelled') ?>';
        }
    }
</script>
<?= $this->endSection() ?>