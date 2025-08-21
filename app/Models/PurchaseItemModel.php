<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseItemModel extends Model
{
    protected $table = 'purchase_details';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'purchase_id',
        'product_id',
        'quantity',
        'price',
        'total'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'purchase_id' => 'required|integer',
        'product_id' => 'required|integer',
        'quantity' => 'required|decimal|greater_than[0]',
        'price' => 'required|decimal|greater_than[0]',
        'total' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [
        'purchase_id' => [
            'required' => 'Purchase ID harus diisi',
            'integer' => 'Purchase ID tidak valid'
        ],
        'product_id' => [
            'required' => 'Produk harus dipilih',
            'integer' => 'Produk tidak valid'
        ],
        'quantity' => [
            'required' => 'Kuantitas harus diisi',
            'decimal' => 'Kuantitas harus berupa angka',
            'greater_than' => 'Kuantitas harus lebih dari 0'
        ],
        'price' => [
            'required' => 'Harga harus diisi',
            'decimal' => 'Harga harus berupa angka',
            'greater_than' => 'Harga harus lebih dari 0'
        ],
        'total' => [
            'decimal' => 'Total harus berupa angka'
        ]
    ];

    protected $beforeInsert = ['calculateTotal'];
    protected $beforeUpdate = ['calculateTotal'];

    protected function calculateTotal(array $data)
    {
        if (isset($data['data']['quantity']) && isset($data['data']['price'])) {
            $data['data']['total'] = $data['data']['quantity'] * $data['data']['price'];
        }
        return $data;
    }

    public function getItemsWithProducts($purchaseId)
    {
        return $this->select('purchase_details.*, products.name as product_name, 
                             products.code as product_code, products.unit,
                             categories.name as category_name')
            ->join('products', 'products.id = purchase_details.product_id')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('purchase_details.purchase_id', $purchaseId)
            ->orderBy('products.name', 'ASC')
            ->findAll();
    }

    public function getItemWithProduct($id)
    {
        return $this->select('purchase_details.*, products.name as product_name, 
                             products.code as product_code, products.unit,
                             products.stock as current_stock, 
                             categories.name as category_name')
            ->join('products', 'products.id = purchase_details.product_id')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('purchase_details.id', $id)
            ->first();
    }

    public function addPurchaseItem($data)
    {
        $this->db->transStart();

        try {
            // Calculate total if not provided
            if (!isset($data['total'])) {
                $data['total'] = $data['quantity'] * $data['price'];
            }

            // Insert purchase item
            $itemId = $this->insert($data);

            if (!$itemId) {
                throw new \Exception('Gagal menambahkan item');
            }

            // Update purchase total amount
            $purchaseModel = new PurchaseModel();
            $purchaseModel->calculateTotalAmount($data['purchase_id']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'id' => $itemId];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updatePurchaseItem($id, $data)
    {
        $this->db->transStart();

        try {
            // Get current item
            $currentItem = $this->find($id);
            if (!$currentItem) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Calculate total if not provided
            if (!isset($data['total'])) {
                $data['total'] = $data['quantity'] * $data['price'];
            }

            // Update item
            if (!$this->update($id, $data)) {
                throw new \Exception('Gagal memperbarui item');
            }

            // Update purchase total amount
            $purchaseModel = new PurchaseModel();
            $purchaseModel->calculateTotalAmount($currentItem['purchase_id']);

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

    public function deletePurchaseItem($id)
    {
        $this->db->transStart();

        try {
            // Get current item
            $currentItem = $this->find($id);
            if (!$currentItem) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Delete item
            if (!$this->delete($id)) {
                throw new \Exception('Gagal menghapus item');
            }

            // Update purchase total amount
            $purchaseModel = new PurchaseModel();
            $purchaseModel->calculateTotalAmount($currentItem['purchase_id']);

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

    public function getItemsByProduct($productId, $limit = null)
    {
        $builder = $this->select('purchase_details.*, purchases.purchase_date, 
                                  purchases.buyer_name, vendors.name as vendor_name')
            ->join('purchases', 'purchases.id = purchase_details.purchase_id')
            ->join('vendors', 'vendors.id = purchases.vendor_id', 'left')
            ->where('purchase_details.product_id', $productId)
            ->orderBy('purchases.purchase_date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    public function getTotalQuantityByProduct($productId, $purchaseId = null)
    {
        $builder = $this->selectSum('quantity')
            ->where('product_id', $productId);

        if ($purchaseId) {
            $builder->where('purchase_id', $purchaseId);
        }

        $result = $builder->first();
        return $result['quantity'] ?? 0;
    }

    public function getTotalValueByProduct($productId, $purchaseId = null)
    {
        $builder = $this->selectSum('total')
            ->where('product_id', $productId);

        if ($purchaseId) {
            $builder->where('purchase_id', $purchaseId);
        }

        $result = $builder->first();
        return $result['total'] ?? 0;
    }

    public function getTopPurchasedProducts($limit = 10)
    {
        return $this->select('products.name as product_name, products.code as product_code,
                             SUM(purchase_details.quantity) as total_quantity,
                             SUM(purchase_details.total) as total_value,
                             COUNT(DISTINCT purchase_details.purchase_id) as purchase_count')
            ->join('products', 'products.id = purchase_details.product_id')
            ->join('purchases', 'purchases.id = purchase_details.purchase_id')
            ->where('purchases.status !=', 'cancelled')
            ->groupBy('purchase_details.product_id, products.name, products.code')
            ->orderBy('total_quantity', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getItemStatistics($purchaseId = null)
    {
        $builder = $this->select('COUNT(*) as total_items,
                                 SUM(quantity) as total_quantity,
                                 SUM(total) as total_value,
                                 AVG(price) as average_price')
            ->join('purchases', 'purchases.id = purchase_details.purchase_id')
            ->where('purchases.status !=', 'cancelled');

        if ($purchaseId) {
            $builder->where('purchase_details.purchase_id', $purchaseId);
        }

        return $builder->first();
    }

    public function beforeDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];

        // Get the item to check its purchase status
        $item = $this->find($id);
        if ($item) {
            $purchaseModel = new PurchaseModel();
            $purchase = $purchaseModel->find($item['purchase_id']);

            if ($purchase && $purchase['status'] === 'received') {
                throw new \Exception('Tidak dapat menghapus item dari pembelian yang sudah diterima!');
            }
        }

        return $data;
    }
}
