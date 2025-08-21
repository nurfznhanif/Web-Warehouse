<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="bg-white shadow-sm border-b border-gray-200 mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Pembelian</h1>
                    <p class="mt-1 text-sm text-gray-500">Informasi lengkap pembelian dari vendor <?= esc($purchase['vendor_name']) ?></p>
                </div>
                <div class="flex space-x-3">
                    <a href="<?= base_url('/purchases') ?>" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="<?= base_url('/purchases/edit/' . $purchase['id']) ?>" 
                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <button onclick="printPurchase()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
            <p class="text-sm text-green-700"><?= session()->getFlashdata('success') ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informasi Utama -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Informasi Vendor -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-blue-100 rounded-full mr-4">
                    <i class="fas fa-truck text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Informasi Vendor</h2>
                    <p class="text-sm text-gray-500">Detail vendor yang melakukan penjualan</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Nama Vendor</label>
                    <div class="text-gray-900 font-medium">
                        <?= esc($purchase['vendor_name']) ?>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Nomor Telepon</label>
                    <div class="text-gray-900">
                        <?= esc($purchase['vendor_phone'] ?? 'Tidak tersedia') ?>
                    </div>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Alamat Vendor</label>
                    <div class="text-gray-900">
                        <?= esc($purchase['vendor_address']) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informasi Pembelian -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-green-100 rounded-full mr-4">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Informasi Pembelian</h2>
                    <p class="text-sm text-gray-500">Detail transaksi pembelian</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                    <div>
                        <?php
                        $status = $purchase['status'] ?? 'pending';
                        $statusClass = match($status) {
                            'received' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            default => 'bg-yellow-100 text-yellow-800'
                        };
                        $statusText = match($status) {
                            'received' => 'Diterima',
                            'cancelled' => 'Dibatalkan',
                            default => 'Pending'
                        };
                        ?>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                            <?= $statusText ?>
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($purchase['notes'])): ?>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Catatan</label>
                    <div class="text-gray-900 bg-gray-50 p-3 rounded-md">
                        <?= esc($purchase['notes']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Detail Barang -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-purple-100 rounded-full mr-4">
                    <i class="fas fa-boxes text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Detail Barang yang Dibeli</h2>
                    <p class="text-sm text-gray-500">Daftar item yang dibeli dalam transaksi ini</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spesifikasi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($items)): ?>
                            <?php 
                            $grandTotal = 0;
                            foreach ($items as $index => $item): 
                                $total = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                $grandTotal += $total;
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= $index + 1 ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= esc($item['product_name']) ?></div>
                                        <?php if (!empty($item['product_code'])): ?>
                                            <div class="text-sm text-gray-500">Kode: <?= esc($item['product_code']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= esc($item['specification'] ?? '-') ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= number_format($item['quantity'], 2) ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= esc($item['unit'] ?? 'pcs') ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp <?= number_format($item['unit_price'] ?? 0, 0, ',', '.') ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        Rp <?= number_format($total, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <!-- Total Keseluruhan -->
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="6" class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                    Total Keseluruhan:
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                                    Rp <?= number_format($grandTotal, 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium">Belum ada detail barang</p>
                                        <p class="text-sm">Detail barang akan muncul setelah ditambahkan</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Informasi -->
    <div class="space-y-6">
        <!-- Status dan Aksi -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status & Aksi</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Status Saat Ini</label>
                    <?php
                    $status = $purchase['status'] ?? 'pending';
                    $statusClass = match($status) {
                        'received' => 'bg-green-100 text-green-800 border-green-200',
                        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                        default => 'bg-yellow-100 text-yellow-800 border-yellow-200'
                    };
                    $statusText = match($status) {
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan', 
                        default => 'Pending'
                    };
                    ?>
                    <div class="flex items-center p-3 border rounded-lg <?= $statusClass ?>">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="font-medium"><?= $statusText ?></span>
                    </div>
                </div>
                
                <?php if ($status === 'pending'): ?>
                <div class="space-y-2">
                    <button onclick="updateStatus('received')" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fas fa-check mr-2"></i>Tandai Diterima
                    </button>
                    <button onclick="updateStatus('cancelled')" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fas fa-times mr-2"></i>Batalkan
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Ringkasan -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Total Item:</span>
                    <span class="text-sm font-medium text-gray-900"><?= count($items ?? []) ?> item</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Total Kuantitas:</span>
                    <span class="text-sm font-medium text-gray-900">
                        <?php
                        $totalQty = 0;
                        if (!empty($items)) {
                            foreach ($items as $item) {
                                $totalQty += $item['quantity'] ?? 0;
                            }
                        }
                        echo number_format($totalQty, 2);
                        ?>
                    </span>
                </div>
                
                <div class="flex justify-between pt-3 border-t border-gray-200">
                    <span class="text-sm font-medium text-gray-900">Total Nilai:</span>
                    <span class="text-sm font-bold text-blue-600">
                        Rp <?php
                        $grandTotal = 0;
                        if (!empty($items)) {
                            foreach ($items as $item) {
                                $grandTotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                            }
                        }
                        echo number_format($grandTotal, 0, ',', '.');
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Informasi Sistem -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
            
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-600">ID Pembelian:</span>
                    <div class="font-mono text-gray-900"><?= $purchase['id'] ?></div>
                </div>
                
                <div>
                    <span class="text-gray-600">Dibuat:</span>
                    <div class="text-gray-900">
                        <?= date('d F Y H:i', strtotime($purchase['created_at'])) ?>
                    </div>
                </div>
                
                <?php if (!empty($purchase['updated_at'])): ?>
                <div>
                    <span class="text-gray-600">Terakhir Update:</span>
                    <div class="text-gray-900">
                        <?= date('d F Y H:i', strtotime($purchase['updated_at'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Update Status -->
<div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
            </div>
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Konfirmasi Perubahan Status</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="modalMessage"></p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmBtn" 
                            class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-blue-600">
                        Ya
                    </button>
                    <button onclick="closeStatusModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
function updateStatus(newStatus) {
    const modal = document.getElementById('statusModal');
    const title = document.getElementById('modalTitle');
    const message = document.getElementById('modalMessage');
    const confirmBtn = document.getElementById('confirmBtn');
    
    let statusText = newStatus === 'received' ? 'Diterima' : 'Dibatalkan';
    
    title.textContent = `Ubah Status ke "${statusText}"`;
    message.textContent = `Apakah Anda yakin ingin mengubah status pembelian ini menjadi "${statusText}"?`;
    
    confirmBtn.onclick = function() {
        // Redirect ke endpoint update status
        window.location.href = `<?= base_url('/purchases/update-status/' . $purchase['id']) ?>/${newStatus}`;
    };
    
    modal.classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function printPurchase() {
    const printContent = `
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="margin: 0; color: #1f2937;">DETAIL PEMBELIAN</h1>
                <p style="margin: 5px 0; color: #6b7280;">ID: <?= $purchase['id'] ?></p>
                <p style="margin: 5px 0; color: #6b7280;">Tanggal Cetak: ${new Date().toLocaleDateString('id-ID')}</p>
            </div>
            
            <div style="margin-bottom: 25px;">
                <h3 style="color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">Informasi Vendor</h3>
                <table style="width: 100%; margin-top: 10px;">
                    <tr>
                        <td style="padding: 5px 0; width: 30%; font-weight: bold;">Nama Vendor:</td>
                        <td style="padding: 5px 0;"><?= esc($purchase['vendor_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-weight: bold;">Alamat:</td>
                        <td style="padding: 5px 0;"><?= esc($purchase['vendor_address']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-weight: bold;">Telepon:</td>
                        <td style="padding: 5px 0;"><?= esc($purchase['vendor_phone'] ?? 'Tidak tersedia') ?></td>
                    </tr>
                </table>
            </div>
            
            <div style="margin-bottom: 25px;">
                <h3 style="color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">Informasi Pembelian</h3>
                <table style="width: 100%; margin-top: 10px;">
                    <tr>
                        <td style="padding: 5px 0; width: 30%; font-weight: bold;">Tanggal Pembelian:</td>
                        <td style="padding: 5px 0;"><?= date('d F Y', strtotime($purchase['purchase_date'])) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-weight: bold;">Nama Pembeli:</td>
                        <td style="padding: 5px 0;"><?= esc($purchase['buyer_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-weight: bold;">Status:</td>
                        <td style="padding: 5px 0;"><?php
                        $status = $purchase['status'] ?? 'pending';
                        $statusText = match($status) {
                            'received' => 'Diterima',
                            'cancelled' => 'Dibatalkan',
                            default => 'Pending'
                        };
                        echo $statusText;
                        ?></td>
                    </tr>
                </table>
            </div>
            
            <div style="margin-bottom: 25px;">
                <h3 style="color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">Detail Barang</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #f9fafb;">
                            <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: left;">No</th>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: left;">Nama Barang</th>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: left;">Spesifikasi</th>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: center;">Jumlah</th>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: center;">Satuan</th>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">Harga Satuan</th>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php 
                            $grandTotal = 0;
                            foreach ($items as $index => $item): 
                                $total = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                $grandTotal += $total;
                            ?>
                        <tr>
                            <td style="border: 1px solid #e5e7eb; padding: 8px;"><?= $index + 1 ?></td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px;"><?= esc($item['product_name']) ?></td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px;"><?= esc($item['specification'] ?? '-') ?></td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: center;"><?= number_format($item['quantity'], 2) ?></td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: center;"><?= esc($item['unit'] ?? 'pcs') ?></td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">Rp <?= number_format($item['unit_price'] ?? 0, 0, ',', '.') ?></td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">Rp <?= number_format($total, 0, ',', '.') ?></td>
                        </tr>
                            <?php endforeach; ?>
                        <tr style="background-color: #f9fafb; font-weight: bold;">
                            <td colspan="6" style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">Total Keseluruhan:</td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($purchase['notes'])): ?>
            <div style="margin-bottom: 25px;">
                <h3 style="color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">Catatan</h3>
                <p style="margin-top: 10px; padding: 10px; background-color: #f9fafb; border-radius: 5px;"><?= esc($purchase['notes']) ?></p>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 40px; text-align: center; color: #6b7280; font-size: 12px;">
                <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
            </div>
        </div>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Detail Pembelian - <?= esc($purchase['vendor_name']) ?></title>
                <style>
                    body { margin: 0; padding: 0; }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Close modal when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});
</script>
<?= $this->endSection() ?>