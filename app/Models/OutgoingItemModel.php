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

    public function getOutgoingItemsWithDetails($limit = null, $offset = null, $search = null, $startDate = null, $endDate = null, $productId = null)
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
                ->orLike('outgoing_items.description', $search)
                ->orLike('outgoing_items.recipient', $search)
                ->groupEnd();
        }

        if ($startDate) {
            $builder->where('DATE(outgoing_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(outgoing_items.date) <=', $endDate);
        }

        if ($productId) {
            $builder->where('outgoing_items.product_id', $productId);
        }

        $builder->orderBy('outgoing_items.date', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    public function countOutgoingItemsWithDetails($search = null, $startDate = null, $endDate = null, $productId = null)
    {
        $builder = $this->join('products', 'products.id = outgoing_items.product_id')
            ->join('categories', 'categories.id = products.category_id');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('outgoing_items.description', $search)
                ->orLike('outgoing_items.recipient', $search)
                ->groupEnd();
        }

        if ($startDate) {
            $builder->where('DATE(outgoing_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(outgoing_items.date) <=', $endDate);
        }

        if ($productId) {
            $builder->where('outgoing_items.product_id', $productId);
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

            // Insert outgoing item - Database trigger akan otomatis mengurangi stok
            $outgoingId = $this->insert($data);

            if (!$outgoingId) {
                throw new \Exception('Gagal menambahkan barang keluar');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'id' => $outgoingId];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateOutgoingItem($id, $data)
    {
        $this->db->transStart();

        try {
            // Get original item
            $originalItem = $this->find($id);
            if (!$originalItem) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Validasi stok jika quantity berubah
            if ($data['quantity'] != $originalItem['quantity']) {
                $productModel = new \App\Models\ProductModel();
                $product = $productModel->find($originalItem['product_id']);

                // Hitung stok setelah perubahan
                // Current stock + original quantity - new quantity
                $stockAfterChange = $product['stock'] + $originalItem['quantity'] - $data['quantity'];

                if ($stockAfterChange < 0) {
                    throw new \Exception('Stok tidak mencukupi untuk perubahan ini');
                }
            }

            // Update outgoing item - Database trigger akan otomatis menyesuaikan stok
            if (!$this->update($id, $data)) {
                throw new \Exception('Gagal mengupdate item');
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

    public function deleteOutgoingItem($id)
    {
        $this->db->transStart();

        try {
            // Get item details untuk validasi
            $item = $this->find($id);
            if (!$item) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Delete outgoing item
            // PENTING: JANGAN tambahkan manual stock adjustment!
            // Database trigger 'after_outgoing_delete' akan otomatis menambah stok kembali
            if (!$this->delete($id)) {
                throw new \Exception('Gagal menghapus item');
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

    public function getOutgoingReport($startDate = null, $endDate = null, $productId = null, $categoryId = null)
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

        if ($startDate) {
            $builder->where('DATE(outgoing_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(outgoing_items.date) <=', $endDate);
        }

        if ($productId) {
            $builder->where('outgoing_items.product_id', $productId);
        }

        if ($categoryId) {
            $builder->where('products.category_id', $categoryId);
        }

        return $builder->orderBy('outgoing_items.date', 'DESC')
            ->findAll();
    }

    public function getOutgoingStatistics()
    {
        $stats = [];

        // Total outgoing items
        $stats['total_items'] = $this->countAll();

        // Today's outgoing
        $stats['today_outgoing'] = $this->where('DATE(date)', date('Y-m-d'))->countAllResults(false);

        // This month's outgoing
        $stats['monthly_outgoing'] = $this->where('YEAR(date)', date('Y'))
            ->where('MONTH(date)', date('m'))
            ->countAllResults(false);

        // Total quantity issued
        $totalQuantity = $this->selectSum('quantity')->first();
        $stats['total_quantity'] = $totalQuantity['quantity'] ?? 0;

        // Most issued product
        $mostIssued = $this->select('products.name, products.code, SUM(outgoing_items.quantity) as total_quantity')
            ->join('products', 'products.id = outgoing_items.product_id')
            ->groupBy('products.id, products.name, products.code')
            ->orderBy('total_quantity', 'DESC')
            ->first();
        $stats['most_issued_product'] = $mostIssued;

        // Recent outgoing (last 7 days)
        $stats['recent_outgoing'] = $this->where('date >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->countAllResults(false);

        return $stats;
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

    public function getOutgoingByProduct($productId, $limit = null)
    {
        $builder = $this->select('outgoing_items.*, 
                                 users.full_name as user_name')
            ->join('users', 'users.id = outgoing_items.user_id', 'left')
            ->where('outgoing_items.product_id', $productId)
            ->orderBy('outgoing_items.date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }
}
