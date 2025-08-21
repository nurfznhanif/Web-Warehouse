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

        // PERBAIKAN: Hanya return satu view saja
        return view('incoming_items/index', $data);
    }

    // GANTI method create() dengan yang ini (hapus concatenation):

    public function create()
    {
        $data = [
            'title' => 'Tambah Barang Masuk - Vadhana Warehouse',
            'products' => $this->productModel->getProductsForSelect(),
            'purchases' => $this->purchaseModel->getPurchasesWithDetails(null, null, null, 'pending'),
            'validation' => session()->getFlashdata('validation')
        ];

        // PERBAIKAN: Hanya return satu view saja
        return view('incoming_items/create', $data);
    }

    public function store()
    {
        $rules = [
            'product_id' => 'required|integer',
            'date' => 'required|valid_date',
            'quantity' => 'required|decimal|greater_than[0]',
            'purchase_id' => 'required|integer', // UBAH: Dari permit_empty jadi required
            'notes' => 'permit_empty|max_length[500]'
        ];

        $messages = [
            'purchase_id' => [
                'required' => 'Pembelian harus dipilih',
                'integer' => 'Pembelian tidak valid'
            ],
            'product_id' => [
                'required' => 'Produk harus dipilih',
                'integer' => 'Produk tidak valid'
            ],
            'quantity' => [
                'required' => 'Jumlah harus diisi',
                'decimal' => 'Jumlah harus berupa angka',
                'greater_than' => 'Jumlah harus lebih dari 0'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        // TAMBAHAN: Validasi khusus untuk purchase-product relationship
        $purchaseId = $this->request->getPost('purchase_id');
        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');

        // Cek apakah produk ada dalam purchase yang dipilih
        $purchaseDetail = $this->purchaseDetailModel->where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->first();

        if (!$purchaseDetail) {
            session()->setFlashdata('error', 'Produk yang dipilih tidak tersedia dalam pembelian yang dipilih');
            return redirect()->back()->withInput();
        }

        // Cek apakah jumlah tidak melebihi sisa yang belum diterima
        $totalReceived = $this->incomingModel->where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->selectSum('quantity')
            ->first()['quantity'] ?? 0;

        $remainingQty = $purchaseDetail['quantity'] - $totalReceived;

        if ($quantity > $remainingQty) {
            session()->setFlashdata('error', "Jumlah yang akan diterima ({$quantity}) melebihi sisa yang belum diterima ({$remainingQty})");
            return redirect()->back()->withInput();
        }

        $data = [
            'product_id' => $productId,
            'purchase_id' => $purchaseId,
            'date' => $this->request->getPost('date') . ' ' . date('H:i:s'),
            'quantity' => $quantity,
            'notes' => $this->request->getPost('notes'),
            'user_id' => session()->get('user_id')
        ];

        $result = $this->incomingModel->addIncomingItem($data);

        if ($result['success']) {
            // GUNAKAN method yang sudah ada (checkPurchaseCompletion)
            $this->checkPurchaseCompletion($purchaseId);

            session()->setFlashdata('success', 'Barang masuk berhasil dicatat dan stok produk telah diperbarui');
            return redirect()->to('/incoming-items');
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        $incomingItem = $this->incomingModel->getIncomingItemWithDetails($id);

        if (!$incomingItem) {
            session()->setFlashdata('error', 'Data barang masuk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $data = [
            'title' => 'Edit Barang Masuk - Vadhana Warehouse',
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
            'date' => $this->request->getPost('date'),
            'quantity' => $this->request->getPost('quantity'),
            'notes' => $this->request->getPost('notes')
        ];

        $result = $this->incomingModel->updateIncomingItem($id, $data);

        if ($result['success']) {
            session()->setFlashdata('success', 'Data barang masuk berhasil diperbarui');
            return redirect()->to('/incoming-items');
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    public function delete($id)
    {
        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'Hanya admin yang dapat menghapus data barang masuk');
            return redirect()->to('/incoming-items');
        }

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

    public function getPurchaseItems($purchaseId)
    {
        // PERBAIKAN: Cek multiple ways untuk detect AJAX
        $isAjax = $this->request->isAJAX() ||
            $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' ||
            $this->request->getHeaderLine('Accept') === 'application/json';

        // Log untuk debugging
        log_message('info', 'getPurchaseItems called - Purchase ID: ' . $purchaseId);
        log_message('info', 'isAJAX: ' . ($this->request->isAJAX() ? 'true' : 'false'));
        log_message('info', 'X-Requested-With: ' . $this->request->getHeaderLine('X-Requested-With'));
        log_message('info', 'Accept header: ' . $this->request->getHeaderLine('Accept'));

        if (!$isAjax) {
            log_message('error', 'Not detected as AJAX request');

            // Jika bukan AJAX, return JSON anyway untuk debugging
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request tidak dikenali sebagai AJAX',
                'debug' => [
                    'isAJAX' => $this->request->isAJAX(),
                    'X-Requested-With' => $this->request->getHeaderLine('X-Requested-With'),
                    'Accept' => $this->request->getHeaderLine('Accept'),
                    'method' => $this->request->getMethod(),
                    'uri' => (string) $this->request->getUri()
                ]
            ]);
        }

        try {
            // Validasi purchase ID
            if (empty($purchaseId) || !is_numeric($purchaseId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Purchase ID tidak valid',
                    'items' => []
                ]);
            }

            // Cek apakah purchase exists
            $purchase = $this->purchaseModel->find($purchaseId);
            if (!$purchase) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Purchase tidak ditemukan',
                    'items' => []
                ]);
            }

            // Get purchase details
            $purchaseDetails = $this->purchaseDetailModel->getDetailsByPurchase($purchaseId);

            if (empty($purchaseDetails)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada produk dalam pembelian ini',
                    'items' => []
                ]);
            }

            // Process each detail
            foreach ($purchaseDetails as &$detail) {
                $receivedQty = $this->incomingModel->where('purchase_id', $purchaseId)
                    ->where('product_id', $detail['product_id'])
                    ->selectSum('quantity')
                    ->first()['quantity'] ?? 0;

                $detail['received_quantity'] = $receivedQty;
                $detail['remaining_quantity'] = $detail['quantity'] - $receivedQty;
                $detail['unit'] = $detail['unit'] ?? 'pcs';
            }

            // Filter items yang masih bisa diterima
            $availableItems = array_filter($purchaseDetails, function ($item) {
                return $item['remaining_quantity'] > 0;
            });

            if (empty($availableItems)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Semua item dalam pembelian ini sudah diterima lengkap',
                    'items' => []
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil dimuat',
                'items' => array_values($availableItems)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Exception in getPurchaseItems: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'items' => []
            ]);
        }
    }

    public function validateQuantity()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['valid' => false]);
        }

        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');
        $purchaseId = $this->request->getPost('purchase_id');

        if (!$productId || !$quantity) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Data tidak lengkap']);
        }

        // If purchase_id is provided, validate against purchase quantity
        if ($purchaseId) {
            $purchaseDetail = $this->purchaseDetailModel->where('purchase_id', $purchaseId)
                ->where('product_id', $productId)
                ->first();

            if (!$purchaseDetail) {
                return $this->response->setJSON(['valid' => false, 'message' => 'Produk tidak ditemukan dalam pembelian']);
            }

            $receivedQty = $this->incomingModel->where('purchase_id', $purchaseId)
                ->where('product_id', $productId)
                ->selectSum('quantity')
                ->first()['quantity'] ?? 0;

            $remainingQty = $purchaseDetail['quantity'] - $receivedQty;

            if ($quantity > $remainingQty) {
                return $this->response->setJSON([
                    'valid' => false,
                    'message' => "Jumlah melebihi sisa yang belum diterima ({$remainingQty})"
                ]);
            }
        }

        return $this->response->setJSON(['valid' => true]);
    }

    public function getProductInfo($productId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/incoming-items');
        }

        $product = $this->productModel->getProductWithCategory($productId);

        if (!$product) {
            return $this->response->setJSON(['found' => false]);
        }

        return $this->response->setJSON([
            'found' => true,
            'product' => $product
        ]);
    }

    public function bulkReceive()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/incoming-items');
        }

        $purchaseId = $this->request->getPost('purchase_id');
        $itemsJson = $this->request->getPost('items');
        $items = json_decode($itemsJson, true);

        if (!$purchaseId || empty($items)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak valid'
            ]);
        }

        $incomingItems = [];
        foreach ($items as $item) {
            if (!empty($item['quantity']) && $item['quantity'] > 0) {
                $incomingItems[] = [
                    'product_id' => $item['product_id'],
                    'purchase_id' => $purchaseId,
                    'date' => date('Y-m-d'),
                    'quantity' => $item['quantity'],
                    'notes' => 'Bulk receive from Purchase #' . $purchaseId,
                    'user_id' => session()->get('user_id')
                ];
            }
        }

        if (empty($incomingItems)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada item yang dipilih untuk diterima'
            ]);
        }

        $result = $this->incomingModel->bulkInsert($incomingItems);

        if ($result['success']) {
            // Check if purchase is fully received
            $this->checkPurchaseCompletion($purchaseId);

            return $this->response->setJSON([
                'success' => true,
                'message' => count($incomingItems) . ' item berhasil diterima'
            ]);
        } else {
            return $this->response->setJSON($result);
        }
    }

    private function checkPurchaseCompletion($purchaseId)
    {
        // Get all purchase details
        $purchaseDetails = $this->purchaseDetailModel->where('purchase_id', $purchaseId)->findAll();
        $fullyReceived = true;

        foreach ($purchaseDetails as $detail) {
            $receivedQty = $this->incomingModel->where('purchase_id', $purchaseId)
                ->where('product_id', $detail['product_id'])
                ->selectSum('quantity')
                ->first()['quantity'] ?? 0;

            if ($receivedQty < $detail['quantity']) {
                $fullyReceived = false;
                break;
            }
        }

        // Update purchase status if fully received
        if ($fullyReceived) {
            $this->purchaseModel->update($purchaseId, ['status' => 'received']);
        }
    }

    public function receiveFromPurchase($purchaseId)
    {
        $purchase = $this->purchaseModel->getPurchaseWithDetails($purchaseId);

        if (!$purchase) {
            session()->setFlashdata('error', 'Purchase order tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        // Get unreceived items
        $unreceivedItems = $this->purchaseDetailModel->getUnreceivedItems($purchaseId);

        if (empty($unreceivedItems)) {
            session()->setFlashdata('info', 'Semua item dari purchase order ini sudah diterima lengkap');
            return redirect()->to('/incoming-items');
        }

        $data = [
            'title' => 'Terima Barang dari Purchase #' . $purchaseId . ' - Warehouse Management System',
            'purchase_id' => $purchaseId,
            'purchase' => $purchase,
            'unreceived_items' => $unreceivedItems
        ];

        return view('incoming_items/receive_from_purchase', $data);
    }

    public function history($productId)
    {
        $product = $this->productModel->getProductWithCategory($productId);

        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $history = $this->incomingModel->getIncomingByProduct($productId, 50);

        $data = [
            'title' => 'Riwayat Barang Masuk - ' . $product['name'],
            'product' => $product,
            'history' => $history
        ];

        return view('incoming_items/history', $data);
    }

    public function printReceipt($id)
    {
        $item = $this->incomingModel->getIncomingItemWithDetails($id);

        if (!$item) {
            session()->setFlashdata('error', 'Data tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $data = [
            'title' => 'Receipt Barang Masuk #' . $id,
            'item' => $item
        ];

        return view('incoming_items/receipt', $data);
    }

    public function export()
    {
        $format = $this->request->getGet('format') ?? 'excel';
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $data = $this->incomingModel->getIncomingReport($startDate, $endDate);

        if ($format === 'pdf') {
            return $this->exportToPDF($data, $startDate, $endDate);
        } else {
            return $this->exportToExcel($data, $startDate, $endDate);
        }
    }

    private function exportToExcel($data, $startDate, $endDate)
    {
        $filename = 'incoming_items_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, [
            'No',
            'Tanggal',
            'Kode Produk',
            'Nama Produk',
            'Kategori',
            'Quantity',
            'Unit',
            'Purchase Order',
            'Vendor',
            'User',
            'Catatan'
        ]);

        // CSV Data
        foreach ($data as $index => $item) {
            fputcsv($output, [
                $index + 1,
                date('d-m-Y H:i', strtotime($item['date'])),
                $item['product_code'],
                $item['product_name'],
                $item['category_name'] ?? '-',
                number_format($item['quantity'], 2),
                $item['unit'],
                $item['purchase_number'] ? 'PO #' . $item['purchase_number'] : 'Manual',
                $item['vendor_name'] ?? '-',
                $item['user_name'] ?? '-',
                $item['notes'] ?? '-'
            ]);
        }

        fclose($output);
        exit;
    }

    private function exportToPDF($data, $startDate, $endDate)
    {
        // Implementasi PDF export bisa menggunakan library seperti TCPDF atau mPDF
        return $this->response->setJSON([
            'message' => 'PDF export belum diimplementasi',
            'total_records' => count($data)
        ]);
    }

    public function getSummary()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/incoming-items');
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $summary = [
            'total_transactions' => 0,
            'total_quantity' => 0,
            'total_products' => 0,
            'from_purchase_count' => 0,
            'manual_entry_count' => 0
        ];

        $builder = $this->incomingModel;

        if ($startDate) {
            $builder = $builder->where('DATE(date) >=', $startDate);
        }

        if ($endDate) {
            $builder = $builder->where('DATE(date) <=', $endDate);
        }

        // Get statistics
        $summary['total_transactions'] = $builder->countAllResults(false);

        $quantitySum = $builder->selectSum('quantity')->first();
        $summary['total_quantity'] = $quantitySum['quantity'] ?? 0;

        $productCount = $builder->distinct()->countAllResults('product_id', false);
        $summary['total_products'] = $productCount;

        $fromPurchase = $builder->where('purchase_id IS NOT NULL')->countAllResults(false);
        $summary['from_purchase_count'] = $fromPurchase;

        $summary['manual_entry_count'] = $summary['total_transactions'] - $summary['from_purchase_count'];

        return $this->response->setJSON($summary);
    }
}
