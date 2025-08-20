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
        'notes' => [
            'max_length' => 'Catatan maksimal 500 karakter'
        ],
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID tidak valid'
        ]
    ];

    public function getIncomingItemsWithDetails($limit = null, $offset = null, $search = null, $startDate = null, $endDate = null)
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
                       ->join('categories', 'categories.id = products.category_id')
                       ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                       ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
                       ->join('users', 'users.id = incoming_items.user_id');

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

    public function countIncomingItemsWithDetails($search = null, $startDate = null, $endDate = null)
    {
        $builder = $this->join('products', 'products.id = incoming_items.product_id')
                       ->join('categories', 'categories.id = products.category_id')
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

    public function getIncomingItemWithDetails($id)
    {
        return $this->select('incoming_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit,
                             categories.name as category_name,
                             purchases.id as purchase_number,
                             vendors.name as vendor_name,
                             users.full_name as user_name')
                   ->join('products', 'products.id = incoming_items.product_id')
                   ->join('categories', 'categories.id = products.category_id')
                   ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                   ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
                   ->join('users', 'users.id = incoming_items.user_id')
                   ->where('incoming_items.id', $id)
                   ->first();
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

    public function getIncomingByDate($startDate, $endDate)
    {
        return $this->select('incoming_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit')
                   ->join('products', 'products.id = incoming_items.product_id')
                   ->where('DATE(incoming_items.date) >=', $startDate)
                   ->where('DATE(incoming_items.date) <=', $endDate)
                   ->orderBy('incoming_items.date', 'DESC')
                   ->findAll();
    }

    public function getIncomingByProduct($productId, $limit = null)
    {
        $builder = $this->select('incoming_items.*, 
                                 purchases.id as purchase_number,
                                 vendors.name as vendor_name')
                       ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                       ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
                       ->where('incoming_items.product_id', $productId)
                       ->orderBy('incoming_items.date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    public function getDailyIncomingData($days = 30)
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

    public function addIncomingItem($data)
    {
        $this->db->transStart();

        try {
            // Validate if purchase exists and get purchase details
            if (isset($data['purchase_id']) && $data['purchase_id']) {
                $purchaseDetailModel = new PurchaseDetailModel();
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

            // Update product stock (handled by database trigger)
            
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

            // Manually update product stock since trigger won't handle updates
            if ($stockAdjustment != 0) {
                $productModel = new ProductModel();
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

            // Manually adjust product stock (reduce by the incoming quantity)
            $productModel = new ProductModel();
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
                       ->join('categories', 'categories.id = products.category_id')
                       ->join('purchases', 'purchases.id = incoming_items.purchase_id', 'left')
                       ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
                       ->join('users', 'users.id = incoming_items.user_id');

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

    public function getIncomingSummary($startDate = null, $endDate = null)
    {
        $builder = $this->select('products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 categories.name as category_name,
                                 SUM(incoming_items.quantity) as total_quantity,
                                 COUNT(incoming_items.id) as transaction_count')
                       ->join('products', 'products.id = incoming_items.product_id')
                       ->join('categories', 'categories.id = products.category_id');

        if ($startDate) {
            $builder->where('DATE(incoming_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(incoming_items.date) <=', $endDate);
        }

        return $builder->groupBy('products.id, products.name, products.code, products.unit, categories.name')
                      ->orderBy('total_quantity', 'DESC')
                      ->findAll();
    }

    public function getTopReceivedProducts($limit = 10, $startDate = null, $endDate = null)
    {
        $builder = $this->select('products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 SUM(incoming_items.quantity) as total_quantity,
                                 COUNT(incoming_items.id) as transaction_count,
                                 MAX(incoming_items.date) as last_received')
                       ->join('products', 'products.id = incoming_items.product_id');

        if ($startDate) {
            $builder->where('DATE(incoming_items.date) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(incoming_items.date) <=', $endDate);
        }

        return $builder->groupBy('products.id, products.name, products.code, products.unit')
                      ->orderBy('total_quantity', 'DESC')
                      ->limit($limit)
                      ->findAll();
    }

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
}