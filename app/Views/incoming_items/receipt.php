<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Penerimaan Barang - <?= esc($incoming_item['product_name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }

        .document-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .receipt-number {
            font-size: 14px;
            font-weight: bold;
        }

        .receipt-date {
            font-size: 12px;
            color: #666;
        }

        .content-section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #374151;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            color: #111827;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .product-table th,
        .product-table td {
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
        }

        .product-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }

        .quantity-highlight {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
        }

        .notes-section {
            background-color: #fffbeb;
            border: 1px solid #f59e0b;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 40px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
            }

            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }

        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <?php
    // Define format_quantity function inline since it's not defined globally
    if (!function_exists('format_quantity')) {
        function format_quantity($number)
        {
            // Remove trailing zeros and format with thousands separator
            $formatted = number_format((float)$number, 2, '.', ',');
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.');
            return $formatted;
        }
    }
    ?>

    <!-- Print Actions -->
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Cetak
        </button>
        <a href="<?= base_url('/incoming-items/view/' . $incoming_item['id']) ?>" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>

    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">VADHANA WAREHOUSE</div>
            <div class="company-address">
                Sistem Manajemen Gudang<br>
                Jl. Contoh No. 123, Kota ABC 12345<br>
                Telp: (021) 123-4567 | Email: info@warehouse.com
            </div>
            <div class="document-title">Bukti Penerimaan Barang</div>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div>
                <div class="receipt-number">No. BPB: <?= str_pad($incoming_item['id'], 6, '0', STR_PAD_LEFT) ?></div>
                <div class="receipt-date">Tanggal: <?= date('d/m/Y', strtotime($incoming_item['date'])) ?></div>
            </div>
            <div>
                <div class="receipt-date">Waktu: <?= date('H:i:s', strtotime($incoming_item['date'])) ?></div>
                <div class="receipt-date">User: <?= esc($incoming_item['user_name'] ?? 'System') ?></div>
            </div>
        </div>

        <!-- Purchase Information -->
        <?php if (!empty($incoming_item['purchase_number'])): ?>
            <div class="content-section">
                <div class="section-title">Informasi Pembelian</div>
                <div class="info-grid">
                    <div>
                        <div class="info-item">
                            <span class="info-label">No. Pembelian:</span>
                            <span class="info-value">#<?= str_pad($incoming_item['purchase_number'], 6, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Vendor:</span>
                            <span class="info-value"><?= esc($incoming_item['vendor_name']) ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="info-item">
                            <span class="info-label">Tgl. Pembelian:</span>
                            <span class="info-value"><?= date('d/m/Y', strtotime($incoming_item['purchase_date'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Product Information -->
        <div class="content-section">
            <div class="section-title">Detail Produk</div>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Jumlah Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= esc($incoming_item['product_code']) ?></td>
                        <td><?= esc($incoming_item['product_name']) ?></td>
                        <td><?= esc($incoming_item['category_name']) ?></td>
                        <td><?= esc($incoming_item['unit']) ?></td>
                        <td class="quantity-highlight"><?= format_quantity($incoming_item['quantity']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Notes -->
        <?php if (!empty($incoming_item['notes'])): ?>
            <div class="notes-section">
                <div class="section-title">Catatan</div>
                <p><?= esc($incoming_item['notes']) ?></p>
            </div>
        <?php endif; ?>

        <!-- Summary -->
        <div class="content-section">
            <div class="section-title">Ringkasan</div>
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <span class="info-label">Total Item:</span>
                        <span class="info-value">1 jenis produk</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Kuantitas:</span>
                        <span class="info-value quantity-highlight"><?= format_quantity($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?></span>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value" style="color: #059669; font-weight: bold;">✓ Diterima</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Stok Bertambah:</span>
                        <span class="info-value"><?= format_quantity($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">Yang Menerima</div>
                <div class="signature-line"></div>
                <div>( <?= esc($incoming_item['user_name'] ?? '________________') ?> )</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Mengetahui</div>
                <div class="signature-line"></div>
                <div>( ________________ )</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis pada <?= date('d/m/Y H:i:s') ?></p>
            <p>Bukti Penerimaan Barang - Warehouse Management System</p>
        </div>
    </div>
</body>

</html>