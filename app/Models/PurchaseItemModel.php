<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseItemModel extends Model
{
    protected $table = 'purchase_items'; // atau 'purchase_details' sesuai struktur database
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_price',
        'total'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'purchase_id' => 'required|integer',
        'product_id' => 'required|integer|is_not_unique[products.id]',
        'quantity' => 'required|decimal|greater_than[0]',
        'unit_price' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'total' => 'permit_empty|decimal|greater_than_equal_to[0]'
    ];

    protected $validationMessages = [
        'purchase_id' => [
            'required' => 'Purchase ID harus diisi',
            'integer' => 'Purchase ID tidak valid'
        ],
        'product_id' => [
            'required' => 'Produk harus dipilih',
            'integer' => 'Produk tidak valid',
            'is_not_unique' => 'Produk yang dipilih tidak valid'
        ],
        'quantity' => [
            'required' => 'Jumlah harus diisi',
            'decimal' => 'Jumlah harus berupa angka',
            'greater_than' => 'Jumlah harus lebih dari 0'
        ],
        'unit_price' => [
            'decimal' => 'Harga satuan harus berupa angka',
            'greater_than_equal_to' => 'Harga satuan tidak boleh negatif'
        ],
        'total' => [
            'decimal' => 'Total harus berupa angka',
            'greater_than_equal_to' => 'Total tidak boleh negatif'
        ]
    ];

    public function getItemsWithProducts($purchaseId)
    {
        return $this->select('purchase_items.*, products.name as product_name, products.code as product_code, products.unit as product_unit')
                   ->join('products', 'products.id = purchase_items.product_id')
                   ->where('purchase_id', $purchaseId)
                   ->findAll();
    }

    public function getItemsWithDetails($purchaseId)
    {
        return $this->select('purchase_items.*, 
                             products.name as product_name, 
                             products.code as product_code,
                             products.unit as product_unit,
                             categories.name as category_name')
                   ->join('products', 'products.id = purchase_items.product_id')
                   ->join('categories', 'categories.id = products.category_id', 'left')
                   ->where('purchase_id', $purchaseId)
                   ->findAll();
    }

    public function getPurchaseItemStatistics($purchaseId)
    {
        $stats = [];
        
        // Total items count
        $stats['total_items'] = $this->where('purchase_id', $purchaseId)->countAllResults(false);
        
        // Total quantity
        $totalQuantity = $this->selectSum('quantity')
                             ->where('purchase_id', $purchaseId)
                             ->first();
        $stats['total_quantity'] = $totalQuantity['quantity'] ?? 0;
        
        // Total amount
        $totalAmount = $this->selectSum('total')
                           ->where('purchase_id', $purchaseId)
                           ->first();
        $stats['total_amount'] = $totalAmount['total'] ?? 0;
        
        // Average unit price
        if ($stats['total_quantity'] > 0 && $stats['total_amount'] > 0) {
            $stats['average_unit_price'] = $stats['total_amount'] / $stats['total_quantity'];
        } else {
            $stats['average_unit_price'] = 0;
        }
        
        return $stats;
    }

    public function updateItemPrices($purchaseId, $itemPrices)
    {
        $this->db->transStart();
        
        try {
            foreach ($itemPrices as $itemId => $prices) {
                $updateData = [];
                
                if (isset($prices['unit_price'])) {
                    $updateData['unit_price'] = $prices['unit_price'];
                }
                
                if (isset($prices['quantity'])) {
                    $quantity = $prices['quantity'];
                    $unitPrice = $prices['unit_price'] ?? 0;
                    $updateData['quantity'] = $quantity;
                    $updateData['total'] = $quantity * $unitPrice;
                } elseif (isset($prices['unit_price'])) {
                    // Update total based on existing quantity
                    $item = $this->find($itemId);
                    if ($item) {
                        $updateData['total'] = $item['quantity'] * $prices['unit_price'];
                    }
                }
                
                if (!empty($updateData)) {
                    $this->update($itemId, $updateData);
                }
            }
            
            $this->db->transCommit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return false;
        }
    }

    public function calculatePurchaseTotal($purchaseId)
    {
        $result = $this->selectSum('total')
                      ->where('purchase_id', $purchaseId)
                      ->first();
        
        return $result['total'] ?? 0;
    }

    public function getTopProductsByPurchase($limit = 10)
    {
        return $this->select('products.name as product_name, 
                             products.code as product_code,
                             SUM(purchase_items.quantity) as total_quantity,
                             COUNT(purchase_items.id) as purchase_count,
                             SUM(purchase_items.total) as total_amount')
                   ->join('products', 'products.id = purchase_items.product_id')
                   ->groupBy('purchase_items.product_id, products.name, products.code')
                   ->orderBy('total_quantity', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getItemsByDateRange($startDate, $endDate)
    {
        return $this->select('purchase_items.*, 
                             products.name as product_name,
                             products.code as product_code,
                             purchases.purchase_date,
                             vendors.name as vendor_name')
                   ->join('products', 'products.id = purchase_items.product_id')
                   ->join('purchases', 'purchases.id = purchase_items.purchase_id')
                   ->join('vendors', 'vendors.id = purchases.vendor_id')
                   ->where('purchases.purchase_date >=', $startDate)
                   ->where('purchases.purchase_date <=', $endDate)
                   ->orderBy('purchases.purchase_date', 'DESC')
                   ->findAll();
    }

    public function duplicateItems($originalPurchaseId, $newPurchaseId)
    {
        $originalItems = $this->where('purchase_id', $originalPurchaseId)->findAll();
        
        $this->db->transStart();
        
        try {
            foreach ($originalItems as $item) {
                $newItemData = [
                    'purchase_id' => $newPurchaseId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                    'total' => $item['total'] ?? 0
                ];
                
                $this->insert($newItemData);
            }
            
            $this->db->transCommit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return false;
        }
    }

    // Hook untuk auto-calculate total saat insert/update
    protected function beforeInsert(array $data)
    {
        return $this->calculateTotal($data);
    }

    protected function beforeUpdate(array $data)
    {
        return $this->calculateTotal($data);
    }

    private function calculateTotal(array $data)
    {
        if (isset($data['data'])) {
            $itemData = &$data['data'];
            
            if (isset($itemData['quantity']) && isset($itemData['unit_price'])) {
                $itemData['total'] = $itemData['quantity'] * $itemData['unit_price'];
            }
        }
        
        return $data;
    }

    public function getMonthlyPurchaseItems($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            
            $result = $this->select('SUM(quantity) as total_quantity, SUM(total) as total_amount, COUNT(id) as item_count')
                          ->join('purchases', 'purchases.id = purchase_items.purchase_id')
                          ->where('YEAR(purchases.purchase_date)', $year)
                          ->where('MONTH(purchases.purchase_date)', $month)
                          ->first();
            
            $monthlyData[] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'total_quantity' => $result['total_quantity'] ?? 0,
                'total_amount' => $result['total_amount'] ?? 0,
                'item_count' => $result['item_count'] ?? 0
            ];
        }

        return $monthlyData;
    }
}