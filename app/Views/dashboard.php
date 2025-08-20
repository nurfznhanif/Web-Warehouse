<h1 class="text-2xl font-bold mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-2">Total Produk</h2>
        <p class="text-3xl font-bold text-blue-600"><?= $total_products ?></p>
    </div>
    
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-2">Barang Masuk Hari Ini</h2>
        <p class="text-3xl font-bold text-green-600"><?= $today_incoming ?></p>
    </div>
    
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-2">Barang Keluar Hari Ini</h2>
        <p class="text-3xl font-bold text-red-600"><?= $today_outgoing ?></p>
    </div>
</div>

<?php if (!empty($low_stock_products)): ?>
<div class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4 text-red-600">Produk Stok Rendah</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2">Nama Barang</th>
                    <th class="px-4 py-2">Kode</th>
                    <th class="px-4 py-2">Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($low_stock_products as $product): ?>
                <tr>
                    <td class="border px-4 py-2"><?= $product['name'] ?></td>
                    <td class="border px-4 py-2"><?= $product['code'] ?></td>
                    <td class="border px-4 py-2 text-red-600"><?= $product['stock'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>