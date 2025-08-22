<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Barang Masuk</h1>
        <p class="text-gray-600">Riwayat transaksi barang masuk untuk produk: <strong><?= esc($product['name']) ?></strong></p>
    </div>
    <div class="flex space-x-3">
        <a href="<?= base_url('/products/view/' . $product['id']) ?>"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center">
            <i class="fas fa-box mr-2"></i>
            Lihat Produk
        </a>
        <a href="<?= base_url('/incoming-items') ?>"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali
        </a>
    </div>
</div>

<!-- Product Info Card -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="bg-blue-100 rounded-lg p-3 mr-4">
                <i class="fas fa-box text-blue-600 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900"><?= esc($product['name']) ?></h2>
                <p class="text-gray-500"><?= esc($product['code']) ?> • <?= esc($product['category_name']) ?></p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">Stok Saat Ini</p>
            <p class="text-2xl font-bold text-green-600"><?= number_format($product['current_stock']) ?> <?= esc($product['unit']) ?></p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                <p class="text-2xl font-bold text-gray-900"><?= count($history) ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-list text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Masuk</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php
                    $totalIncoming = 0;
                    foreach ($history as $item) {
                        $totalIncoming += $item['quantity'];
                    }
                    echo number_format($totalIncoming);
                    ?>
                </p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-arrow-down text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Rata-rata per Transaksi</p>
                <p class="text-2xl font-bold text-gray-900">
                    <?php
                    $avgIncoming = count($history) > 0 ? $totalIncoming / count($history) : 0;
                    echo number_format($avgIncoming, 1);
                    ?>
                </p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Transaksi Terbaru</p>
                <p class="text-lg font-bold text-gray-900">
                    <?php
                    if (!empty($history)) {
                        echo date('d M Y', strtotime($history[0]['date']));
                    } else {
                        echo '-';
                    }
                    ?>
                </p>
            </div>
            <div class="bg-orange-100 rounded-full p-3">
                <i class="fas fa-calendar text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- History Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Riwayat Transaksi Barang Masuk</h3>
            <div class="flex items-center space-x-4">
                <button onclick="exportHistory()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($history)): ?>
        <div class="text-center py-12">
            <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Belum ada riwayat barang masuk</p>
            <p class="text-gray-400">Riwayat transaksi akan muncul di sini setelah ada barang masuk</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuantitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($history as $index => $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $index + 1 ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= date('d M Y', strtotime($item['date'])) ?></div>
                                <div class="text-xs text-gray-500"><?= date('H:i', strtotime($item['date'])) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-green-600">
                                        +<?= number_format($item['quantity']) ?> <?= esc($item['unit']) ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $item['purchase_number'] ? 'PO-' . $item['purchase_number'] : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= esc($item['vendor_name'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= esc($item['user_name']) ?>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <?php if (!empty($item['notes'])): ?>
                                    <div class="text-sm text-gray-900 truncate" title="<?= esc($item['notes']) ?>">
                                        <?= esc($item['notes']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="<?= base_url('/incoming-items/view/' . $item['id']) ?>"
                                        class="text-blue-600 hover:text-blue-900 transition duration-150 ease-in-out"
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('/incoming-items/edit/' . $item['id']) ?>"
                                        class="text-indigo-600 hover:text-indigo-900 transition duration-150 ease-in-out"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Chart Section -->
<?php if (!empty($history)): ?>
    <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik Barang Masuk</h3>
        <div class="h-64">
            <canvas id="incomingChart"></canvas>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($history)): ?>
            // Prepare chart data
            const chartData = <?= json_encode(array_reverse($history)) ?>;
            const labels = chartData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short'
                });
            });
            const quantities = chartData.map(item => parseFloat(item.quantity));

            // Create chart
            const ctx = document.getElementById('incomingChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Kuantitas Barang Masuk',
                        data: quantities,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.parsed.y.toLocaleString()} <?= esc($product['unit']) ?>`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Kuantitas (<?= esc($product['unit']) ?>)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tanggal'
                            }
                        }
                    }
                }
            });
        <?php endif; ?>
    });

    function exportHistory() {
        // Create CSV content
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "No,Tanggal,Waktu,Kuantitas,Unit,Purchase Order,Vendor,User,Catatan\n";

        const history = <?= json_encode($history) ?>;
        history.forEach((item, index) => {
            const date = new Date(item.date);
            const dateStr = date.toLocaleDateString('id-ID');
            const timeStr = date.toLocaleTimeString('id-ID');
            const po = item.purchase_number ? `PO-${item.purchase_number}` : '-';
            const vendor = item.vendor_name || '-';
            const notes = (item.notes || '').replace(/"/g, '""'); // Escape quotes

            csvContent += `${index + 1},"${dateStr}","${timeStr}","${item.quantity}","${item.unit}","${po}","${vendor}","${item.user_name}","${notes}"\n`;
        });

        // Download CSV
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `riwayat_barang_masuk_${<?= json_encode($product['code']) ?>}_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<?= $this->endSection() ?>