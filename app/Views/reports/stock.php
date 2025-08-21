<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Stok Barang</h1>
            <p class="text-gray-600 mt-1">Lihat stok terkini semua produk di warehouse</p>
        </div>
        <a href="<?= base_url('/reports') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Laporan
        </a>
    </div>
</div>

<!-- Statistics Section -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="bg-blue-100 rounded-full p-3 mr-4">
                <i class="fas fa-boxes text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Produk</p>
                <p class="text-2xl font-bold text-blue-600"><?= number_format($statistics['total_products']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="bg-green-100 rounded-full p-3 mr-4">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Stok Aman</p>
                <p class="text-2xl font-bold text-green-600"><?= number_format($statistics['in_stock']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="bg-yellow-100 rounded-full p-3 mr-4">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Stok Rendah</p>
                <p class="text-2xl font-bold text-yellow-600"><?= number_format($statistics['low_stock']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="bg-red-100 rounded-full p-3 mr-4">
                <i class="fas fa-times-circle text-red-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Habis</p>
                <p class="text-2xl font-bold text-red-600"><?= number_format($statistics['out_of_stock']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="bg-purple-100 rounded-full p-3 mr-4">
                <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Nilai Total</p>
                <p class="text-xl font-bold text-purple-600">Rp <?= number_format($statistics['total_value'], 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div>
                <input type="text"
                    id="search-input"
                    placeholder="Cari produk..."
                    class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <select id="status-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="in_stock">Stok Aman</option>
                    <option value="low_stock">Stok Rendah</option>
                    <option value="out_of_stock">Habis</option>
                </select>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="exportToCSV()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                <i class="fas fa-download mr-2"></i>Export CSV
            </button>
            <button onclick="printReport()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                <i class="fas fa-print mr-2"></i>Print
            </button>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Data Stok Produk</h3>
        <p class="text-sm text-gray-600 mt-1">Update terakhir: <?= date('d M Y H:i:s') ?></p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="stock-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Stok</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Stok</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="stock-tbody">
                <?php foreach ($products as $product): ?>
                    <?php
                    $stock = $product['stock'] ?? 0;
                    $minStock = $product['min_stock'] ?? 10;
                    $price = $product['price'] ?? 0;
                    $stockValue = $stock * $price;

                    $statusClass = 'bg-green-100 text-green-800';
                    $statusText = 'Stok Aman';
                    $statusIcon = 'fas fa-check-circle';
                    $rowClass = 'in_stock';

                    if ($stock <= 0) {
                        $statusClass = 'bg-red-100 text-red-800';
                        $statusText = 'Habis';
                        $statusIcon = 'fas fa-times-circle';
                        $rowClass = 'out_of_stock';
                    } elseif ($stock <= $minStock) {
                        $statusClass = 'bg-yellow-100 text-yellow-800';
                        $statusText = 'Stok Rendah';
                        $statusIcon = 'fas fa-exclamation-triangle';
                        $rowClass = 'low_stock';
                    }
                    ?>
                    <tr class="hover:bg-gray-50 table-row" data-status="<?= $rowClass ?>" data-search="<?= strtolower(esc($product['name'] . ' ' . $product['code'] . ' ' . $product['category_name'])) ?>">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= esc($product['code']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900"><?= esc($product['name']) ?></div>
                                <div class="text-sm text-gray-500"><?= esc($product['unit']) ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= esc($product['category_name']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-lg font-bold <?= $stock <= 0 ? 'text-red-600' : ($stock <= $minStock ? 'text-yellow-600' : 'text-green-600') ?>">
                                <?= number_format($stock) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= number_format($minStock) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp <?= number_format($price, 0, ',', '.') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            Rp <?= number_format($stockValue, 0, ',', '.') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                <i class="<?= $statusIcon ?> mr-1"></i>
                                <?= $statusText ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Search functionality
    document.getElementById('search-input').addEventListener('keyup', function() {
        filterTable();
    });

    // Status filter functionality
    document.getElementById('status-filter').addEventListener('change', function() {
        filterTable();
    });

    function filterTable() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const statusFilter = document.getElementById('status-filter').value;
        const rows = document.querySelectorAll('.table-row');

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            const statusData = row.getAttribute('data-status');

            const matchesSearch = searchData.includes(searchTerm);
            const matchesStatus = !statusFilter || statusData === statusFilter;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function exportToCSV() {
        const table = document.getElementById('stock-table');
        const rows = Array.from(table.querySelectorAll('tr'));

        const csvContent = rows.map(row => {
            const cells = Array.from(row.querySelectorAll('th, td'));
            return cells.map(cell => {
                let text = cell.textContent.trim();
                // Remove icon classes and clean text
                text = text.replace(/fas fa-[a-z-]+/g, '').trim();
                return `"${text.replace(/"/g, '""')}"`;
            }).join(',');
        }).join('\n');

        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const link = document.createElement('a');

        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `laporan-stok-${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    function printReport() {
        // Create a new window for printing
        const printWindow = window.open('', '', 'height=600,width=800');

        const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Laporan Stok Barang</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .header { text-align: center; margin-bottom: 20px; }
                .date { text-align: right; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Laporan Stok Barang</h1>
                <h3>Vadhana Warehouse</h3>
            </div>
            <div class="date">
                Tanggal Cetak: ${new Date().toLocaleDateString('id-ID')}
            </div>
            ${document.getElementById('stock-table').outerHTML}
        </body>
        </html>
    `;

        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }
</script>
<?= $this->endSection() ?>