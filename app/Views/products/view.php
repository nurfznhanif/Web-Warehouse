<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Detail Produk</h1>
        <p class="text-gray-600 mt-1"><?= esc($product['name']) ?></p>
    </div>
    <div class="flex space-x-3">
        <a href="<?= base_url('/products') ?>" 
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Alert Status Stok -->
<?php 
$minStock = $product['min_stock'] ?? 10;
if ($product['stock'] <= 0): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Peringatan:</strong> Produk ini sudah habis!
    </div>
</div>
<?php elseif ($product['stock'] <= $minStock): ?>
<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Peringatan:</strong> Stok produk ini sudah rendah (<?= number_format($product['stock']) ?> tersisa)
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informasi Produk -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Produk</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Kode Barang</label>
                    <div class="text-lg font-mono bg-gray-50 px-3 py-2 rounded border">
                        <?= esc($product['code']) ?>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Nama Barang</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?= esc($product['name']) ?>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Kategori</label>
                    <div class="text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-tag mr-1"></i>
                            <?= esc($product['category_name'] ?? 'Tidak ada kategori') ?>
                        </span>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Satuan</label>
                    <div class="text-gray-900 font-medium">
                        <?= esc($product['unit']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Transaksi -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Transaksi</h2>
                <div class="flex space-x-2">
                    <button onclick="showTransactionHistory('incoming')" 
                            class="bg-green-100 text-green-800 px-3 py-1 rounded text-sm hover:bg-green-200 transition-colors">
                        <i class="fas fa-arrow-down mr-1"></i>Masuk
                    </button>
                    <button onclick="showTransactionHistory('outgoing')" 
                            class="bg-red-100 text-red-800 px-3 py-1 rounded text-sm hover:bg-red-200 transition-colors">
                        <i class="fas fa-arrow-up mr-1"></i>Keluar
                    </button>
                    <button onclick="showTransactionHistory('all')" 
                            class="bg-gray-100 text-gray-800 px-3 py-1 rounded text-sm hover:bg-gray-200 transition-colors">
                        Semua
                    </button>
                </div>
            </div>
            
            <div id="transaction-history" class="space-y-3">
                <!-- Transaksi akan dimuat via AJAX -->
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-history text-3xl mb-2"></i>
                    <p>Klik tombol di atas untuk melihat riwayat transaksi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Informasi -->
    <div class="space-y-6">
        <!-- Status Stok -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Stok</h3>
            
            <div class="space-y-4">
                <div class="text-center">
                    <?php 
                    $stockStatus = 'Stok Aman';
                    $stockClass = 'text-green-600';
                    $stockIcon = 'fa-check-circle';
                    
                    if ($product['stock'] <= 0) {
                        $stockStatus = 'Habis';
                        $stockClass = 'text-red-600';
                        $stockIcon = 'fa-times-circle';
                    } elseif ($product['stock'] <= $minStock) {
                        $stockStatus = 'Stok Rendah';
                        $stockClass = 'text-yellow-600';
                        $stockIcon = 'fa-exclamation-triangle';
                    }
                    ?>
                    <div class="text-3xl font-bold <?= $stockClass ?> mb-2">
                        <?= number_format($product['stock'], 0, ',', '.') ?>
                    </div>
                    <div class="text-gray-600"><?= esc($product['unit']) ?></div>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $stockClass ?> bg-gray-100">
                            <i class="fas <?= $stockIcon ?> mr-1"></i>
                            <?= $stockStatus ?>
                        </span>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Minimum Stok:</span>
                        <span class="font-medium"><?= number_format($minStock) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
            
            <div class="space-y-3">
                <a href="<?= base_url('/incoming-items/create?product_id=' . $product['id']) ?>" 
                   class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-down mr-2"></i>Tambah Stok
                </a>
                
                <a href="<?= base_url('/outgoing-items/create?product_id=' . $product['id']) ?>" 
                   class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-up mr-2"></i>Kurangi Stok
                </a>
                
                <a href="<?= base_url('/products/edit/' . $product['id']) ?>" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit Produk
                </a>
                
                <?php if (session()->get('role') === 'admin'): ?>
                <button onclick="deleteProduct()" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                    <i class="fas fa-trash mr-2"></i>Hapus Produk
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Informasi Audit -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">ID Produk:</span>
                    <span class="font-mono">#<?= $product['id'] ?></span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Dibuat:</span>
                    <span><?= date('d M Y', strtotime($product['created_at'])) ?></span>
                </div>
                
                <?php if (isset($product['updated_at']) && $product['updated_at']): ?>
                <div class="flex justify-between">
                    <span class="text-gray-600">Diupdate:</span>
                    <span><?= date('d M Y', strtotime($product['updated_at'])) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($product['last_transaction_date'])): ?>
                <div class="flex justify-between">
                    <span class="text-gray-600">Transaksi Terakhir:</span>
                    <span><?= date('d M Y', strtotime($product['last_transaction_date'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistik Singkat -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Masuk:</span>
                    <span class="font-medium text-green-600" id="total-incoming">-</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Keluar:</span>
                    <span class="font-medium text-red-600" id="total-outgoing">-</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Net Stock:</span>
                    <span class="font-medium" id="net-stock">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentTransactionType = 'all';

// Load transaction history
function showTransactionHistory(type) {
    currentTransactionType = type;
    const container = document.getElementById('transaction-history');
    
    // Show loading
    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
            <p class="text-gray-500">Memuat riwayat transaksi...</p>
        </div>
    `;
    
    // Update button states
    document.querySelectorAll('button[onclick^="showTransactionHistory"]').forEach(btn => {
        btn.classList.remove('bg-blue-100', 'text-blue-800');
        btn.classList.add('bg-gray-100', 'text-gray-800');
    });
    event.target.classList.remove('bg-gray-100', 'text-gray-800');
    event.target.classList.add('bg-blue-100', 'text-blue-800');
    
    // Fetch data
    fetch(`<?= base_url('/api/products/' . $product['id'] . '/transactions') ?>?type=${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTransactions(data.transactions);
                updateStatistics(data.statistics);
            } else {
                container.innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                        <p>Gagal memuat data transaksi</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                    <p>Terjadi kesalahan saat memuat data</p>
                </div>
            `;
        });
}

// Display transactions
function displayTransactions(transactions) {
    const container = document.getElementById('transaction-history');
    
    if (transactions.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p>Tidak ada riwayat transaksi</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    transactions.forEach(transaction => {
        const isIncoming = transaction.type === 'incoming';
        const iconClass = isIncoming ? 'fa-arrow-down text-green-600' : 'fa-arrow-up text-red-600';
        const bgClass = isIncoming ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
        const textClass = isIncoming ? 'text-green-800' : 'text-red-800';
        
        html += `
            <div class="border rounded-lg p-4 ${bgClass}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas ${iconClass} mr-3"></i>
                        <div>
                            <div class="font-medium ${textClass}">
                                ${isIncoming ? 'Barang Masuk' : 'Barang Keluar'}
                            </div>
                            <div class="text-sm text-gray-600">
                                ${formatDate(transaction.date)}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold ${textClass}">
                            ${isIncoming ? '+' : '-'}${formatNumber(transaction.quantity)} ${transaction.unit}
                        </div>
                        ${transaction.description ? `<div class="text-sm text-gray-600">${transaction.description}</div>` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Update statistics
function updateStatistics(stats) {
    document.getElementById('total-incoming').textContent = formatNumber(stats.total_incoming || 0);
    document.getElementById('total-outgoing').textContent = formatNumber(stats.total_outgoing || 0);
    document.getElementById('net-stock').textContent = formatNumber(stats.net_stock || 0);
}

// Format number
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Delete product
function deleteProduct() {
    if (confirm('Apakah Anda yakin ingin menghapus produk "<?= esc($product['name']) ?>"?\n\nPerhatian: Semua data transaksi yang terkait dengan produk ini juga akan terpengaruh.')) {
        if (confirm('Konfirmasi sekali lagi. Tindakan ini tidak dapat dibatalkan!')) {
            window.location.href = '<?= base_url('/products/delete/' . $product['id']) ?>';
        }
    }
}

// Load statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch(`<?= base_url('/api/products/' . $product['id'] . '/statistics') ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatistics(data.statistics);
            }
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
});

// Auto-refresh every 30 seconds
setInterval(function() {
    if (currentTransactionType !== 'all') {
        // Refresh current view
        const button = document.querySelector(`button[onclick="showTransactionHistory('${currentTransactionType}')"]`);
        if (button) {
            button.click();
        }
    }
}, 30000);

// Print functionality
function printProductInfo() {
    const printContent = `
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <h1>Detail Produk</h1>
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Kode Barang</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= esc($product['code']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Nama Barang</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= esc($product['name']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Kategori</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= esc($product['category_name'] ?? 'Tidak ada') ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Satuan</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= esc($product['unit']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Stok Saat Ini</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= number_format($product['stock']) ?> <?= esc($product['unit']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Minimum Stok</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= number_format($minStock) ?></td>
                </tr>
            </table>
            <p style="margin-top: 30px; text-align: center; color: #666;">
                Dicetak pada: ${new Date().toLocaleString('id-ID')}
            </p>
        </div>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
}

// Add print button to quick actions
document.addEventListener('DOMContentLoaded', function() {
    const quickActions = document.querySelector('.space-y-3');
    if (quickActions) {
        const printButton = document.createElement('button');
        printButton.onclick = printProductInfo;
        printButton.className = 'w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors';
        printButton.innerHTML = '<i class="fas fa-print mr-2"></i>Cetak Info';
        quickActions.appendChild(printButton);
    }
});
</script>
<?= $this->endSection() ?>