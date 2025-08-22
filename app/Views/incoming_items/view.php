<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detail Barang Masuk</h1>
            <p class="mt-2 text-gray-600">Informasi lengkap transaksi penerimaan barang</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= base_url('/incoming-items') ?>"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
            <a href="<?= base_url('/incoming-items/receipt/' . $incoming_item['id']) ?>"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-print mr-2"></i>
                Cetak Bukti
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detail Transaksi -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm">
                <!-- Header Card -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Informasi Transaksi</h2>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- ID Transaksi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">ID Transaksi</label>
                            <p class="text-lg font-semibold text-gray-900">#<?= str_pad($incoming_item['id'], 6, '0', STR_PAD_LEFT) ?></p>
                        </div>

                        <!-- Tanggal & Waktu -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal & Waktu</label>
                            <p class="text-lg font-semibold text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($incoming_item['date'])) ?>
                            </p>
                        </div>

                        <!-- Produk -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Produk</label>
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                                <div class="bg-blue-100 rounded-full p-3">
                                    <i class="fas fa-box text-blue-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900"><?= esc($incoming_item['product_name']) ?></h3>
                                    <p class="text-sm text-gray-600">Kode: <?= esc($incoming_item['product_code']) ?></p>
                                    <p class="text-sm text-gray-600">Kategori: <?= esc($incoming_item['category_name']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Jumlah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Jumlah Diterima</label>
                            <div class="flex items-center space-x-2">
                                <span class="text-2xl font-bold text-green-600">
                                    <?= number_format($incoming_item['quantity'], 0) ?>
                                </span>
                                <span class="text-lg text-gray-600"><?= esc($incoming_item['unit']) ?></span>
                            </div>
                        </div>

                        <!-- User Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Diinput oleh</label>
                            <div class="flex items-center space-x-2">
                                <div class="bg-gray-100 rounded-full p-2">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                                <span class="text-lg font-medium text-gray-900"><?= esc($incoming_item['user_name'] ?? 'Unknown') ?></span>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <?php if (!empty($incoming_item['notes'])): ?>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Catatan</label>
                                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-gray-700"><?= esc($incoming_item['notes']) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Informasi Pembelian -->
            <?php if (!empty($incoming_item['purchase_number'])): ?>
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Pembelian</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Pembelian</label>
                                <p class="text-lg font-semibold text-blue-600">
                                    <a href="<?= base_url('/purchases/view/' . $incoming_item['purchase_number']) ?>"
                                        class="hover:underline">
                                        #<?= str_pad($incoming_item['purchase_number'], 6, '0', STR_PAD_LEFT) ?>
                                    </a>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Vendor</label>
                                <p class="text-lg font-medium text-gray-900"><?= esc($incoming_item['vendor_name']) ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Pembelian</label>
                                <p class="text-lg font-medium text-gray-900">
                                    <?= date('d/m/Y', strtotime($incoming_item['purchase_date'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Status</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-3">
                        <div class="bg-green-100 rounded-full p-2">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <span class="text-lg font-medium text-green-600">Barang Diterima</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        Stok produk telah bertambah <?= number_format($incoming_item['quantity'], 0) ?> <?= esc($incoming_item['unit']) ?>
                    </p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Aksi Cepat</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="<?= base_url('/incoming-items/edit/' . $incoming_item['id']) ?>"
                        class="flex items-center w-full px-4 py-2 text-left text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <i class="fas fa-edit mr-3"></i>
                        Edit Transaksi
                    </a>
                    <a href="<?= base_url('/incoming-items/receipt/' . $incoming_item['id']) ?>"
                        class="flex items-center w-full px-4 py-2 text-left text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fas fa-print mr-3"></i>
                        Cetak Bukti
                    </a>
                    <a href="<?= base_url('/incoming-items/history/' . $incoming_item['product_id']) ?>"
                        class="flex items-center w-full px-4 py-2 text-left text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fas fa-history mr-3"></i>
                        Riwayat Produk
                    </a>
                    <?php if (session()->get('user_role') === 'admin'): ?>
                        <a href="<?= base_url('/incoming-items/delete/' . $incoming_item['id']) ?>"
                            class="flex items-center w-full px-4 py-2 text-left text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok produk akan disesuaikan.')">
                            <i class="fas fa-trash mr-3"></i>
                            Hapus Transaksi
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Timeline</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="bg-green-100 rounded-full p-2 mt-1">
                                <i class="fas fa-plus text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Transaksi Dibuat</p>
                                <p class="text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($incoming_item['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                        <?php if ($incoming_item['updated_at'] != $incoming_item['created_at']): ?>
                            <div class="flex items-start space-x-3">
                                <div class="bg-blue-100 rounded-full p-2 mt-1">
                                    <i class="fas fa-edit text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Terakhir Diperbarui</p>
                                    <p class="text-sm text-gray-500">
                                        <?= date('d/m/Y H:i', strtotime($incoming_item['updated_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>