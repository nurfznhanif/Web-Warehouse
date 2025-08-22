<?php

namespace App\Controllers;

use App\Models\IncomingItemModel;
use App\Models\ProductModel;
use App\Models\PurchaseModel;
use App\Models\PurchaseDetailModel;
use App\Models\VendorModel;

class IncomingItems extends BaseController
{
    protected $incomingModel;
    protected $productModel;
    protected $purchaseModel;
    protected $purchaseDetailModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->incomingModel = new IncomingItemModel();
        $this->productModel = new ProductModel();
        $this->purchaseModel = new PurchaseModel();
        $this->purchaseDetailModel = new PurchaseDetailModel();
        $this->vendorModel = new VendorModel();
    }

    public function index()
    {
        $perPage = 20;
        $currentPage = $this->request->getGet('page') ?? 1;
        $search = $this->request->getGet('search');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $offset = ($currentPage - 1) * $perPage;
        $incomingItems = $this->incomingModel->getIncomingItemsWithDetails($perPage, $offset, $search, $startDate, $endDate);
        $totalItems = $this->incomingModel->countIncomingItemsWithDetails($search, $startDate, $endDate);

        $pager = \Config\Services::pager();
        $pager->setPath('incoming-items');

        $data = [
            'title' => 'Barang Masuk - Vadhana Warehouse',
            'incoming_items' => $incomingItems,
            'pager' => $pager->makeLinks($currentPage, $perPage, $totalItems),
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_items' => $totalItems,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'statistics' => $this->incomingModel->getIncomingStatistics()
        ];

        return view('incoming_items/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Barang Masuk - Vadhana Warehouse',
            'products' => $this->productModel->getProductsForSelect(),
            'purchases' => $this->purchaseModel->getPurchasesWithDetails(null, null, null, 'pending'),
            'validation' => session()->getFlashdata('validation')
        ];

        return view('incoming_items/create', $data);
    }

    public function store()
    {
        $rules = [
            'product_id' => 'required|integer',
            'date' => 'required|valid_date',
            'quantity' => 'required|decimal|greater_than[0]',
            'purchase_id' => 'permit_empty|integer',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $data = [
            'product_id' => $this->request->getPost('product_id'),
            'purchase_id' => $this->request->getPost('purchase_id') ?: null,
            'date' => $this->request->getPost('date') . ' ' . date('H:i:s'),
            'quantity' => $this->request->getPost('quantity'),
            'notes' => $this->request->getPost('notes'),
            'user_id' => session()->get('user_id')
        ];

        $result = $this->incomingModel->addIncomingItem($data);

        if ($result['success']) {
            session()->setFlashdata('success', 'Barang masuk berhasil dicatat dan stok produk telah diperbarui');
            return redirect()->to('/incoming-items');
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    public function view($id)
    {
        $incomingItem = $this->incomingModel->getIncomingItemWithDetails($id);

        if (!$incomingItem) {
            session()->setFlashdata('error', 'Data barang masuk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        // Get current stock for the product
        $product = $this->productModel->find($incomingItem['product_id']);
        $incomingItem['stock'] = $product['stock'] ?? 0;

        $data = [
            'title' => 'Detail Barang Masuk #' . $id . ' - Vadhana Warehouse',
            'incoming_item' => $incomingItem
        ];

        return view('incoming_items/view', $data);
    }

    public function edit($id)
    {
        $incomingItem = $this->incomingModel->getIncomingItemWithDetails($id);

        if (!$incomingItem) {
            session()->setFlashdata('error', 'Data barang masuk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $data = [
            'title' => 'Edit Barang Masuk - Warehouse Management System',
            'incoming_item' => $incomingItem,
            'products' => $this->productModel->getProductsForSelect(),
            'purchases' => $this->purchaseModel->getPurchasesWithDetails(),
            'validation' => session()->getFlashdata('validation')
        ];

        return view('incoming_items/edit', $data);
    }

    public function update($id)
    {
        $incomingItem = $this->incomingModel->find($id);

        if (!$incomingItem) {
            session()->setFlashdata('error', 'Data barang masuk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $rules = [
            'product_id' => 'required|integer',
            'date' => 'required|valid_date',
            'quantity' => 'required|decimal|greater_than[0]',
            'purchase_id' => 'permit_empty|integer',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $data = [
            'product_id' => $this->request->getPost('product_id'),
            'purchase_id' => $this->request->getPost('purchase_id') ?: null,
            'date' => $this->request->getPost('date') . ' ' . date('H:i:s'),
            'quantity' => $this->request->getPost('quantity'),
            'notes' => $this->request->getPost('notes')
        ];

        $result = $this->incomingModel->updateIncomingItem($id, $data);

        if ($result['success']) {
            session()->setFlashdata('success', 'Data barang masuk berhasil diperbarui dan stok produk telah disesuaikan');
            return redirect()->to('/incoming-items');
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    public function delete($id)
    {
        $incomingItem = $this->incomingModel->find($id);

        if (!$incomingItem) {
            session()->setFlashdata('error', 'Data barang masuk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $result = $this->incomingModel->deleteIncomingItem($id);

        if ($result['success']) {
            session()->setFlashdata('success', 'Data barang masuk berhasil dihapus dan stok produk telah disesuaikan');
        } else {
            session()->setFlashdata('error', $result['message']);
        }

        return redirect()->to('/incoming-items');
    }

    public function printReceipt($id)
    {
        $incomingItem = $this->incomingModel->getIncomingItemWithDetails($id);

        if (!$incomingItem) {
            session()->setFlashdata('error', 'Data barang masuk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $data = [
            'title' => 'Receipt Barang Masuk #' . $id,
            'incoming_item' => $incomingItem
        ];

        return view('incoming_items/receipt', $data);
    }

    public function history($productId)
    {
        $product = $this->productModel->getProductWithCategory($productId);

        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan');
            return redirect()->to('/products');
        }

        $history = $this->incomingModel->getIncomingHistory($productId);

        $data = [
            'title' => 'Riwayat Barang Masuk - ' . $product['name'],
            'product' => $product,
            'history' => $history
        ];

        return view('incoming_items/history', $data);
    }

    public function getPurchaseItems($purchaseId)
    {
        try {
            $purchaseItems = $this->purchaseDetailModel->getPurchaseDetailsByPurchaseId($purchaseId);

            $items = [];
            foreach ($purchaseItems as $item) {
                $items[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'price' => $item['price']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error retrieving purchase items: ' . $e->getMessage()
            ]);
        }
    }

    public function export()
    {
        $search = $this->request->getGet('search');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $incomingItems = $this->incomingModel->getIncomingItemsWithDetails(null, null, $search, $startDate, $endDate);

        // Set headers for CSV download
        $this->response->setHeader('Content-Type', 'text/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="barang_masuk_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, [
            'No',
            'Tanggal',
            'Waktu',
            'Produk',
            'Kode Produk',
            'Kategori',
            'Kuantitas',
            'Unit',
            'Purchase Order',
            'Vendor',
            'User',
            'Catatan'
        ]);

        // CSV Data
        foreach ($incomingItems as $index => $item) {
            fputcsv($output, [
                $index + 1,
                date('d M Y', strtotime($item['date'])),
                date('H:i', strtotime($item['date'])),
                $item['product_name'],
                $item['product_code'],
                $item['category_name'],
                $item['quantity'],
                $item['unit'],
                $item['purchase_number'] ? 'PO-' . $item['purchase_number'] : '-',
                $item['vendor_name'] ?? '-',
                $item['user_name'],
                $item['notes'] ?? ''
            ]);
        }

        fclose($output);
        exit();
    }

    public function bulkImport()
    {
        if (!$this->request->getFile('csv_file')) {
            session()->setFlashdata('error', 'File CSV harus dipilih');
            return redirect()->back();
        }

        $file = $this->request->getFile('csv_file');

        if (!$file->isValid()) {
            session()->setFlashdata('error', 'File tidak valid');
            return redirect()->back();
        }

        if ($file->getExtension() !== 'csv') {
            session()->setFlashdata('error', 'File harus berformat CSV');
            return redirect()->back();
        }

        try {
            $handle = fopen($file->getTempName(), 'r');
            $header = fgetcsv($handle); // Skip header row

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            while (($row = fgetcsv($handle)) !== false) {
                try {
                    // Validate and process each row
                    $data = [
                        'product_id' => $row[0],
                        'date' => $row[1] . ' ' . date('H:i:s'),
                        'quantity' => $row[2],
                        'purchase_id' => !empty($row[3]) ? $row[3] : null,
                        'notes' => $row[4] ?? '',
                        'user_id' => session()->get('user_id')
                    ];

                    $result = $this->incomingModel->addIncomingItem($data);

                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Baris " . ($successCount + $errorCount + 1) . ": " . $result['message'];
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Baris " . ($successCount + $errorCount + 1) . ": " . $e->getMessage();
                }
            }

            fclose($handle);

            $message = "Import selesai. Berhasil: {$successCount}, Gagal: {$errorCount}";

            if ($errorCount > 0) {
                session()->setFlashdata('warning', $message);
                session()->setFlashdata('import_errors', $errors);
            } else {
                session()->setFlashdata('success', $message);
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Error saat memproses file: ' . $e->getMessage());
        }

        return redirect()->to('/incoming-items');
    }
}
