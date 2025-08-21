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
            'title' => 'Barang Masuk - Warehouse Management System',
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

        return view('layouts/main', $data) . view('incoming_items/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Barang Masuk - Warehouse Management System',
            'products' => $this->productModel->getProductsForSelect(),
            'purchases' => $this->purchaseModel->getPurchasesWithDetails(null, null, null, 'pending'),
            'validation' => session()->getFlashdata('validation')
        ];

        return view('layouts/main', $data) . view('incoming_items/create', $data);
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

        return view('layouts/main', $data) . view('incoming_items/edit', $data);
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
            'date' => $this->request->getPost('date') . ' ' . date('H:i:s', strtotime($incomingItem['date'])),
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
        if (!$this->isAdmin()) {
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
        $purchaseDetails = $this->purchaseDetailModel->getDetailsByPurchase($purchaseId);

        // Get already received quantities for each product
        foreach ($purchaseDetails as &$detail) {
            $receivedQty = $this->incomingModel->where('purchase_id', $purchaseId)
                ->where('product_id', $detail['product_id'])
                ->selectSum('quantity')
                ->first()['quantity'] ?? 0;

            $detail['received_quantity'] = $receivedQty;
            $detail['remaining_quantity'] = $detail['quantity'] - $receivedQty;
        }

        return $this->response->setJSON($purchaseDetails);
    }

    public function bulkReceive()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/incoming-items');
        }

        $purchaseId = $this->request->getPost('purchase_id');
        $items = $this->request->getPost('items');

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
                    'date' => date('Y-m-d H:i:s'),
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
            $this->purchaseModel->updateStatus($purchaseId, 'received');
        }
    }

    public function export()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $format = $this->request->getGet('format') ?? 'csv';

        $incomingItems = $this->incomingModel->getIncomingReport($startDate, $endDate);

        if ($format === 'csv') {
            return $this->exportCSV($incomingItems, $startDate, $endDate);
        } else {
            return $this->exportJSON($incomingItems, $startDate, $endDate);
        }
    }

    private function exportCSV($data, $startDate, $endDate)
    {
        $filename = 'incoming_items_' . date('Y-m-d_H-i-s') . '.csv';

        $csv = "Tanggal,Kode Produk,Nama Produk,Kategori,Jumlah,Satuan,Vendor,User,Catatan\n";

        foreach ($data as $item) {
            $csv .= '"' . date('d/m/Y H:i', strtotime($item['date'])) . '",';
            $csv .= '"' . $item['product_code'] . '",';
            $csv .= '"' . $item['product_name'] . '",';
            $csv .= '"' . $item['category_name'] . '",';
            $csv .= '"' . $item['quantity'] . '",';
            $csv .= '"' . $item['unit'] . '",';
            $csv .= '"' . ($item['vendor_name'] ?? '') . '",';
            $csv .= '"' . $item['user_name'] . '",';
            $csv .= '"' . ($item['notes'] ?? '') . '"' . "\n";
        }

        return $this->response
            ->setContentType('text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    private function exportJSON($data, $startDate, $endDate)
    {
        $filename = 'incoming_items_' . date('Y-m-d_H-i-s') . '.json';

        $exportData = [
            'exported_at' => date('Y-m-d H:i:s'),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'total_records' => count($data),
            'data' => $data
        ];

        return $this->response
            ->setContentType('application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody(json_encode($exportData, JSON_PRETTY_PRINT));
    }

    public function getStatistics()
    {
        $period = $this->request->getGet('period') ?? '30days';

        $startDate = null;
        switch ($period) {
            case '7days':
                $startDate = date('Y-m-d', strtotime('-7 days'));
                break;
            case '30days':
                $startDate = date('Y-m-d', strtotime('-30 days'));
                break;
            case '90days':
                $startDate = date('Y-m-d', strtotime('-90 days'));
                break;
        }

        $endDate = date('Y-m-d');

        $statistics = [
            'total_transactions' => $this->incomingModel->countIncomingItemsWithDetails(null, $startDate, $endDate),
            'total_quantity' => $this->incomingModel->getIncomingByDate($startDate, $endDate),
            'top_products' => $this->incomingModel->getTopReceivedProducts(5, $startDate, $endDate),
            'daily_trend' => $this->incomingModel->getDailyIncomingData(30)
        ];

        // Calculate total quantity
        $totalQty = 0;
        foreach ($statistics['total_quantity'] as $item) {
            $totalQty += $item['quantity'];
        }
        $statistics['total_quantity'] = $totalQty;

        return $this->response->setJSON($statistics);
    }

    public function receiveFromPurchase($purchaseId)
    {
        $purchase = $this->purchaseModel->getPurchaseWithDetails($purchaseId);

        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        if ($purchase['status'] === 'received') {
            session()->setFlashdata('warning', 'Pembelian ini sudah diterima sepenuhnya');
            return redirect()->to('/incoming-items');
        }

        // Get unreceived items
        $unreceivedItems = $this->purchaseDetailModel->getUnreceivedItems($purchaseId);

        $data = [
            'title' => 'Terima Barang dari Pembelian #' . $purchaseId,
            'purchase' => $purchase,
            'unreceived_items' => $unreceivedItems
        ];

        return view('layouts/main', $data) . view('incoming_items/receive_from_purchase', $data);
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

        return view('layouts/main', $data) . view('incoming_items/history', $data);
    }
}
