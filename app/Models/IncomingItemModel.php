<?php

namespace App\Models;

use CodeIgniter\Model;

class IncomingItemModel extends Model
{
    protected $table = 'incoming_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'product_id',
        'purchase_id',
        'date',
        'quantity',
        'notes',
        'user_id'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'product_id' => 'required|integer',
        'purchase_id' => 'required|integer',
        'date' => 'required|valid_date',
        'quantity' => 'required|decimal|greater_than[0]',
        'notes' => 'permit_empty|max_length[500]',
        'user_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'product_id' => [
            'required' => 'Produk harus dipilih',
            'integer' => 'Produk tidak valid'
        ],
        'purchase_id' => [
            'required' => 'Pembelian harus dipilih',
            'integer' => 'Pembelian tidak valid'
        ],
        'date' => [
            'required' => 'Tanggal harus diisi',
            'valid_date' => 'Format tanggal tidak valid'
        ],
        'quantity' => [
            'required' => 'Jumlah harus diisi',
            'decimal' => 'Jumlah harus berupa angka',
            'greater_than' => 'Jumlah harus lebih dari 0'
        ],
        'notes' => [
            'max_length' => 'Catatan maksimal 500 karakter'
        ],
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID tidak valid'
        ]
    ];

    public function getIncomingItemsWithDetails($limit = null, $offset = null, $search = null, $startDate = null, $endDate = null, $productId = null)
    {
        $builder = $this->select('incoming_items.*, 
                                 products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 categories.name as category_name,
                                 purchases.id as purchase_number,
                                 purchases.purchase_date,
                                 vendors.name as vendor_name,
                                 users.full_name as user_name')
            ->join('products', 'products.id = incoming_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->join('purchases', 'purchases.id = incoming_items.purchase_id')
            ->join('vendors', 'vendors.id = purchases.vendor_id')
            ->join('users', 'users.id = incoming_items.user_id');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('vendors.name', $search)
                ->orLike('purchases.id', $search)
                ->groupEnd();
        }

        if ($startDate) {
            $builder->where('DATE(incoming_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(incoming_items.date) <=', $endDate);
        }

        if ($productId) {
            $builder->where('incoming_items.product_id', $productId);
        }

        $builder->orderBy('incoming_items.date', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    public function countIncomingItemsWithDetails($search = null, $startDate = null, $endDate = null, $productId = null)
    {
        $builder = $this->join('products', 'products.id = incoming_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->join('purchases', 'purchases.id = incoming_items.purchase_id')
            ->join('vendors', 'vendors.id = purchases.vendor_id');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('vendors.name', $search)
                ->orLike('purchases.id', $search)
                ->groupEnd();
        }

        if ($startDate) {
            $builder->where('DATE(incoming_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(incoming_items.date) <=', $endDate);
        }

        if ($productId) {
            $builder->where('incoming_items.product_id', $productId);
        }

        return $builder->countAllResults();
    }

    public function getIncomingItemWithDetails($id)
    {
        return $this->select('incoming_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit,
                             categories.name as category_name,
                             purchases.id as purchase_number,
                             purchases.purchase_date,
                             vendors.name as vendor_name,
                             users.full_name as user_name')
            ->join('products', 'products.id = incoming_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->join('purchases', 'purchases.id = incoming_items.purchase_id')
            ->join('vendors', 'vendors.id = purchases.vendor_id')
            ->join('users', 'users.id = incoming_items.user_id')
            ->where('incoming_items.id', $id)
            ->first();
    }

    public function addIncomingItemSimple($data)
    {
        // Method sederhana untuk insert data tanpa logic tambahan
        // Digunakan ketika validasi sudah dilakukan di controller
        return $this->insert($data);
    }

    public function addIncomingItem($data)
    {
        // Method lengkap dengan validasi TANPA update stok
        // Update stok akan dilakukan di controller

        $this->db->transStart();

        try {
            // Validasi pembelian harus ada dan berstatus pending
            $purchaseModel = new \App\Models\PurchaseModel();
            $purchase = $purchaseModel->find($data['purchase_id']);

            if (!$purchase) {
                throw new \Exception('Pembelian tidak ditemukan');
            }

            if ($purchase['status'] !== 'pending') {
                throw new \Exception('Pembelian sudah diproses atau dibatalkan');
            }

            // Validasi produk ada dalam detail pembelian
            $purchaseDetailModel = new \App\Models\PurchaseDetailModel();
            $purchaseDetail = $purchaseDetailModel->where('purchase_id', $data['purchase_id'])
                ->where('product_id', $data['product_id'])
                ->first();

            if (!$purchaseDetail) {
                throw new \Exception('Produk tidak ditemukan dalam pembelian yang dipilih');
            }

            // Cek total kuantitas yang sudah diterima sebelumnya
            $totalReceived = $this->where('purchase_id', $data['purchase_id'])
                ->where('product_id', $data['product_id'])
                ->selectSum('quantity')
                ->first()['quantity'] ?? 0;

            $newTotal = $totalReceived + $data['quantity'];

            // Kuantitas tidak boleh melebihi yang dibeli
            if ($newTotal > $purchaseDetail['quantity']) {
                throw new \Exception('Jumlah penerimaan (' . $newTotal . ') melebihi jumlah pembelian (' . $purchaseDetail['quantity'] . ')');
            }

            // Insert data barang masuk SAJA - tanpa update stok dan status
            $incomingId = $this->insert($data);
            if (!$incomingId) {
                throw new \Exception('Gagal menyimpan data barang masuk');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'message' => 'Data barang masuk berhasil disimpan', 'id' => $incomingId];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateIncomingItem($id, $data)
    {
        $this->db->transStart();

        try {
            $item = $this->find($id);
            if (!$item) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Hanya boleh update notes dan user_id
            $allowedFields = ['notes', 'user_id'];
            $updateData = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                throw new \Exception('Tidak ada data yang dapat diupdate');
            }

            if (!$this->update($id, $updateData)) {
                throw new \Exception('Gagal memperbarui data');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'message' => 'Data berhasil diperbarui'];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteIncomingItem($id)
    {
        $this->db->transStart();

        try {
            $item = $this->find($id);
            if (!$item) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Hapus data barang masuk
            if (!$this->delete($id)) {
                throw new \Exception('Gagal menghapus item');
            }

            // Kurangi stok produk
            $productModel = new \App\Models\ProductModel();
            $product = $productModel->find($item['product_id']);
            $newStock = $product['stock'] - $item['quantity'];

            if ($newStock < 0) {
                throw new \Exception('Penghapusan akan menyebabkan stok negatif');
            }

            if (!$productModel->update($item['product_id'], ['stock' => $newStock])) {
                throw new \Exception('Gagal memperbarui stok produk');
            }

            // Jika item ini dari pembelian, kembalikan status pembelian ke pending
            if ($item['purchase_id']) {
                $purchaseModel = new \App\Models\PurchaseModel();
                $purchase = $purchaseModel->find($item['purchase_id']);

                if ($purchase && $purchase['status'] === 'received') {
                    $purchaseModel->update($item['purchase_id'], ['status' => 'pending']);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkPurchaseCompletion($purchaseId)
    {
        $purchaseDetailModel = new \App\Models\PurchaseDetailModel();
        $purchaseDetails = $purchaseDetailModel->where('purchase_id', $purchaseId)->findAll();

        foreach ($purchaseDetails as $detail) {
            $receivedQty = $this->where('purchase_id', $purchaseId)
                ->where('product_id', $detail['product_id'])
                ->selectSum('quantity')
                ->first()['quantity'] ?? 0;

            if ($receivedQty < $detail['quantity']) {
                return false; // Masih ada yang belum diterima lengkap
            }
        }

        return true; // Semua sudah diterima lengkap
    }

    public function getIncomingStatistics()
    {
        $stats = [];

        // Total incoming items
        $stats['total_items'] = $this->countAll();

        // Today's incoming
        $stats['today_incoming'] = $this->where('DATE(date)', date('Y-m-d'))->countAllResults(false);

        // This month's incoming
        $stats['monthly_incoming'] = $this->where('YEAR(date)', date('Y'))
            ->where('MONTH(date)', date('m'))
            ->countAllResults(false);

        // Total quantity received
        $totalQuantity = $this->selectSum('quantity')->first();
        $stats['total_quantity'] = $totalQuantity['quantity'] ?? 0;

        // Most received product
        $mostReceived = $this->select('products.name, products.code, SUM(incoming_items.quantity) as total_quantity')
            ->join('products', 'products.id = incoming_items.product_id')
            ->groupBy('products.id, products.name, products.code')
            ->orderBy('total_quantity', 'DESC')
            ->first();
        $stats['most_received_product'] = $mostReceived;

        // Recent incoming (last 7 days)
        $stats['recent_incoming'] = $this->where('date >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->countAllResults(false);

        return $stats;
    }

    public function getRecentIncoming($limit = 5)
    {
        return $this->select('incoming_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit,
                             users.full_name as user_name')
            ->join('products', 'products.id = incoming_items.product_id')
            ->join('users', 'users.id = incoming_items.user_id')
            ->orderBy('incoming_items.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getIncomingByProduct($productId, $limit = null)
    {
        $builder = $this->select('incoming_items.*, 
                                 purchases.id as purchase_number,
                                 purchases.purchase_date,
                                 vendors.name as vendor_name,
                                 users.full_name as user_name')
            ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
            ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
            ->join('users', 'users.id = incoming_items.user_id', 'left')
            ->where('incoming_items.product_id', $productId)
            ->orderBy('incoming_items.date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }
}
