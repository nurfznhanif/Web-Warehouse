<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Barang Masuk</h1>
        <p class="mt-2 text-gray-600">Edit data penerimaan barang</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        <form action="<?= base_url('/incoming-items/update/' . $incoming_item['id']) ?>" method="POST">
            <?= csrf_field() ?>

            <div class="p-6 space-y-6">
                <!-- Alert untuk error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <!-- Informasi Pembelian (Read Only) -->
                <?php if (!empty($incoming_item['purchase_number'])): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-medium text-blue-900 mb-2">Informasi Pembelian</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                            <div>
                                <p><strong>Nomor Pembelian:</strong> #<?= esc($incoming_item['purchase_number']) ?></p>
                                <p><strong>Vendor:</strong> <?= esc($incoming_item['vendor_name'] ?? '-') ?></p>
                            </div>
                            <div>
                                <p><strong>Tanggal Pembelian:</strong> <?= date('d/m/Y', strtotime($incoming_item['purchase_date'] ?? '')) ?></p>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-blue-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Data pembelian, produk, dan kuantitas tidak dapat diubah
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Informasi Produk (Read Only) -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Informasi Produk</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <p><strong>Nama Produk:</strong> <?= esc($incoming_item['product_name']) ?></p>
                            <p><strong>Kode Produk:</strong> <?= esc($incoming_item['product_code']) ?></p>
                        </div>
                        <div>
                            <p><strong>Kategori:</strong> <?= esc($incoming_item['category_name']) ?></p>
                            <p><strong>Satuan:</strong> <?= esc($incoming_item['unit']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Informasi Penerimaan (Read Only) -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Detail Penerimaan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
                        <div>
                            <p><strong>Jumlah Diterima:</strong></p>
                            <p class="text-lg font-semibold text-green-600">
                                <?= number_format($incoming_item['quantity'], 0) ?> <?= esc($incoming_item['unit']) ?>
                            </p>
                        </div>
                        <div>
                            <p><strong>Tanggal Penerimaan:</strong></p>
                            <p class="text-lg font-semibold">
                                <?= date('d/m/Y H:i', strtotime($incoming_item['date'])) ?>
                            </p>
                        </div>
                        <div>
                            <p><strong>Diinput oleh:</strong></p>
                            <p class="text-lg font-semibold">
                                <?= esc($incoming_item['user_name'] ?? 'Unknown') ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Catatan (Editable) -->
                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Catatan
                    </label>
                    <textarea name="notes" id="notes" rows="4"
                        placeholder="Catatan tambahan (opsional)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= old('notes', $incoming_item['notes']) ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('notes')): ?>
                        <p class="text-sm text-red-600"><?= $validation->getError('notes') ?></p>
                    <?php endif; ?>
                    <p class="text-sm text-gray-500">Hanya catatan yang dapat diubah</p>
                </div>

                <!-- Informasi Perubahan -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Keterbatasan Edit
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Pembelian terkait tidak dapat diubah</li>
                                    <li>Produk tidak dapat diubah</li>
                                    <li>Kuantitas/jumlah tidak dapat diubah</li>
                                    <li>Tanggal penerimaan tidak dapat diubah</li>
                                    <li>Hanya catatan dan user yang mengubah yang dapat diperbarui</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <a href="<?= base_url('/incoming-items') ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    Update Catatan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>