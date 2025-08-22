<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Barang Masuk #<?= $incoming_item['id'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        /* Header */
        .receipt-header {
            background: #2563eb;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .receipt-header h1 {
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .receipt-header .subtitle {
            font-size: 1.1em;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .receipt-id {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            display: inline-block;
            font-weight: 600;
            font-size: 1.1em;
        }

        /* Content */
        .receipt-content {
            padding: 30px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            text-align: center;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }

        .info-item .label {
            font-size: 0.85em;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-item .value {
            font-size: 1.1em;
            font-weight: 600;
            color: #111827;
        }

        .info-item .sub-value {
            font-size: 0.9em;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Product Section */
        .product-section {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .product-header {
            background: #059669;
            color: white;
            padding: 15px 25px;
            font-weight: 600;
            font-size: 1.1em;
        }

        .product-content {
            padding: 25px;
            background: white;
        }

        .product-details {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 25px;
            align-items: center;
        }

        .product-code {
            background: #1f2937;
            color: white;
            padding: 12px 16px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            text-align: center;
            min-width: 100px;
        }

        .product-info h3 {
            font-size: 1.2em;
            font-weight: 600;
            color: #111827;
            margin-bottom: 5px;
        }

        .product-info .category {
            color: #6b7280;
            font-size: 0.9em;
        }

        .quantity-badge {
            background: #059669;
            color: white;
            padding: 15px 25px;
            border-radius: 25px;
            text-align: center;
            font-weight: bold;
            font-size: 1.2em;
            min-width: 120px;
        }

        /* Purchase Order Section */
        .purchase-section {
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .purchase-section h3 {
            color: #1d4ed8;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .purchase-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .purchase-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .purchase-item .label {
            font-weight: 600;
            color: #374151;
        }

        .purchase-item .value {
            color: #1f2937;
            font-weight: 500;
        }

        /* Notes Section */
        .notes-section {
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .notes-section h3 {
            color: #a16207;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .notes-section p {
            color: #713f12;
            line-height: 1.6;
        }

        /* Stock Impact */
        .stock-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stock-item {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
        }

        .stock-impact {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .stock-status {
            background: #f0fdf4;
            border: 1px solid #86efac;
        }

        .stock-item .icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .stock-item .label {
            font-size: 0.9em;
            color: #374151;
            margin-bottom: 5px;
        }

        .stock-item .value {
            font-size: 1.1em;
            font-weight: bold;
        }

        .stock-impact .value {
            color: #059669;
        }

        .stock-status .value {
            color: #16a34a;
        }

        /* Footer */
        .receipt-footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 25px;
            text-align: center;
            color: #6b7280;
        }

        .timestamp {
            font-size: 0.9em;
            margin-bottom: 20px;
        }

        .signature-area {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            max-width: 500px;
            margin: 0 auto;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            height: 60px;
            border-bottom: 1px solid #9ca3af;
            margin: 20px 0 10px 0;
        }

        .signature-label {
            font-size: 0.9em;
            color: #374151;
            font-weight: 500;
        }

        /* Action Buttons */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-print {
            background: #2563eb;
            color: white;
        }

        .btn-print:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .btn-back:hover {
            background: #4b5563;
            color: white;
            text-decoration: none;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
            }
            
            .receipt-container {
                margin: 0;
                box-shadow: none;
                border-radius: 0;
            }
            
            .no-print {
                display: none !important;
            }

            .receipt-header {
                background: #000 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .product-header,
            .quantity-badge,
            .stock-impact,
            .stock-status {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .receipt-container {
                margin: 10px;
            }

            .receipt-content {
                padding: 20px;
            }

            .product-details {
                grid-template-columns: 1fr;
                gap: 15px;
                text-align: center;
            }

            .stock-section {
                grid-template-columns: 1fr;
            }

            .signature-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .action-buttons {
                position: relative;
                top: auto;
                right: auto;
                justify-content: center;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Action Buttons -->
    <div class="action-buttons no-print">
        <button onclick="window.print()" class="btn btn-print">
            🖨️ Print
        </button>
        <a href="<?= base_url('/incoming-items/view/' . $incoming_item['id']) ?>" class="btn btn-back">
            ← Back
        </a>
    </div>

    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <h1>RECEIPT BARANG MASUK</h1>
            <div class="subtitle">Warehouse Management System</div>
            <div class="receipt-id">ID: #<?= $incoming_item['id'] ?></div>
        </div>

        <!-- Content -->
        <div class="receipt-content">
            <!-- Transaction Info -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Nomor Transaksi</div>
                    <div class="value">#<?= $incoming_item['id'] ?></div>
                </div>
                
                <div class="info-item">
                    <div class="label">Tanggal</div>
                    <div class="value"><?= date('d M Y', strtotime($incoming_item['date'])) ?></div>
                    <div class="sub-value"><?= date('H:i:s', strtotime($incoming_item['date'])) ?> WIB</div>
                </div>
                
                <div class="info-item">
                    <div class="label">Dicatat Oleh</div>
                    <div class="value"><?= esc($incoming_item['user_name']) ?></div>
                    <div class="sub-value">Staff Warehouse</div>
                </div>
            </div>

            <!-- Product Section -->
            <div class="product-section">
                <div class="product-header">
                    📦 DETAIL PRODUK YANG DITERIMA
                </div>
                <div class="product-content">
                    <div class="product-details">
                        <div class="product-code"><?= esc($incoming_item['product_code']) ?></div>
                        <div class="product-info">
                            <h3><?= esc($incoming_item['product_name']) ?></h3>
                            <div class="category">Kategori: <?= esc($incoming_item['category_name']) ?></div>
                        </div>
                        <div class="quantity-badge">
                            <?= number_format($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Order Info -->
            <?php if (!empty($incoming_item['purchase_number'])): ?>
            <div class="purchase-section">
                <h3>📋 Informasi Purchase Order</h3>
                <div class="purchase-grid">
                    <div class="purchase-item">
                        <span class="label">Nomor PO:</span>
                        <span class="value">PO-<?= $incoming_item['purchase_number'] ?></span>
                    </div>
                    <div class="purchase-item">
                        <span class="label">Vendor:</span>
                        <span class="value"><?= esc($incoming_item['vendor_name']) ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if (!empty($incoming_item['notes'])): ?>
            <div class="notes-section">
                <h3>📝 Catatan</h3>
                <p><?= nl2br(esc($incoming_item['notes'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Stock Impact -->
            <div class="stock-section">
                <div class="stock-item stock-impact">
                    <div class="icon">📈</div>
                    <div class="label">Dampak Stok</div>
                    <div class="value">+<?= number_format($incoming_item['quantity']) ?> <?= esc($incoming_item['unit']) ?></div>
                </div>
                
                <div class="stock-item stock-status">
                    <div class="icon">✅</div>
                    <div class="label">Status</div>
                    <div class="value">BERHASIL DITERIMA</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="timestamp">
                Dicetak pada: <?= date('d M Y, H:i:s') ?> WIB
            </div>
            <div style="font-size: 0.9em;">
                Receipt ini adalah bukti sah penerimaan barang di warehouse
            </div>
            
            <div class="signature-area">
                <div class="signature-grid">
                    <div class="signature-box">
                        <div>Diterima oleh</div>
                        <div class="signature-line"></div>
                        <div class="signature-label">Staff Warehouse</div>
                    </div>
                    <div class="signature-box">
                        <div>Disetujui oleh</div>
                        <div class="signature-line"></div>
                        <div class="signature-label">Supervisor</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print when accessed with ?print=1
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        }

        // Handle print completion
        window.onafterprint = function() {
            if (window.location.search.includes('print=1')) {
                window.close();
            }
        }
    </script>
</body>
</html>
                    </div>
                    
                    <div class="info-card">
                        <h3>Status</h3>
                        <div class="value" style="color: #38a169;">✅ BERHASIL</div>
                        <div class="sub-value">Stok telah diperbarui</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="timestamp">
                Dicetak pada: <?= date('d M Y, H:i:s') ?> WIB
            </div>
            <div>
                <small>Receipt ini adalah bukti sah penerimaan barang di warehouse</small>
            </div>
            
            <div class="signature">
                <div style="display: flex; justify-content: space-around; margin-top: 40px;">
                    <div>
                        <div>Diterima oleh,</div>
                        <div class="signature-line"></div>
                        <div style="margin-top: 10px; font-size: 0.9em;">Petugas Warehouse</div>
                    </div>
                    <div>
                        <div>Disetujui oleh,</div>
                        <div class="signature-line"></div>
                        <div style="margin-top: 10px; font-size: 0.9em;">Supervisor</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print when accessed with ?print=1
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        }

        // Handle print completion
        window.onafterprint = function() {
            if (window.location.search.includes('print=1')) {
                window.close();
            }
        }
    </script>
</body>
</html>