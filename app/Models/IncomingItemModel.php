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
     * ✅ FIXED - menggunakan products.stock bukan current_stock
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
     * ✅ FIXED - menggunakan products.stock bukan current_stock
     */
    public function getIncomingItemWithDetails($id)
    {
        return $this->select('incoming_items.*, 
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
            ->join('users', 'users.id = incoming_items.user_id', 'left')
            ->where('incoming_items.id', $id)
            ->first();
    }

    /**
     * Add incoming item with stock management
     * HANYA SATU METHOD INI - TIDAK ADA DUPLIKASI!
     */
    public function addIncomingItem($data)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Validate purchase order if provided
            if (!empty($data['purchase_id'])) {
                $purchaseDetailModel = new \App\Models\PurchaseDetailModel();
                $purchaseDetail = $purchaseDetailModel->where('purchase_id', $data['purchase_id'])
                    ->where('product_id', $data['product_id'])
                    ->first();

                if (!$purchaseDetail) {
                    throw new \Exception('Produk tidak ditemukan dalam purchase order yang dipilih');
                }

                // Check if total received quantity doesn't exceed purchased quantity
                $totalReceived = $this->where('purchase_id', $data['purchase_id'])
                    ->where('product_id', $data['product_id'])
                    ->selectSum('quantity')
                    ->first()['quantity'] ?? 0;

                if (($totalReceived + $data['quantity']) > $purchaseDetail['quantity']) {
                    throw new \Exception('Jumlah yang diterima melebihi jumlah dalam purchase order');
                }
            }

            // Insert incoming item
            $this->insert($data);
            $incomingId = $this->getInsertID();

            if (!$incomingId) {
                throw new \Exception('Gagal menyimpan data barang masuk');
            }

            // Update product stock
            $productModel = new \App\Models\ProductModel();
            $product = $productModel->find($data['product_id']);

            if (!$product) {
                throw new \Exception('Produk tidak ditemukan');
            }

            $newStock = $product['current_stock'] + $data['quantity'];
            $updateStock = $productModel->update($data['product_id'], ['current_stock' => $newStock]);

            if (!$updateStock) {
                throw new \Exception('Gagal mengupdate stok produk');
            }

            // Update purchase order status if applicable
            if (!empty($data['purchase_id'])) {
                $this->updatePurchaseOrderProgress($data['purchase_id']);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal disimpan');
            }

            return [
                'success' => true,
                'message' => 'Barang masuk berhasil dicatat dan stok produk telah diperbarui',
                'incoming_id' => $incomingId
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update incoming item with stock adjustment
     * HANYA SATU METHOD INI - TIDAK ADA DUPLIKASI!
     */
    public function updateIncomingItem($id, $data)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get original data
            $originalItem = $this->find($id);
            if (!$originalItem) {
                throw new \Exception('Data barang masuk tidak ditemukan');
            }

            // Validate if product is changed and exists
            if (isset($data['product_id']) && $data['product_id'] != $originalItem['product_id']) {
                $productModel = new \App\Models\ProductModel();
                $newProduct = $productModel->find($data['product_id']);
                if (!$newProduct) {
                    throw new \Exception('Produk baru tidak ditemukan');
                }
            }

            // Update incoming item
            $updateResult = $this->update($id, $data);
            if (!$updateResult) {
                throw new \Exception('Gagal mengupdate data barang masuk');
            }

            // Calculate and apply stock adjustments
            $productModel = new \App\Models\ProductModel();

            // If product changed
            if (isset($data['product_id']) && $originalItem['product_id'] != $data['product_id']) {
                // Reduce stock from original product
                $originalProduct = $productModel->find($originalItem['product_id']);
                $newOriginalStock = $originalProduct['stock'] - $originalItem['quantity'];
                $productModel->update($originalItem['product_id'], ['stock' => $newOriginalStock]);

                // Add stock to new product
                $newProduct = $productModel->find($data['product_id']);
                $newProductStock = $newProduct['stock'] + $data['quantity'];
                $productModel->update($data['product_id'], ['stock' => $newProductStock]);
            } else {
                // Same product, adjust quantity difference
                $quantityDiff = $data['quantity'] - $originalItem['quantity'];
                if ($quantityDiff != 0) {
                    $product = $productModel->find($originalItem['product_id']);
                    $newStock = $product['stock'] + $quantityDiff;
                    if ($newStock < 0) {
                        throw new \Exception('Stok tidak boleh menjadi negatif');
                    }
                    $productModel->update($originalItem['product_id'], ['stock' => $newStock]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return [
                'success' => true,
                'message' => 'Data barang masuk berhasil diperbarui dan stok disesuaikan'
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete incoming item with stock adjustment
     * HANYA SATU METHOD INI - TIDAK ADA DUPLIKASI!
     */
    public function deleteIncomingItem($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get original data
            $incomingItem = $this->find($id);
            if (!$incomingItem) {
                throw new \Exception('Data barang masuk tidak ditemukan');
            }

            // Check if we can reduce stock
            $productModel = new \App\Models\ProductModel();
            $product = $productModel->find($incomingItem['product_id']);

            if (!$product) {
                throw new \Exception('Produk tidak ditemukan');
            }

            if ($product['current_stock'] < $incomingItem['quantity']) {
                throw new \Exception('Tidak dapat menghapus: stok produk akan menjadi negatif');
            }

            // Reduce stock
            $newStock = $product['current_stock'] - $incomingItem['quantity'];
            $updateStock = $productModel->update($incomingItem['product_id'], ['current_stock' => $newStock]);

            if (!$updateStock) {
                throw new \Exception('Gagal mengupdate stok produk');
            }

            // Delete incoming item
            $deleteResult = $this->delete($id);
            if (!$deleteResult) {
                throw new \Exception('Gagal menghapus data barang masuk');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return [
                'success' => true,
                'message' => 'Data barang masuk berhasil dihapus dan stok disesuaikan'
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get incoming history for a specific product
     */
    public function getIncomingHistory($productId, $limit = null)
    {
        $builder = $this->select('incoming_items.*, 
                                 products.name as product_name, 
                                 products.code as product_code,
                                 products.unit,
                                 purchases.id as purchase_number,
                                 vendors.name as vendor_name,
                                 users.full_name as user_name')
            ->join('products', 'products.id = incoming_items.product_id')
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

    /**
     * Get incoming statistics for dashboard
     */
    public function getIncomingStatistics()
    {
        $stats = [];

        // Total items
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

    /**
     * Get recent incoming items for dashboard
     */
    public function getRecentIncoming($limit = 5)
    {
        return $this->select('incoming_items.*, 
                             products.name as product_name,
                             products.unit')
            ->join('products', 'products.id = incoming_items.product_id')
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
                        'count' => (int)$result['count'],
                        'quantity' => (float)$result['quantity']
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
     * Update purchase order progress based on received items
     * PRIVATE METHOD - tidak duplikat
     */
    private function updatePurchaseOrderProgress($purchaseId)
    {
        try {
            $purchaseModel = new \App\Models\PurchaseModel();
            $purchaseDetailModel = new \App\Models\PurchaseDetailModel();

            // Get all purchase details
            $purchaseDetails = $purchaseDetailModel->where('purchase_id', $purchaseId)->findAll();

            if (empty($purchaseDetails)) {
                return;
            }

            $totalExpected = 0;
            $totalReceived = 0;

            foreach ($purchaseDetails as $detail) {
                $totalExpected += $detail['quantity'];

                // Get total received for this product from this purchase
                $received = $this->where('purchase_id', $purchaseId)
                    ->where('product_id', $detail['product_id'])
                    ->selectSum('quantity')
                    ->first();

                $totalReceived += $received['quantity'] ?? 0;
            }

            // Calculate progress percentage
            $progress = $totalExpected > 0 ? ($totalReceived / $totalExpected) * 100 : 0;

            // Update status based on progress
            $status = 'pending';
            if ($progress >= 100) {
                $status = 'completed';
            } elseif ($progress > 0) {
                $status = 'partial';
            }

            $purchaseModel->update($purchaseId, [
                'status' => $status,
                'progress' => min(100, round($progress, 2))
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the main transaction
            log_message('error', 'Failed to update purchase progress: ' . $e->getMessage());
        }
    }

    /**
     * Bulk insert for import functionality
     */
    public function bulkInsert($items)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $successCount = 0;
        $errors = [];

        try {
            foreach ($items as $index => $item) {
                $result = $this->addIncomingItem($item);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errors[] = "Row " . ($index + 1) . ": " . $result['message'];
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Bulk insert transaction failed');
            }

            return [
                'success' => true,
                'success_count' => $successCount,
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $errors
            ];
        }
    }
}
