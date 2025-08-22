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

        // ✅ TAMBAHAN: Get pending purchases untuk receive button
        $pendingPurchases = $this->purchaseModel->getPurchasesWithDetails(10, null, null, 'pending');

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
            'statistics' => $this->incomingModel->getIncomingStatistics(),
            'pending_purchases' => $pendingPurchases // ✅ TAMBAHAN DATA
        ];

        return view('incoming_items/index', $data);
    }

    public function create()
    {
        // Hanya tampilkan pembelian dengan status 'pending'
        $data = [
            'title' => 'Tambah Barang Masuk - Vadhana Warehouse',
            'purchases' => $this->purchaseModel->getPurchasesWithDetails(null, null, null, 'pending'),
            'validation' => session()->getFlashdata('validation')
        ];

        return view('incoming_items/create', $data);
    }

    public function store()
    {
        $rules = [
            'purchase_id' => 'required|integer',
            'date' => 'required|valid_date',
            'selected_products' => 'required',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $purchaseId = $this->request->getPost('purchase_id');
        $selectedProducts = $this->request->getPost('selected_products');
        $date = $this->request->getPost('date');
        $notes = $this->request->getPost('notes');

        // Validasi pembelian masih dalam status pending
        $purchase = $this->purchaseModel->find($purchaseId);
        if (!$purchase || $purchase['status'] !== 'pending') {
            session()->setFlashdata('error', 'Pembelian tidak valid atau sudah diproses');
            return redirect()->back()->withInput();
        }

        // Decode selected products
        $incomingItems = [];
        foreach ($selectedProducts as $productJson) {
            $product = json_decode($productJson, true);
            if ($product) {
                $incomingItems[] = [
                    'product_id' => $product['product_id'],
                    'purchase_id' => $purchaseId,
                    'date' => $date . ' ' . date('H:i:s'),
                    'quantity' => $product['quantity'],
                    'notes' => $notes,
                    'user_id' => session()->get('user_id')
                ];
            }
        }

        if (empty($incomingItems)) {
            session()->setFlashdata('error', 'Tidak ada produk yang dipilih');
            return redirect()->back()->withInput();
        }

        // PERBAIKAN: Gunakan satu transaksi database saja
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($incomingItems as $item) {
                // Validasi produk ada dalam pembelian
                $purchaseDetail = $this->purchaseDetailModel
                    ->where('purchase_id', $purchaseId)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$purchaseDetail) {
                    throw new \Exception('Produk tidak ditemukan dalam pembelian yang dipilih');
                }

                // Cek total yang sudah diterima
                $totalReceived = $this->incomingModel
                    ->where('purchase_id', $purchaseId)
                    ->where('product_id', $item['product_id'])
                    ->selectSum('quantity')
                    ->first()['quantity'] ?? 0;

                $newTotal = $totalReceived + $item['quantity'];

                if ($newTotal > $purchaseDetail['quantity']) {
                    throw new \Exception('Jumlah penerimaan melebihi jumlah pembelian');
                }

                // 1. Insert data incoming item saja
                // Stok akan otomatis terupdate oleh database trigger
                if (!$this->incomingModel->insert($item)) {
                    throw new \Exception('Gagal menyimpan data barang masuk');
                }

                // HAPUS: Update stok manual karena sudah ada trigger
                // Database trigger akan otomatis update stok
            }

            // 2. Check apakah semua item sudah diterima lengkap
            $allReceived = $this->checkIfPurchaseFullyReceived($purchaseId);

            if ($allReceived) {
                if (!$this->purchaseModel->update($purchaseId, ['status' => 'received'])) {
                    throw new \Exception('Gagal memperbarui status pembelian');
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            $message = count($incomingItems) . ' produk berhasil diterima dan stok telah diperbarui';
            if ($allReceived) {
                $message .= '. Status pembelian telah diubah menjadi RECEIVED.';
            }

            session()->setFlashdata('success', $message);
            return redirect()->to('/incoming-items');
        } catch (\Exception $e) {
            $db->transRollback();
            session()->setFlashdata('error', $e->getMessage());
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
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        // Hanya update notes dan user_id, tidak boleh mengubah purchase_id, product_id, quantity
        $data = [
            'notes' => $this->request->getPost('notes'),
            'user_id' => session()->get('user_id')
        ];

        if ($this->incomingModel->update($id, $data)) {
            session()->setFlashdata('success', 'Data barang masuk berhasil diperbarui');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui data barang masuk');
        }

        return redirect()->to('/incoming-items');
    }

    public function delete($id)
    {
        if (!$this->isAdmin()) {
            session()->setFlashdata('error', 'Hanya admin yang dapat menghapus data barang masuk');
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
        // Validasi pembelian
        $purchase = $this->purchaseModel->find($purchaseId);
        if (!$purchase || $purchase['status'] !== 'pending') {
            return $this->response->setJSON([]);
        }

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

    private function processIncomingFromPurchase($purchaseId, $incomingItems)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Validasi setiap item dan insert data
            foreach ($incomingItems as $item) {
                // Gunakan method addIncomingItem yang sudah tidak update stok
                $result = $this->incomingModel->addIncomingItem($item);

                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }

                // Update stok produk hanya di sini
                $product = $this->productModel->find($item['product_id']);
                $newStock = $product['stock'] + $item['quantity'];

                if (!$this->productModel->update($item['product_id'], ['stock' => $newStock])) {
                    throw new \Exception('Gagal memperbarui stok produk');
                }
            }

            // Check apakah semua item sudah diterima lengkap
            $allReceived = $this->checkIfPurchaseFullyReceived($purchaseId);

            if ($allReceived) {
                // Update status pembelian menjadi 'received'
                if (!$this->purchaseModel->update($purchaseId, ['status' => 'received'])) {
                    throw new \Exception('Gagal memperbarui status pembelian');
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            $message = count($incomingItems) . ' produk berhasil diterima dan stok telah diperbarui';
            if ($allReceived) {
                $message .= '. Status pembelian telah diubah menjadi RECEIVED.';
            }

            return ['success' => true, 'message' => $message];
        } catch (\Exception $e) {
            $db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkIfPurchaseFullyReceived($purchaseId)
    {
        $purchaseDetails = $this->purchaseDetailModel->where('purchase_id', $purchaseId)->findAll();

        foreach ($purchaseDetails as $detail) {
            $receivedQty = $this->incomingModel
                ->where('purchase_id', $purchaseId)
                ->where('product_id', $detail['product_id'])
                ->selectSum('quantity')
                ->first()['quantity'] ?? 0;

            if ($receivedQty < $detail['quantity']) {
                return false; // Masih ada yang belum diterima lengkap
            }
        }

        return true; // Semua sudah diterima lengkap
    }

    public function view($id)
    {
        $incomingItem = $this->incomingModel->getIncomingItemWithDetails($id);

        if (!$incomingItem) {
            session()->setFlashdata('error', 'Data barang masuk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $data = [
            'title' => 'Detail Barang Masuk #' . str_pad($id, 6, '0', STR_PAD_LEFT),
            'incoming_item' => $incomingItem
        ];

        return view('incoming_items/view', $data);
    }

    public function receipt($id)
    {
        $incomingItem = $this->incomingModel->getIncomingItemWithDetails($id);

        if (!$incomingItem) {
            session()->setFlashdata('error', 'Data barang masuk tidak ditemukan');
            return redirect()->to('/incoming-items');
        }

        $data = [
            'title' => 'Bukti Penerimaan Barang #' . str_pad($id, 6, '0', STR_PAD_LEFT),
            'incoming_item' => $incomingItem
        ];

        return view('incoming_items/receipt', $data);
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

        return view('incoming_items/receive_from_purchase', $data);
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
            if (!empty($item['selected']) && !empty($item['quantity']) && $item['quantity'] > 0) {
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

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($incomingItems as $item) {
                // Validasi produk ada dalam pembelian
                $purchaseDetail = $this->purchaseDetailModel
                    ->where('purchase_id', $purchaseId)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$purchaseDetail) {
                    throw new \Exception('Produk tidak ditemukan dalam pembelian yang dipilih');
                }

                // Cek total yang sudah diterima
                $totalReceived = $this->incomingModel
                    ->where('purchase_id', $purchaseId)
                    ->where('product_id', $item['product_id'])
                    ->selectSum('quantity')
                    ->first()['quantity'] ?? 0;

                $newTotal = $totalReceived + $item['quantity'];

                if ($newTotal > $purchaseDetail['quantity']) {
                    throw new \Exception('Jumlah penerimaan melebihi jumlah pembelian');
                }

                // Insert data incoming item (stok akan otomatis terupdate oleh trigger)
                if (!$this->incomingModel->insert($item)) {
                    throw new \Exception('Gagal menyimpan data barang masuk');
                }
            }

            // Check apakah semua item sudah diterima lengkap
            $allReceived = $this->checkIfPurchaseFullyReceived($purchaseId);

            if ($allReceived) {
                if (!$this->purchaseModel->update($purchaseId, ['status' => 'received'])) {
                    throw new \Exception('Gagal memperbarui status pembelian');
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            $message = count($incomingItems) . ' item berhasil diterima';
            if ($allReceived) {
                $message .= '. Status pembelian telah diubah menjadi RECEIVED.';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'redirect' => base_url('/incoming-items')
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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

        // PERBAIKAN: Hanya return satu view saja
        // Karena view 'incoming_items/history' sudah extend 'layouts/main'
        return view('incoming_items/history', $data);
    }

    /**
     * Mendapatkan informasi produk via AJAX
     * 
     * @param int $productId
     * @return mixed
     */
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

    /**
     * Validasi quantity via AJAX
     * 
     * @return mixed
     */
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
}
