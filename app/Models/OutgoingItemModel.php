<?php

namespace App\Models;

use CodeIgniter\Model;

class OutgoingItemModel extends Model
{
    protected $table = 'outgoing_items';
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
        'purchase_id' => 'permit_empty|integer',
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
            'integer' => 'Purchase ID tidak valid'
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
                             products.stock as current_stock,
                             categories.name as category_name,
                             users.full_name as user_name')
            ->join('products', 'products.id = outgoing_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->join('users', 'users.id = outgoing_items.user_id')
            ->where('outgoing_items.id', $id)
            ->first();
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

    public function getOutgoingByDate($startDate, $endDate)
    {
        return $this->select('outgoing_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit')
            ->join('products', 'products.id = outgoing_items.product_id')
            ->where('DATE(outgoing_items.date) >=', $startDate)
            ->where('DATE(outgoing_items.date) <=', $endDate)
            ->orderBy('outgoing_items.date', 'DESC')
            ->findAll();
    }

    public function getOutgoingByProduct($productId, $limit = null)
    {
        $builder = $this->where('product_id', $productId)
            ->orderBy('date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    public function getDailyOutgoingData($days = 30)
    {
        $dailyData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateLabel = date('M d', strtotime("-{$i} days"));

            $count = $this->where('DATE(date)', $date)->countAllResults(false);
            $quantity = $this->where('DATE(date)', $date)
                ->selectSum('quantity')
                ->first()['quantity'] ?? 0;

            $dailyData[] = [
                'date' => $dateLabel,
                'count' => $count,
                'quantity' => $quantity
            ];
        }

        return $dailyData;
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

    public function addOutgoingItem($data)
    {
        $this->db->transStart();

        try {
            // Check stock availability
            $productModel = new ProductModel();
            $product = $productModel->find($data['product_id']);

            if (!$product) {
                throw new \Exception('Produk tidak ditemukan');
            }

            if ($product['stock'] < $data['quantity']) {
                throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $product['stock'] . ' ' . $product['unit']);
            }

            // Insert outgoing item
            $outgoingId = $this->insert($data);

            if (!$outgoingId) {
                throw new \Exception('Gagal menambahkan barang keluar');
            }

            // Update product stock (handled by database trigger)

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

            // Check stock availability for the adjustment
            $stockAdjustment = $data['quantity'] - $originalItem['quantity'];

            if ($stockAdjustment > 0) {
                // Need more stock
                $productModel = new ProductModel();
                $product = $productModel->find($originalItem['product_id']);

                if ($product['stock'] < $stockAdjustment) {
                    throw new \Exception('Stok tidak mencukupi untuk perubahan ini');
                }
            }

            // Update outgoing item
            if (!$this->update($id, $data)) {
                throw new \Exception('Gagal mengupdate item');
            }

            // Manually update product stock since trigger won't handle updates properly
            if ($stockAdjustment != 0) {
                $productModel = new ProductModel();
                $product = $productModel->find($originalItem['product_id']);
                $newStock = $product['stock'] - $stockAdjustment; // Subtract because it's outgoing

                if ($newStock < 0) {
                    throw new \Exception('Stok tidak boleh negatif');
                }

                $productModel->update($originalItem['product_id'], ['stock' => $newStock]);
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
            // Get item details
            $item = $this->find($id);
            if (!$item) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Delete outgoing item
            if (!$this->delete($id)) {
                throw new \Exception('Gagal menghapus item');
            }

            // Manually adjust product stock (add back the outgoing quantity)
            $productModel = new ProductModel();
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

    public function getOutgoingSummary($startDate = null, $endDate = null)
    {
        $builder = $this->select('products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 categories.name as category_name,
                                 SUM(outgoing_items.quantity) as total_quantity,
                                 COUNT(outgoing_items.id) as transaction_count')
            ->join('products', 'products.id = outgoing_items.product_id')
            ->join('categories', 'categories.id = products.category_id');

        if ($startDate) {
            $builder->where('DATE(outgoing_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(outgoing_items.date) <=', $endDate);
        }

        return $builder->groupBy('products.id, products.name, products.code, products.unit, categories.name')
            ->orderBy('total_quantity', 'DESC')
            ->findAll();
    }

    public function getTopIssuedProducts($limit = 10, $startDate = null, $endDate = null)
    {
        $builder = $this->select('products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 SUM(outgoing_items.quantity) as total_quantity,
                                 COUNT(outgoing_items.id) as transaction_count,
                                 MAX(outgoing_items.date) as last_issued')
            ->join('products', 'products.id = outgoing_items.product_id');

        if ($startDate) {
            $builder->where('DATE(outgoing_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(outgoing_items.date) <=', $endDate);
        }

        return $builder->groupBy('products.id, products.name, products.code, products.unit')
            ->orderBy('total_quantity', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getTopRecipients($limit = 10, $startDate = null, $endDate = null)
    {
        $builder = $this->select('recipient, 
                                 COUNT(id) as transaction_count,
                                 SUM(quantity) as total_quantity,
                                 MAX(date) as last_transaction')
            ->where('recipient IS NOT NULL')
            ->where('recipient !=', '');

        if ($startDate) {
            $builder->where('DATE(date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(date) <=', $endDate);
        }

        return $builder->groupBy('recipient')
            ->orderBy('transaction_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function bulkInsert($items)
    {
        $this->db->transStart();

        try {
            foreach ($items as $item) {
                $result = $this->addOutgoingItem($item);
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Bulk insert gagal');
            }

            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getMonthlyComparison($months = 12)
    {
        $monthlyData = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));

            $count = $this->where('DATE_FORMAT(date, "%Y-%m")', $month)
                ->countAllResults(false);

            $quantity = $this->where('DATE_FORMAT(date, "%Y-%m")', $month)
                ->selectSum('quantity')
                ->first()['quantity'] ?? 0;

            $monthlyData[] = [
                'month' => $monthLabel,
                'count' => $count,
                'quantity' => $quantity
            ];
        }

        return $monthlyData;
    }
}
