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
        'date' => 'required|valid_date',
        'quantity' => 'required|decimal|greater_than[0]',
        'purchase_id' => 'permit_empty|integer',
        'notes' => 'permit_empty|max_length[500]',
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
        'purchase_id' => [
            'integer' => 'Purchase ID tidak valid'
        ],
        'notes' => [
            'max_length' => 'Catatan maksimal 500 karakter'
        ],
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID tidak valid'
        ]
    ];

    /**
     * Get incoming items with complete details
     */
    public function getIncomingItemsWithDetails($limit = null, $offset = null, $search = null, $startDate = null, $endDate = null)
    {
        $builder = $this->select('incoming_items.*, 
                                 products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 products.stock,
                                 categories.name as category_name,
                                 purchases.id as purchase_number,
                                 vendors.name as vendor_name,
                                 users.full_name as user_name')
                       ->join('products', 'products.id = incoming_items.product_id')
                       ->join('categories', 'categories.id = products.category_id', 'left')
                       ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                       ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
                       ->join('users', 'users.id = incoming_items.user_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('vendors.name', $search)
                ->groupEnd();
        }

        if ($startDate) {
            $builder->where('DATE(incoming_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(incoming_items.date) <=', $endDate);
        }

        $builder->orderBy('incoming_items.date', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    /**
     * Count incoming items with details for pagination
     */
    public function countIncomingItemsWithDetails($search = null, $startDate = null, $endDate = null)
    {
        $builder = $this->join('products', 'products.id = incoming_items.product_id')
                       ->join('categories', 'categories.id = products.category_id', 'left')
                       ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                       ->join('vendors', 'vendors.id = purchases.vendor_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('vendors.name', $search)
                ->groupEnd();
        }

        if ($startDate) {
            $builder->where('DATE(incoming_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(incoming_items.date) <=', $endDate);
        }

        return $builder->countAllResults();
    }

    /**
     * Get single incoming item with details
     */
    public function getIncomingItemWithDetails($id)
    {
        return $this->select('incoming_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit,
                             products.stock as current_stock,
                             categories.name as category_name,
                             purchases.id as purchase_number,
                             vendors.name as vendor_name,
                             users.full_name as user_name')
                   ->join('products', 'products.id = incoming_items.product_id')
                   ->join('categories', 'categories.id = products.category_id', 'left')
                   ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                   ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
                   ->join('users', 'users.id = incoming_items.user_id', 'left')
                   ->where('incoming_items.id', $id)
                   ->first();
    }

    /**
     * Add incoming item with business logic validation
     */
    public function addIncomingItem($data)
    {
        $this->db->transStart();

        try {
            // Validate if purchase exists and get purchase details
            if (isset($data['purchase_id']) && $data['purchase_id']) {
                $purchaseDetailModel = new \App\Models\PurchaseDetailModel();
                $purchaseDetail = $purchaseDetailModel->where('purchase_id', $data['purchase_id'])
                                                     ->where('product_id', $data['product_id'])
                                                     ->first();

                if (!$purchaseDetail) {
                    throw new \Exception('Produk tidak ditemukan dalam pembelian yang dipilih');
                }

                // Check if total received quantity doesn't exceed purchased quantity
                $totalReceived = $this->where('purchase_id', $data['purchase_id'])
                                     ->where('product_id', $data['product_id'])
                                     ->selectSum('quantity')
                                     ->first()['quantity'] ?? 0;

                if (($totalReceived + $data['quantity']) > $purchaseDetail['quantity']) {
                    throw new \Exception('Jumlah yang diterima melebihi jumlah pembelian');
                }
            }

            // Insert incoming item
            $incomingId = $this->insert($data);

            if (!$incomingId) {
                throw new \Exception('Gagal menambahkan barang masuk');
            }

            // Update product stock
            $productModel = new \App\Models\ProductModel();
            $product = $productModel->find($data['product_id']);
            $newStock = $product['stock'] + $data['quantity'];
            $productModel->update($data['product_id'], ['stock' => $newStock]);
            
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'id' => $incomingId];

        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update incoming item with stock adjustment
     */
    public function updateIncomingItem($id, $data)
    {
        $this->db->transStart();

        try {
            // Get original item
            $originalItem = $this->find($id);
            if (!$originalItem) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Calculate stock adjustment
            $stockAdjustment = $data['quantity'] - $originalItem['quantity'];

            // Update incoming item
            if (!$this->update($id, $data)) {
                throw new \Exception('Gagal mengupdate item');
            }

            // Update product stock if quantity changed
            if ($stockAdjustment != 0) {
                $productModel = new \App\Models\ProductModel();
                $product = $productModel->find($originalItem['product_id']);
                $newStock = $product['stock'] + $stockAdjustment;
                
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

    /**
     * Delete incoming item with stock adjustment
     */
    public function deleteIncomingItem($id)
    {
        $this->db->transStart();

        try {
            // Get item details
            $item = $this->find($id);
            if (!$item) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Delete incoming item
            if (!$this->delete($id)) {
                throw new \Exception('Gagal menghapus item');
            }

            // Adjust product stock (reduce by the incoming quantity)
            $productModel = new \App\Models\ProductModel();
            $product = $productModel->find($item['product_id']);
            $newStock = $product['stock'] - $item['quantity'];
            
            if ($newStock < 0) {
                throw new \Exception('Penghapusan akan menyebabkan stok negatif');
            }
            
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

    /**
     * Get incoming statistics
     */
    public function getIncomingStatistics()
    {
        $stats = [];

        // Total transactions
        $stats['total_transactions'] = $this->countAll();

        // Total quantity
        $totalQty = $this->selectSum('quantity')->first();
        $stats['total_quantity'] = $totalQty['quantity'] ?? 0;

        // Today's count
        $todayCount = $this->where('DATE(date)', date('Y-m-d'))->countAllResults();
        $stats['today_count'] = $todayCount;

        // From purchase count
        $fromPurchaseCount = $this->where('purchase_id IS NOT NULL')->countAllResults();
        $stats['from_purchase'] = $fromPurchaseCount;

        return $stats;
    }

    /**
     * Get recent incoming items
     */
    public function getRecentIncoming($limit = 5)
    {
        return $this->select('incoming_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit,
                             users.full_name as user_name')
                   ->join('products', 'products.id = incoming_items.product_id')
                   ->join('users', 'users.id = incoming_items.user_id', 'left')
                   ->orderBy('incoming_items.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get incoming items by product (for history)
     */
    public function getIncomingByProduct($productId, $limit = 50)
    {
        return $this->select('incoming_items.*, 
                             purchases.id as purchase_id,
                             vendors.name as vendor_name,
                             users.full_name as user_name')
                   ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                   ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
                   ->join('users', 'users.id = incoming_items.user_id', 'left')
                   ->where('incoming_items.product_id', $productId)
                   ->orderBy('incoming_items.date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get daily incoming data for charts
     */
    public function getDailyIncomingData($days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $results = $this->select("DATE(date) as date, 
                                 COUNT(*) as count, 
                                 SUM(quantity) as quantity")
                       ->where('DATE(date) >=', $startDate)
                       ->groupBy('DATE(date)')
                       ->orderBy('date', 'ASC')
                       ->findAll();
        
        // Fill missing dates with zero values
        $dailyData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateLabel = date('M j', strtotime($date));
            
            $found = false;
            foreach ($results as $result) {
                if ($result['date'] === $date) {
                    $dailyData[] = [
                        'date' => $dateLabel,
                        'count' => $result['count'],
                        'quantity' => $result['quantity']
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $dailyData[] = [
                    'date' => $dateLabel,
                    'count' => 0,
                    'quantity' => 0
                ];
            }
        }
        
        return $dailyData;
    }

    /**
     * Bulk insert for multiple items
     */
    public function bulkInsert($items)
    {
        $this->db->transStart();

        try {
            foreach ($items as $item) {
                $result = $this->addIncomingItem($item);
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

    /**
     * Get incoming report data
     */
    public function getIncomingReport($startDate = null, $endDate = null, $productId = null, $categoryId = null)
    {
        $builder = $this->select('incoming_items.*, 
                                 products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 categories.name as category_name,
                                 purchases.id as purchase_number,
                                 vendors.name as vendor_name,
                                 users.full_name as user_name')
                       ->join('products', 'products.id = incoming_items.product_id')
                       ->join('categories', 'categories.id = products.category_id', 'left')
                       ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                       ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
                       ->join('users', 'users.id = incoming_items.user_id', 'left');

        if ($startDate) {
            $builder->where('DATE(incoming_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(incoming_items.date) <=', $endDate);
        }

        if ($productId) {
            $builder->where('incoming_items.product_id', $productId);
        }

        if ($categoryId) {
            $builder->where('products.category_id', $categoryId);
        }

        return $builder->orderBy('incoming_items.date', 'DESC')
                      ->findAll();
    }
}