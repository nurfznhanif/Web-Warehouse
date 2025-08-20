<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Data Barang</h1>
    <a href="<?= base_url('/products/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        Tambah Barang
    </a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2">Kode</th>
                <th class="px-4 py-2">Nama Barang</th>
                <th class="px-4 py-2">Kategori</th>
                <th class="px-4 py-2">Satuan</th>
                <th class="px-4 py-2">Stok</th>
                <th class="px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
            <tr>
                <td colspan="6" class="border px-4 py-4 text-center">Tidak ada data</td>
            </tr>
            <?php else: ?>
            <?php foreach ($products as $product): ?>
            <tr>
                <td class="border px-4 py-2"><?= $product['code'] ?></td>
                <td class="border px-4 py-2"><?= $product['name'] ?></td>
                <td class="border px-4 py-2"><?= $product['category_name'] ?></td>
                <td class="border px-4 py-2"><?= $product['unit'] ?></td>
                <td class="border px-4 py-2"><?= $product['stock'] ?></td>
                <td class="border px-4 py-2">
                    <a href="<?= base_url('/products/edit/' . $product['id']) ?>" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                    <?php if ($isAdmin): ?>
                    <a href="<?= base_url('/products/delete/' . $product['id']) ?>" 
                       onclick="return confirmDelete('Apakah Anda yakin ingin menghapus produk ini?')" 
                       class="text-red-600 hover:text-red-800">Hapus</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>