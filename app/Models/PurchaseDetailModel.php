<?php
// app/Models/PurchaseDetailModel.php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseDetailModel extends Model
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
    protected $updatedField = null;

    protected $validationRules = [
        'purchase_id' => 'required|integer',
        'product_id' => 'required|integer',
        'quantity' => 'required|decimal|greater_than[0]',
        'price' => 'required|decimal|greater_than[0]',
        'total' => 'required|decimal|greater_than[0]'
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
            'required' => 'Jumlah harus diisi',
            'decimal' => 'Jumlah harus berupa angka',
            'greater_than' => 'Jumlah harus lebih dari 0'
        ],
        'price' => [
            'required' => 'Harga harus diisi',
            'decimal' => 'Harga harus berupa angka',
            'greater_than' => 'Harga harus lebih dari 0'
        ],
        'total' => [
            'required' => 'Total harus diisi',
            'decimal' => 'Total harus berupa angka',
            'greater_than' => 'Total harus lebih dari 0'
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

    public function getDetailsByPurchase($purchaseId)
    {
        return $this->db->table($this->table . ' pd')
            ->select('pd.*, p.name as product_name, p.code as product_code,
                                p.unit, c.name as category_name')
            ->join('products p', 'pd.product_id = p.id')
            ->join('categories c', 'p.category_id = c.id')
            ->where('pd.purchase_id', $purchaseId)
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getDetailWithProduct($id)
    {
        return $this->db->table($this->table . ' pd')
            ->select('pd.*, p.name as product_name, p.code as product_code,
                                p.unit, p.stock as current_stock, c.name as category_name')
            ->join('products p', 'pd.product_id = p.id')
            ->join('categories c', 'p.category_id = c.id')
            ->where('pd.id', $id)
            ->get()
            ->getRowArray();
    }

    public function addPurchaseItem($data)
    {
        $this->db->transStart();

        try {
            // Insert purchase detail
            $detailId = $this->insert($data);

            if (!$detailId) {
                throw new \Exception('Gagal menambahkan item');
            }

            // Update purchase total amount
            $purchaseModel = new PurchaseModel();
            $purchaseModel->calculateTotalAmount($data['purchase_id']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'id' => $detailId];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updatePurchaseItem($id, $data)
    {
        $this->db->transStart();

        try {
            // Get purchase ID before update
            $detail = $this->find($id);
            if (!$detail) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Update purchase detail
            if (!$this->update($id, $data)) {
                throw new \Exception('Gagal mengupdate item');
            }

            // Update purchase total amount
            $purchaseModel = new PurchaseModel();
            $purchaseModel->calculateTotalAmount($detail['purchase_id']);

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
            // Get purchase ID before delete
            $detail = $this->find($id);
            if (!$detail) {
                throw new \Exception('Item tidak ditemukan');
            }

            // Delete purchase detail
            if (!$this->delete($id)) {
                throw new \Exception('Gagal menghapus item');
            }

            // Update purchase total amount
            $purchaseModel = new PurchaseModel();
            $purchaseModel->calculateTotalAmount($detail['purchase_id']);

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

    public function bulkInsert($purchaseId, $items)
    {
        $this->db->transStart();

        try {
            foreach ($items as $item) {
                $itemData = [
                    'purchase_id' => $purchaseId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price']
                ];

                if (!$this->insert($itemData)) {
                    throw new \Exception('Gagal menambahkan item: ' . $item['product_name']);
                }
            }

            // Update purchase total amount
            $purchaseModel = new PurchaseModel();
            $purchaseModel->calculateTotalAmount($purchaseId);

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

    public function getItemsByProduct($productId)
    {
        return $this->db->table($this->table . ' pd')
            ->select('pd.*, pu.purchase_date, pu.buyer_name, v.name as vendor_name')
            ->join('purchases pu', 'pd.purchase_id = pu.id')
            ->join('vendors v', 'pu.vendor_id = v.id')
            ->where('pd.product_id', $productId)
            ->orderBy('pu.purchase_date', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getTopPurchasedProducts($limit = 10, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' pd');
        $builder->select('p.name as product_name, p.code as product_code, p.unit,
                         SUM(pd.quantity) as total_quantity,
                         SUM(pd.total) as total_amount,
                         COUNT(pd.id) as purchase_count,
                         AVG(pd.price) as avg_price');
        $builder->join('products p', 'pd.product_id = p.id');
        $builder->join('purchases pu', 'pd.purchase_id = pu.id');

        if ($dateFrom) {
            $builder->where('DATE(pu.purchase_date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(pu.purchase_date) <=', $dateTo);
        }

        $builder->where('pu.status !=', 'cancelled');
        $builder->groupBy('p.id, p.name, p.code, p.unit');
        $builder->orderBy('total_quantity', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    public function getPurchaseDetailStatistics($purchaseId)
    {
        $stats = $this->db->table($this->table)
            ->select('COUNT(id) as item_count,
                                  SUM(quantity) as total_quantity,
                                  SUM(total) as total_amount,
                                  AVG(price) as avg_price,
                                  MIN(price) as min_price,
                                  MAX(price) as max_price')
            ->where('purchase_id', $purchaseId)
            ->get()
            ->getRowArray();

        return $stats;
    }

    public function checkDuplicateProduct($purchaseId, $productId, $excludeId = null)
    {
        $builder = $this->where('purchase_id', $purchaseId)
            ->where('product_id', $productId);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    public function getUnreceivedItems($purchaseId)
    {
        // Get items that haven't been fully received yet
        return $this->db->table($this->table . ' pd')
            ->select('pd.*, p.name as product_name, p.code as product_code, p.unit,
                                COALESCE(SUM(ii.quantity), 0) as received_quantity,
                                (pd.quantity - COALESCE(SUM(ii.quantity), 0)) as remaining_quantity')
            ->join('products p', 'pd.product_id = p.id')
            ->join('incoming_items ii', 'pd.purchase_id = ii.purchase_id AND pd.product_id = ii.product_id', 'left')
            ->where('pd.purchase_id', $purchaseId)
            ->groupBy('pd.id, p.name, p.code, p.unit')
            ->having('remaining_quantity >', 0)
            ->get()
            ->getResultArray();
    }

    public function getReceivingSummary($purchaseId)
    {
        return $this->db->table($this->table . ' pd')
            ->select('pd.*, p.name as product_name, p.code as product_code, p.unit,
                                COALESCE(SUM(ii.quantity), 0) as received_quantity,
                                (pd.quantity - COALESCE(SUM(ii.quantity), 0)) as remaining_quantity,
                                CASE 
                                    WHEN COALESCE(SUM(ii.quantity), 0) = 0 THEN "not_received"
                                    WHEN COALESCE(SUM(ii.quantity), 0) < pd.quantity THEN "partial"
                                    ELSE "complete"
                                END as receiving_status')
            ->join('products p', 'pd.product_id = p.id')
            ->join('incoming_items ii', 'pd.purchase_id = ii.purchase_id AND pd.product_id = ii.product_id', 'left')
            ->where('pd.purchase_id', $purchaseId)
            ->groupBy('pd.id, p.name, p.code, p.unit')
            ->get()
            ->getResultArray();
    }
}
