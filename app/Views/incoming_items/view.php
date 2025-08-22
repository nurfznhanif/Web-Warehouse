<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail Barang Masuk</h1>
        <p class="text-gray-600">Detail transaksi barang masuk #<?= $incoming_item['id'] ?></p>
    </div>
    <div class="flex space-x-3">
        <a href="<?= base_url('/incoming-items/edit/' . $incoming_item['id']) ?>"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center">
            <i class="fas fa-edit mr-2"></i>
            Edit
        </a>
        <a href="<?= base_url('/incoming-items') ?>"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Transaksi</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Transaction Details -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">ID Transaksi</label>
                        <p class="mt-1 text-lg font-semibold text-gray-900">#<?= $incoming_item['id'] ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Tanggal & Waktu</label>
                        <p class="mt-1 text-gray-900"><?= date('d M Y, H:i', strtotime($incoming_item['date'])) ?> WIB</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Dicatat Oleh</label>
                        <div class="mt-1 flex items-center">
                            <div class="bg-blue-100 rounded-full p-2 mr-3">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900"><?= esc($incoming_item['user_name']) ?></p>
                                <p class="text-sm text-gray-500">Administrator</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Produk</label>
                        <div class="mt-1">
                            <p class="text-lg font-semibold text-gray-900"><?= esc($incoming_item['product_name']) ?></p>
                            <p class="text-sm text-gray-500"><?= esc($incoming_item['product_code']) ?></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Kategori</label>
                        <span class="inline-flex items-center mt-1 px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?= esc($incoming_item['category_name']) ?>
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Kuantitas</label>
                        <p class="mt-1 text-2xl font-bold text-green-600">
                            <?= number_format($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Purchase Order Info -->
            <?php if (!empty($incoming_item['purchase_id'])): ?>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Purchase Order</h3>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-blue-700">Nomor PO</label>
                                <p class="mt-1 font-semibold text-blue-900">PO-<?= $incoming_item['purchase_number'] ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-700">Vendor</label>
                                <p class="mt-1 font-semibold text-blue-900"><?= esc($incoming_item['vendor_name']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if (!empty($incoming_item['notes'])): ?>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Catatan</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700"><?= nl2br(esc($incoming_item['notes'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Stock Impact -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dampak Stok</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Stok Sebelumnya</span>
                    <span class="font-medium text-gray-900" id="previous-stock">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Barang Masuk</span>
                    <span class="font-medium text-green-600">+<?= number_format($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?></span>
                </div>
                <div class="border-t pt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Stok Sekarang</span>
                        <span class="font-bold text-gray-900" id="current-stock">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
            <div class="space-y-3">
                <a href="<?= base_url('/incoming-items/edit/' . $incoming_item['id']) ?>"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Transaksi
                </a>
                <a href="<?= base_url('/products/view/' . $incoming_item['product_id']) ?>"
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium flex items-center justify-center">
                    <i class="fas fa-box mr-2"></i>
                    Lihat Produk
                </a>
                <button onclick="printTransaction()"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium flex items-center justify-center">
                    <i class="fas fa-print mr-2"></i>
                    Cetak
                </button>
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="bg-green-100 rounded-full p-2 mr-3">
                        <i class="fas fa-plus text-green-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Transaksi Dibuat</p>
                        <p class="text-xs text-gray-500"><?= date('d M Y, H:i', strtotime($incoming_item['created_at'])) ?></p>
                    </div>
                </div>

                <?php if (isset($incoming_item['updated_at']) && $incoming_item['updated_at'] != $incoming_item['created_at']): ?>
                    <div class="flex items-start">
                        <div class="bg-blue-100 rounded-full p-2 mr-3">
                            <i class="fas fa-edit text-blue-600 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Terakhir Diupdate</p>
                            <p class="text-xs text-gray-500"><?= date('d M Y, H:i', strtotime($incoming_item['updated_at'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Print Modal -->
<div id="printModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Cetak Transaksi</h3>
            <div class="space-y-3">
                <button onclick="printReceipt()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Cetak Bukti Transaksi
                </button>
                <button onclick="printLabel()"
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    Cetak Label Produk
                </button>
                <button onclick="closePrintModal()"
                    class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load current stock info
        loadStockInfo();
    });

    function loadStockInfo() {
        // Simulate loading current stock - in real implementation, this would be an AJAX call
        const currentStock = <?= $incoming_item['current_stock'] ?? 0 ?>;
        const quantity = <?= $incoming_item['quantity'] ?>;
        const unit = '<?= esc($incoming_item['unit']) ?>';

        const previousStock = currentStock - quantity;

        document.getElementById('previous-stock').textContent = `${previousStock.toLocaleString()} ${unit}`;
        document.getElementById('current-stock').textContent = `${currentStock.toLocaleString()} ${unit}`;
    }

    function printTransaction() {
        document.getElementById('printModal').classList.remove('hidden');
    }

    function closePrintModal() {
        document.getElementById('printModal').classList.add('hidden');
    }

    function printReceipt() {
        const printContent = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px;">
                <h1 style="margin: 0; font-size: 24px;">BUKTI BARANG MASUK</h1>
                <p style="margin: 5px 0; color: #666;">Warehouse Management System</p>
            </div>
            
            <div style="margin-bottom: 30px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">No. Transaksi:</td>
                        <td style="padding: 8px 0;">#<?= $incoming_item['id'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Tanggal:</td>
                        <td style="padding: 8px 0;"><?= date('d M Y, H:i', strtotime($incoming_item['date'])) ?> WIB</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Dicatat oleh:</td>
                        <td style="padding: 8px 0;"><?= esc($incoming_item['user_name']) ?></td>
                    </tr>
                </table>
            </div>
            
            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
                <h3 style="margin: 0 0 15px 0; color: #333;">Detail Produk</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Produk:</td>
                        <td style="padding: 8px 0;"><?= esc($incoming_item['product_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Kode:</td>
                        <td style="padding: 8px 0;"><?= esc($incoming_item['product_code']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Kategori:</td>
                        <td style="padding: 8px 0;"><?= esc($incoming_item['category_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; font-size: 16px;">Kuantitas:</td>
                        <td style="padding: 8px 0; font-size: 16px; font-weight: bold; color: #16a34a;"><?= number_format($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?></td>
                    </tr>
                </table>
            </div>
            
            <?php if (!empty($incoming_item['purchase_id'])): ?>
            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 30px; background-color: #f8fafc;">
                <h3 style="margin: 0 0 15px 0; color: #333;">Purchase Order</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">No. PO:</td>
                        <td style="padding: 8px 0;">PO-<?= $incoming_item['purchase_number'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Vendor:</td>
                        <td style="padding: 8px 0;"><?= esc($incoming_item['vendor_name']) ?></td>
                    </tr>
                </table>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($incoming_item['notes'])): ?>
            <div style="margin-bottom: 30px;">
                <h3 style="margin: 0 0 10px 0; color: #333;">Catatan:</h3>
                <p style="background-color: #f9fafb; padding: 15px; border-radius: 8px; margin: 0;"><?= nl2br(esc($incoming_item['notes'])) ?></p>
            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p style="margin: 0; color: #666; font-size: 12px;">Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
            </div>
        </div>
    `;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
        <html>
            <head>
                <title>Bukti Barang Masuk #<?= $incoming_item['id'] ?></title>
                <style>
                    body { margin: 0; padding: 20px; }
                    @media print {
                        body { margin: 0; }
                    }
                </style>
            </head>
            <body>
                ${printContent}
                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() {
                            window.close();
                        }
                    }
</script>
</body>

</html>
`);
printWindow.document.close();

closePrintModal();
}

function printLabel() {
const labelContent = `
<div style="font-family: Arial, sans-serif; width: 300px; margin: 0 auto; padding: 20px; border: 2px solid #000;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 18px;">LABEL PRODUK</h2>
    </div>

    <div style="margin-bottom: 15px;">
        <strong>Produk:</strong><br>
        <span style="font-size: 16px;"><?= esc($incoming_item['product_name']) ?></span>
    </div>

    <div style="margin-bottom: 15px;">
        <strong>Kode:</strong> <?= esc($incoming_item['product_code']) ?>
    </div>

    <div style="margin-bottom: 15px;">
        <strong>Kategori:</strong> <?= esc($incoming_item['category_name']) ?>
    </div>

    <div style="margin-bottom: 15px;">
        <strong>Tanggal Masuk:</strong><br>
        <?= date('d M Y', strtotime($incoming_item['date'])) ?>
    </div>

    <div style="text-align: center; margin-top: 20px; padding: 10px; background-color: #f0f0f0; border-radius: 5px;">
        <strong style="font-size: 18px;"><?= number_format($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?></strong>
    </div>

    <div style="text-align: center; margin-top: 15px; font-size: 10px; color: #666;">
        ID: #<?= $incoming_item['id'] ?> | <?= date('d/m/Y H:i') ?>
    </div>
</div>
`;

const printWindow = window.open('', '_blank');
printWindow.document.write(`
<html>

<head>
    <title>Label Produk - <?= esc($incoming_item['product_name']) ?></title>
    <style>
        body {
            margin: 0;
            padding: 20px;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    ${labelContent}
    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            }
        }
    </script>
</body>

</html>
`);
printWindow.document.close();

closePrintModal();
}

// Close modal when clicking outside
document.getElementById('printModal').addEventListener('click', function(e) {
if (e.target === this) {
closePrintModal();
}
});
</script>

<?= $this->endSection() ?>