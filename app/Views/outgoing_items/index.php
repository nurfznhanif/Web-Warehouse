<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Barang Keluar</h1>
    <a href="<?= base_url('/outgoing-items/create') ?>"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
        <i class="fas fa-plus mr-2"></i>Tambah Barang Keluar
    </a>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
                <i class="fas fa-arrow-up text-red-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total Transaksi</p>
                <p class="text-2xl font-bold text-gray-800"><?= $statistics['total_items'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-full">
                <i class="fas fa-calendar-day text-orange-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= $statistics['today_outgoing'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
                <i class="fas fa-calendar-month text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= $statistics['monthly_outgoing'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
                <i class="fas fa-boxes text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total Quantity</p>
                <p class="text-2xl font-bold text-gray-800"><?= $statistics['total_quantity'] ?? 0 ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filter dan Search -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="<?= base_url('/outgoing-items') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cari Transaksi</label>
            <input type="text" name="search" value="<?= esc($search ?? '') ?>"
                placeholder="Nama produk, kode, atau catatan..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
            <input type="date" name="start_date" value="<?= esc($start_date ?? '') ?>"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
            <input type="date" name="end_date" value="<?= esc($end_date ?? '') ?>"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg mr-2 transition-colors">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="<?= base_url('/outgoing-items') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Export Options -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Export Data</h3>
        <div class="flex space-x-2">
            <a href="<?= base_url('/outgoing-items/export?format=csv&start_date=' . urlencode($start_date ?? '') . '&end_date=' . urlencode($end_date ?? '')) ?>"
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                <i class="fas fa-file-csv mr-1"></i>CSV
            </a>
            <a href="<?= base_url('/outgoing-items/export?format=json&start_date=' . urlencode($start_date ?? '') . '&end_date=' . urlencode($end_date ?? '')) ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                <i class="fas fa-file-code mr-1"></i>JSON
            </a>
        </div>
    </div>
</div>

<!-- Tabel Data Barang Keluar -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Produk
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kategori
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Jumlah
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Purchase ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Catatan
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        User
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($outgoing_items)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-arrow-up text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg mb-2">Tidak ada data barang keluar</p>
                                <p class="text-sm">Mulai tambahkan transaksi barang keluar</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($outgoing_items as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= date('d/m/Y', strtotime($item['date'])) ?></div>
                                <div class="text-sm text-gray-500"><?= date('H:i', strtotime($item['date'])) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= esc($item['product_name']) ?></div>
                                <div class="text-sm text-gray-500"><?= esc($item['product_code']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= esc($item['category_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= number_format($item['quantity'], 2) ?></div>
                                <div class="text-sm text-gray-500"><?= esc($item['unit']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($item['purchase_id'])): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        #<?= $item['purchase_id'] ?>
                                    </span>
                                    <?php if (!empty($item['vendor_name'])): ?>
                                        <div class="text-xs text-gray-500"><?= esc($item['vendor_name']) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate" title="<?= esc($item['notes'] ?? '') ?>">
                                    <?= esc($item['notes'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= esc($item['user_name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?= base_url('/outgoing-items/edit/' . $item['id']) ?>"
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (session()->get('role') === 'admin'): ?>
                                        <button onclick="confirmDelete(<?= $item['id'] ?>, '<?= esc($item['product_name']) ?>')"
                                            class="text-red-600 hover:text-red-900 transition-colors">
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
    <?php if (!empty($pager)): ?>
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?= $pager ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Menampilkan
                            <span class="font-medium"><?= (($current_page - 1) * $per_page) + 1 ?></span>
                            sampai
                            <span class="font-medium"><?= min($current_page * $per_page, $total_items) ?></span>
                            dari
                            <span class="font-medium"><?= $total_items ?></span>
                            hasil
                        </p>
                    </div>
                    <div>
                        <?= $pager ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Konfirmasi Hapus</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Apakah Anda yakin ingin menghapus transaksi barang keluar untuk produk <span id="productName" class="font-semibold"></span>?
                    Stok produk akan dikembalikan.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="deleteForm" method="POST" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 mr-2">
                        Hapus
                    </button>
                </form>
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, productName) {
    document.getElementById('productName').textContent = productName;
    document.getElementById('deleteForm').action = '<?= base_url('/outgoing-items/delete/') ?>' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>