<?php

namespace App\Controllers;

use App\Models\OutgoingItemModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;

class OutgoingItems extends BaseController
{
    protected $outgoingModel;
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->outgoingModel = new OutgoingItemModel();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        $perPage = 20;
        $currentPage = $this->request->getGet('page') ?? 1;
        $search = $this->request->getGet('search');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $offset = ($currentPage - 1) * $perPage;
        $outgoingItems = $this->outgoingModel->getOutgoingItemsWithDetails($perPage, $offset, $search, $startDate, $endDate);
        $totalItems = $this->outgoingModel->countOutgoingItemsWithDetails($search, $startDate, $endDate);

        $pager = \Config\Services::pager();
        $pager->setPath('outgoing-items');

        $data = [
            'title' => 'Barang Keluar - Warehouse Management System',
            'outgoing_items' => $outgoingItems,
            'pager' => $pager->makeLinks($currentPage, $perPage, $totalItems),
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_items' => $totalItems,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'statistics' => $this->outgoingModel->getOutgoingStatistics()
        ];

        return view('layouts/main', $data) . view('outgoing_items/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Barang Keluar - Warehouse Management System',
            'products' => $this->productModel->getProductsWithCategory(),
            'categories' => $this->categoryModel->findAll(),
            'validation' => session()->getFlashdata('validation')
        ];

        return view('layouts/main', $data) . view('outgoing_items/create', $data);
    }

    public function store()
    {
        $rules = [
            'product_id' => 'required|integer',
            'date' => 'required|valid_date',
            'quantity' => 'required|decimal|greater_than[0]',
            'description' => 'permit_empty|max_length[500]',
            'recipient' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        // Check stock availability
        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');

        $stockCheck = $this->productModel->checkStockAvailability($productId, $quantity);
        if (!$stockCheck['available']) {
            session()->setFlashdata('error', $stockCheck['message']);
            return redirect()->back()->withInput();
        }

        $data = [
            'product_id' => $productId,
            'date' => $this->request->getPost('date') . ' ' . date('H:i:s'),
            'quantity' => $quantity,
            'description' => $this->request->getPost('description'),
            'recipient' => $this->request->getPost('recipient'),
            'user_id' => session()->get('user_id')
        ];

        $result = $this->outgoingModel->addOutgoingItem($data);

        if ($result['success']) {
            session()->setFlashdata('success', 'Barang keluar berhasil dicatat dan stok produk telah diperbarui');
            return redirect()->to('/outgoing-items');
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        $outgoingItem = $this->outgoingModel->getOutgoingItemWithDetails($id);

        if (!$outgoingItem) {
            session()->setFlashdata('error', 'Data barang keluar tidak ditemukan');
            return redirect()->to('/outgoing-items');
        }

        $data = [
            'title' => 'Edit Barang Keluar - Warehouse Management System',
            'outgoing_item' => $outgoingItem,
            'products' => $this->productModel->getProductsWithCategory(),
            'categories' => $this->categoryModel->findAll(),
            'validation' => session()->getFlashdata('validation')
        ];

        return view('layouts/main', $data) . view('outgoing_items/edit', $data);
    }

    public function update($id)
    {
        $outgoingItem = $this->outgoingModel->find($id);

        if (!$outgoingItem) {
            session()->setFlashdata('error', 'Data barang keluar tidak ditemukan');
            return redirect()->to('/outgoing-items');
        }

        $rules = [
            'product_id' => 'required|integer',
            'date' => 'required|valid_date',
            'quantity' => 'required|decimal|greater_than[0]',
            'description' => 'permit_empty|max_length[500]',
            'recipient' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $data = [
            'product_id' => $this->request->getPost('product_id'),
            'date' => $this->request->getPost('date') . ' ' . date('H:i:s', strtotime($outgoingItem['date'])),
            'quantity' => $this->request->getPost('quantity'),
            'description' => $this->request->getPost('description'),
            'recipient' => $this->request->getPost('recipient')
        ];

        $result = $this->outgoingModel->updateOutgoingItem($id, $data);

        if ($result['success']) {
            session()->setFlashdata('success', 'Data barang keluar berhasil diperbarui');
            return redirect()->to('/outgoing-items');
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    public function delete($id)
    {
        if (!$this->isAdmin()) {
            session()->setFlashdata('error', 'Hanya admin yang dapat menghapus data barang keluar');
            return redirect()->to('/outgoing-items');
        }

        $outgoingItem = $this->outgoingModel->find($id);

        if (!$outgoingItem) {
            session()->setFlashdata('error', 'Data barang keluar tidak ditemukan');
            return redirect()->to('/outgoing-items');
        }

        $result = $this->outgoingModel->deleteOutgoingItem($id);

        if ($result['success']) {
            session()->setFlashdata('success', 'Data barang keluar berhasil dihapus dan stok produk telah disesuaikan');
        } else {
            session()->setFlashdata('error', $result['message']);
        }

        return redirect()->to('/outgoing-items');
    }

    public function checkStock()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');

        if (!$productId || !$quantity) {
            return $this->response->setJSON(['available' => false, 'message' => 'Data tidak lengkap']);
        }

        $stockCheck = $this->productModel->checkStockAvailability($productId, $quantity);

        $product = $this->productModel->find($productId);
        $response = [
            'available' => $stockCheck['available'],
            'message' => $stockCheck['message'],
            'current_stock' => $product['stock'] ?? 0,
            'unit' => $product['unit'] ?? ''
        ];

        return $this->response->setJSON($response);
    }

    public function getProductStock($productId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/outgoing-items');
        }

        $product = $this->productModel->getProductWithCategory($productId);

        if (!$product) {
            return $this->response->setJSON(['found' => false]);
        }

        return $this->response->setJSON([
            'found' => true,
            'stock' => $product['stock'],
            'unit' => $product['unit'],
            'min_stock' => $product['min_stock'],
            'name' => $product['name'],
            'code' => $product['code']
        ]);
    }

    public function bulkIssue()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/outgoing-items');
        }

        $items = $this->request->getPost('items');
        $recipient = $this->request->getPost('recipient');
        $description = $this->request->getPost('description');
        $date = $this->request->getPost('date') ?? date('Y-m-d');

        if (empty($items)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada item yang dipilih'
            ]);
        }

        $outgoingItems = [];
        foreach ($items as $item) {
            if (!empty($item['quantity']) && $item['quantity'] > 0) {
                $outgoingItems[] = [
                    'product_id' => $item['product_id'],
                    'date' => $date . ' ' . date('H:i:s'),
                    'quantity' => $item['quantity'],
                    'description' => $description,
                    'recipient' => $recipient,
                    'user_id' => session()->get('user_id')
                ];
            }
        }

        if (empty($outgoingItems)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada item yang valid untuk dikeluarkan'
            ]);
        }

        $result = $this->outgoingModel->bulkInsert($outgoingItems);

        if ($result['success']) {
            return $this->response->setJSON([
                'success' => true,
                'message' => count($outgoingItems) . ' item berhasil dikeluarkan'
            ]);
        } else {
            return $this->response->setJSON($result);
        }
    }

    public function export()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $format = $this->request->getGet('format') ?? 'csv';

        $outgoingItems = $this->outgoingModel->getOutgoingReport($startDate, $endDate);

        if ($format === 'csv') {
            return $this->exportCSV($outgoingItems, $startDate, $endDate);
        } else {
            return $this->exportJSON($outgoingItems, $startDate, $endDate);
        }
    }

    private function exportCSV($data, $startDate, $endDate)
    {
        $filename = 'outgoing_items_' . date('Y-m-d_H-i-s') . '.csv';

        $csv = "Tanggal,Kode Produk,Nama Produk,Kategori,Jumlah,Satuan,Penerima,Deskripsi,User\n";

        foreach ($data as $item) {
            $csv .= '"' . date('d/m/Y H:i', strtotime($item['date'])) . '",';
            $csv .= '"' . $item['product_code'] . '",';
            $csv .= '"' . $item['product_name'] . '",';
            $csv .= '"' . $item['category_name'] . '",';
            $csv .= '"' . $item['quantity'] . '",';
            $csv .= '"' . $item['unit'] . '",';
            $csv .= '"' . ($item['recipient'] ?? '') . '",';
            $csv .= '"' . ($item['description'] ?? '') . '",';
            $csv .= '"' . $item['user_name'] . '"' . "\n";
        }

        return $this->response
            ->setContentType('text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    private function exportJSON($data, $startDate, $endDate)
    {
        $filename = 'outgoing_items_' . date('Y-m-d_H-i-s') . '.json';

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
            'total_transactions' => $this->outgoingModel->countOutgoingItemsWithDetails(null, $startDate, $endDate),
            'total_quantity' => 0,
            'top_products' => $this->outgoingModel->getTopIssuedProducts(5, $startDate, $endDate),
            'top_recipients' => $this->outgoingModel->getTopRecipients(5, $startDate, $endDate),
            'daily_trend' => $this->outgoingModel->getDailyOutgoingData(30)
        ];

        // Calculate total quantity
        $outgoingData = $this->outgoingModel->getOutgoingByDate($startDate, $endDate);
        foreach ($outgoingData as $item) {
            $statistics['total_quantity'] += $item['quantity'];
        }

        return $this->response->setJSON($statistics);
    }

    public function history($productId)
    {
        $product = $this->productModel->getProductWithCategory($productId);

        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan');
            return redirect()->to('/outgoing-items');
        }

        $history = $this->outgoingModel->getOutgoingByProduct($productId, 50);

        $data = [
            'title' => 'Riwayat Barang Keluar - ' . $product['name'],
            'product' => $product,
            'history' => $history
        ];

        return view('layouts/main', $data) . view('outgoing_items/history', $data);
    }

    public function getAvailableProducts()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/outgoing-items');
        }

        $categoryId = $this->request->getGet('category_id');
        $search = $this->request->getGet('search');

        $builder = $this->productModel->select('products.*, categories.name as category_name')
            ->join('categories', 'categories.id = products.category_id')
            ->where('products.stock >', 0);

        if ($categoryId) {
            $builder->where('products.category_id', $categoryId);
        }

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->groupEnd();
        }

        $products = $builder->orderBy('products.name', 'ASC')
            ->limit(20)
            ->findAll();

        return $this->response->setJSON($products);
    }

    public function validateStockBeforeUpdate($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['valid' => false]);
        }

        $quantity = $this->request->getPost('quantity');
        $productId = $this->request->getPost('product_id');

        $currentItem = $this->outgoingModel->find($id);
        if (!$currentItem) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Item tidak ditemukan']);
        }

        // Calculate stock needed for the update
        $stockAdjustment = $quantity - $currentItem['quantity'];

        if ($stockAdjustment > 0) {
            // Need more stock
            $product = $this->productModel->find($productId);

            if ($product['stock'] < $stockAdjustment) {
                return $this->response->setJSON([
                    'valid' => false,
                    'message' => "Stok tidak mencukupi. Stok tersedia: {$product['stock']} {$product['unit']}"
                ]);
            }
        }

        return $this->response->setJSON(['valid' => true]);
    }
}
