<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Products -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Produk</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_products']) ?></p>
                <p class="text-sm text-gray-500"><?= $stats['total_categories'] ?> kategori</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-box text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Stok Rendah</p>
                <p class="text-2xl font-bold text-yellow-600"><?= number_format($stats['low_stock_products']) ?></p>
                <p class="text-sm text-gray-500"><?= $stats['out_of_stock_products'] ?> habis</p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Today's Incoming -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Barang Masuk Hari Ini</p>
                <p class="text-2xl font-bold text-green-600"><?= number_format($stats['today_incoming']) ?></p>
                <p class="text-sm text-gray-500"><?= $stats['monthly_incoming'] ?> bulan ini</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-arrow-down text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Today's Outgoing -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Barang Keluar Hari Ini</p>
                <p class="text-2xl font-bold text-red-600"><?= number_format($stats['today_outgoing']) ?></p>
                <p class="text-sm text-gray-500"><?= $stats['monthly_outgoing'] ?> bulan ini</p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-arrow-up text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Transaction Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Transaksi 7 Hari Terakhir</h3>
            <select id="chart-period" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                <option value="7days">7 Hari</option>
                <option value="30days">30 Hari</option>
                <option value="12months">12 Bulan</option>
            </select>
        </div>
        <div class="h-64">
            <canvas id="transactionChart"></canvas>
        </div>
    </div>

    <!-- Stock Status Pie Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Stok Produk</h3>
        <div class="h-64">
            <canvas id="stockChart"></canvas>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="w-4 h-4 bg-green-500 rounded mx-auto mb-1"></div>
                <p class="text-sm text-gray-600">Stok Aman</p>
                <p class="font-semibold"><?= $chart_data['stock_status']['in_stock'] ?></p>
            </div>
            <div>
                <div class="w-4 h-4 bg-yellow-500 rounded mx-auto mb-1"></div>
                <p class="text-sm text-gray-600">Stok Rendah</p>
                <p class="font-semibold"><?= $chart_data['stock_status']['low_stock'] ?></p>
            </div>
            <div>
                <div class="w-4 h-4 bg-red-500 rounded mx-auto mb-1"></div>
                <p class="text-sm text-gray-600">Habis</p>
                <p class="font-semibold"><?= $chart_data['stock_status']['out_of_stock'] ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions and Alerts -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Recent Incoming Items -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Barang Masuk Terbaru</h3>
            <a href="<?= base_url('/incoming-items') ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (empty($recent_incoming)): ?>
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p>Belum ada transaksi barang masuk</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recent_incoming as $item): ?>
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900"><?= esc($item['product_name']) ?></p>
                            <p class="text-sm text-gray-600"><?= number_format($item['quantity']) ?> <?= esc($item['unit']) ?></p>
                            <p class="text-xs text-gray-500"><?= date('d M Y H:i', strtotime($item['date'])) ?></p>
                        </div>
                        <div class="text-green-600">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Outgoing Items -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Barang Keluar Terbaru</h3>
            <a href="<?= base_url('/outgoing-items') ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (empty($recent_outgoing)): ?>
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p>Belum ada transaksi barang keluar</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recent_outgoing as $item): ?>
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900"><?= esc($item['product_name']) ?></p>
                            <p class="text-sm text-gray-600"><?= number_format($item['quantity']) ?> <?= esc($item['unit']) ?></p>
                            <p class="text-xs text-gray-500"><?= date('d M Y H:i', strtotime($item['date'])) ?></p>
                        </div>
                        <div class="text-red-600">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Low Stock Alert -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Peringatan Stok Rendah</h3>
            <a href="<?= base_url('/products?filter=low_stock') ?>" class="text-yellow-600 hover:text-yellow-800 text-sm">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (empty($low_stock_products)): ?>
            <div class="text-center text-green-500 py-8">
                <i class="fas fa-check-circle text-3xl mb-2"></i>
                <p>Semua produk stok aman</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($low_stock_products, 0, 5) as $product): ?>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900"><?= esc($product['name']) ?></p>
                            <p class="text-sm text-yellow-600">
                                Stok: <?= number_format($product['stock']) ?> <?= esc($product['unit']) ?>
                                (Min: <?= number_format($product['min_stock']) ?>)
                            </p>
                            <p class="text-xs text-gray-500"><?= esc($product['category_name']) ?></p>
                        </div>
                        <div class="text-yellow-600">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (count($low_stock_products) > 5): ?>
                    <div class="text-center py-2">
                        <p class="text-sm text-gray-600">
                            dan <?= count($low_stock_products) - 5 ?> produk lainnya
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="<?= base_url('/products/create') ?>" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
            <i class="fas fa-plus-circle text-blue-600 text-2xl mb-2"></i>
            <span class="text-sm font-medium text-blue-800">Tambah Produk</span>
        </a>

        <a href="<?= base_url('/incoming-items/create') ?>" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-down text-green-600 text-2xl mb-2"></i>
            <span class="text-sm font-medium text-green-800">Barang Masuk</span>
        </a>

        <a href="<?= base_url('/outgoing-items/create') ?>" class="flex flex-col items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-up text-red-600 text-2xl mb-2"></i>
            <span class="text-sm font-medium text-red-800">Barang Keluar</span>
        </a>

        <a href="<?= base_url('/reports') ?>" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
            <i class="fas fa-chart-bar text-purple-600 text-2xl mb-2"></i>
            <span class="text-sm font-medium text-purple-800">Laporan</span>
        </a>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
    // Transaction Chart
    const transactionCtx = document.getElementById('transactionChart').getContext('2d');
    let transactionChart = new Chart(transactionCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($chart_data['transactions'], 'date')) ?>,
            datasets: [{
                label: 'Barang Masuk',
                data: <?= json_encode(array_column($chart_data['transactions'], 'incoming')) ?>,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Barang Keluar',
                data: <?= json_encode(array_column($chart_data['transactions'], 'outgoing')) ?>,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });

    // Stock Status Chart
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    const stockChart = new Chart(stockCtx, {
        type: 'doughnut',
        data: {
            labels: ['Stok Aman', 'Stok Rendah', 'Habis'],
            datasets: [{
                data: [
                    <?= $chart_data['stock_status']['in_stock'] ?>,
                    <?= $chart_data['stock_status']['low_stock'] ?>,
                    <?= $chart_data['stock_status']['out_of_stock'] ?>
                ],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Chart period change handler
    document.getElementById('chart-period').addEventListener('change', function() {
        const period = this.value;

        fetch(`<?= base_url('/dashboard/getTransactionChart') ?>?period=${period}`)
            .then(response => response.json())
            .then(data => {
                transactionChart.data.labels = data.map(item => item.label);
                transactionChart.data.datasets[0].data = data.map(item => item.incoming);
                transactionChart.data.datasets[1].data = data.map(item => item.outgoing);
                transactionChart.update();
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });

    // Auto refresh stock alerts every 5 minutes
    setInterval(function() {
        fetch('<?= base_url('/dashboard/getStockAlert') ?>')
            .then(response => response.json())
            .then(data => {
                // Update notification count if needed
                const bellIcon = document.querySelector('.fa-bell').parentElement;
                const existingBadge = bellIcon.querySelector('.absolute');

                if (data.count > 0) {
                    if (existingBadge) {
                        existingBadge.textContent = data.count;
                    } else {
                        const badge = document.createElement('span');
                        badge.className = 'absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
                        badge.textContent = data.count;
                        bellIcon.appendChild(badge);
                    }
                } else {
                    if (existingBadge) {
                        existingBadge.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error refreshing stock alerts:', error);
            });
    }, 300000); // 5 minutes

    // Format numbers with thousands separator
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to cards
        const cards = document.querySelectorAll('.bg-white.rounded-lg.shadow-sm');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-md');
            });
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-md');
            });
        });
    });
</script>
<?= $this->endSection() ?>