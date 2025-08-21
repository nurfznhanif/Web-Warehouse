<?php

namespace App\Models;

use CodeIgniter\Model;

class OutgoingItemModel extends Model
{
    protected $table = 'outgoing_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'product_id',
        'date',
        'quantity',
        'description',
        'recipient',
        'user_id'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'product_id' => 'required|integer',
        'date' => 'required|valid_date',
        'quantity' => 'required|decimal|greater_than[0]',
        'description' => 'permit_empty|max_length[500]',
        'recipient' => 'permit_empty|max_length[100]',
        'user_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'product_id' => [
            'required' => 'Produk harus dipilih',
            'integer' => 'Produk tidak valid'
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
        'description' => [
            'max_length' => 'Deskripsi maksimal 500 karakter'
        ],
        'recipient' => [
            'max_length' => 'Penerima maksimal 100 karakter'
        ],
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID tidak valid'
        ]
    ];

    public function addOutgoingItem($data)
    {
        $this->db->transStart();

        try {
            // Check stock availability
            $productModel = new \App\Models\ProductModel();
            $product = $productModel->find($data['product_id']);

            if (!$product) {
                throw new \Exception('Produk tidak ditemukan');
            }

            if ($product['stock'] < $data['quantity']) {
                throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $product['stock'] . ' ' . $product['unit']);
            }

            // Insert outgoing item
            if (!$this->insert($data)) {
                throw new \Exception('Gagal menyimpan data barang keluar');
            }

            // Update product stock (reduce)
            $newStock = $product['stock'] - $data['quantity'];
            if (!$productModel->update($data['product_id'], ['stock' => $newStock])) {
                throw new \Exception('Gagal mengupdate stok produk');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'message' => 'Barang keluar berhasil dicatat'];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getOutgoingStatistics()
    {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');

        // Today's statistics
        $todayStats = $this->where('DATE(date)', $today)
            ->selectSum('quantity', 'total_quantity')
            ->selectCount('id', 'total_count')
            ->first();

        // This month's statistics
        $monthStats = $this->where('DATE_FORMAT(date, "%Y-%m")', $thisMonth)
            ->selectCount('id', 'total_count')
            ->first();

        return [
            'today_count' => $todayStats['total_count'] ?? 0,
            'today_quantity' => $todayStats['total_quantity'] ?? 0,
            'month_count' => $monthStats['total_count'] ?? 0
        ];
    }

    public function getOutgoingItemsWithDetails($limit = null, $offset = null, $search = null, $startDate = null, $endDate = null)
    {
        $builder = $this->select('outgoing_items.*, 
                                 products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 categories.name as category_name,
                                 users.full_name as user_name')
            ->join('products', 'products.id = outgoing_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->join('users', 'users.id = outgoing_items.user_id');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('outgoing_items.recipient', $search)
                ->orLike('outgoing_items.description', $search)
                ->groupEnd();
        }

        if ($startDate) {
            $builder->where('DATE(outgoing_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(outgoing_items.date) <=', $endDate);
        }

        $builder->orderBy('outgoing_items.date', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    public function countOutgoingItemsWithDetails($search = null, $startDate = null, $endDate = null)
    {
        $builder = $this->join('products', 'products.id = outgoing_items.product_id')
            ->join('categories', 'categories.id = products.category_id');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('outgoing_items.recipient', $search)
                ->orLike('outgoing_items.description', $search)
                ->groupEnd();
        }

        if ($startDate) {
            $builder->where('DATE(outgoing_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(outgoing_items.date) <=', $endDate);
        }

        return $builder->countAllResults();
    }

    public function getOutgoingItemWithDetails($id)
    {
        return $this->select('outgoing_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit,
                             categories.name as category_name,
                             users.full_name as user_name')
            ->join('products', 'products.id = outgoing_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->join('users', 'users.id = outgoing_items.user_id')
            ->where('outgoing_items.id', $id)
            ->first();
    }

    public function updateOutgoingItem($id, $data)
    {
        $this->db->transStart();

        try {
            // Get current item data
            $currentItem = $this->find($id);
            if (!$currentItem) {
                throw new \Exception('Data barang keluar tidak ditemukan');
            }

            $productModel = new \App\Models\ProductModel();
            $product = $productModel->find($currentItem['product_id']);

            if (!$product) {
                throw new \Exception('Produk tidak ditemukan');
            }

            // Calculate stock adjustment
            $oldQuantity = $currentItem['quantity'];
            $newQuantity = $data['quantity'];
            $stockAdjustment = $oldQuantity - $newQuantity; // Positive = add to stock, Negative = remove from stock

            $newStock = $product['stock'] + $stockAdjustment;

            // Check if new quantity exceeds available stock
            if ($newStock < 0) {
                throw new \Exception('Stok tidak mencukupi untuk perubahan ini. Stok tersedia: ' . $product['stock'] . ' ' . $product['unit']);
            }

            // Update outgoing item
            if (!$this->update($id, $data)) {
                throw new \Exception('Gagal mengupdate data barang keluar');
            }

            // Update product stock
            if (!$productModel->update($currentItem['product_id'], ['stock' => $newStock])) {
                throw new \Exception('Gagal mengupdate stok produk');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'message' => 'Data barang keluar berhasil diupdate'];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteOutgoingItem($id)
    {
        $this->db->transStart();

        try {
            // Get item details
            $item = $this->find($id);
            if (!$item) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Delete outgoing item
            if (!$this->delete($id)) {
                throw new \Exception('Gagal menghapus item');
            }

            // Restore product stock (add back the outgoing quantity)
            $productModel = new \App\Models\ProductModel();
            $product = $productModel->find($item['product_id']);
            $newStock = $product['stock'] + $item['quantity'];

            $productModel->update($item['product_id'], ['stock' => $newStock]);

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

    public function bulkInsert($items)
    {
        $this->db->transStart();

        try {
            $productModel = new \App\Models\ProductModel();

            // Validate all items first
            foreach ($items as $item) {
                $product = $productModel->find($item['product_id']);
                if (!$product) {
                    throw new \Exception('Produk dengan ID ' . $item['product_id'] . ' tidak ditemukan');
                }

                if ($product['stock'] < $item['quantity']) {
                    throw new \Exception('Stok tidak mencukupi untuk produk: ' . $product['name'] . '. Stok tersedia: ' . $product['stock']);
                }
            }

            // Insert all items and update stock
            foreach ($items as $item) {
                // Insert outgoing item
                if (!$this->insert($item)) {
                    throw new \Exception('Gagal menyimpan item');
                }

                // Update product stock
                $product = $productModel->find($item['product_id']);
                $newStock = $product['stock'] - $item['quantity'];
                $productModel->update($item['product_id'], ['stock' => $newStock]);
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

    public function getRecentOutgoing($limit = 5)
    {
        return $this->select('outgoing_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit,
                             users.full_name as user_name')
            ->join('products', 'products.id = outgoing_items.product_id')
            ->join('users', 'users.id = outgoing_items.user_id')
            ->orderBy('outgoing_items.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
