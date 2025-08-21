<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .receipt-info {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
        }
        
        .label {
            font-weight: bold;
            width: 40%;
        }
        
        .value {
            width: 60%;
            text-align: right;
        }
        
        .product-section {
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            padding: 15px 0;
            margin: 20px 0;
        }
        
        .product-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .quantity-box {
            text-align: center;
            background: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            margin: 10px 0;
        }
        
        .quantity-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #666;
        }
        
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .receipt-container {
                border: none;
                max-width: none;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">VADHANA WAREHOUSE</div>
            <div style="font-size: 10px; color: #666;">Sistem Manajemen Gudang</div>
            <div class="receipt-title">RECEIPT BARANG MASUK</div>
        </div>
        
        <!-- Receipt Info -->
        <div class="receipt-info">
            <div class="info-row">
                <span class="label">Receipt #:</span>
                <span class="value"><?= str_pad($item['id'], 6, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Tanggal:</span>
                <span class="value"><?= date('d/m/Y H:i', strtotime($item['date'])) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Penanggung Jawab:</span>
                <span class="value"><?= esc($item['user_name'] ?? 'Unknown') ?></span>
            </div>
            <?php if (!empty($item['purchase_number'])): ?>
            <div class="info-row">
                <span class="label">Purchase Order:</span>
                <span class="value">PO #<?= $item['purchase_number'] ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($item['vendor_name'])): ?>
            <div class="info-row">
                <span class="label">Vendor:</span>
                <span class="value"><?= esc($item['vendor_name']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Section -->
        <div class="product-section">
            <div class="product-name"><?= esc($item['product_name']) ?></div>
            <div style="font-size: 10px; color: #666; margin-bottom: 10px;">
                Kode: <?= esc($item['product_code']) ?> | 
                Kategori: <?= esc($item['category_name'] ?? '-') ?>
            </div>
            
            <div class="quantity-box">
                <div style="font-size: 10px; margin-bottom: 5px;">QUANTITY DITERIMA</div>
                <div class="quantity-value">
                    <?= number_format($item['quantity']) ?> <?= esc($item['unit']) ?>
                </div>
            </div>
        </div>
        
        <!-- Notes -->
        <?php if (!empty($item['notes'])): ?>
        <div style="margin: 15px 0;">
            <div style="font-weight: bold; margin-bottom: 5px;">Catatan:</div>
            <div style="background: #f9f9f9; padding: 8px; border-left: 3px solid #ccc; font-size: 11px;">
                <?= nl2br(esc($item['notes'])) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div style="font-size: 10px; margin-bottom: 5px;">PENERIMA</div>
                <div class="signature-line">
                    <?= esc($item['user_name'] ?? 'Unknown') ?>
                </div>
            </div>
            <div class="signature-box">
                <div style="font-size: 10px; margin-bottom: 5px;">SUPERVISOR</div>
                <div class="signature-line">
                    ( _________________ )
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div>Dicetak pada: <?= date('d/m/Y H:i:s') ?></div>
            <div style="margin-top: 5px;">
                Dokumen ini digenerate otomatis oleh sistem
            </div>
        </div>
        
        <!-- Print Button (hidden when printing) -->
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" 
                    style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <button onclick="window.close()" 
                    style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                Tutup
            </button>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
        
        // Close window after printing
        window.onafterprint = function() {
            // window.close();
        }
    </script>
</body>
</html>