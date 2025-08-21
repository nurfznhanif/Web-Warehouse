<?php

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
    protected $updatedField = null; // tidak ada updated_at di tabel

    // Validasi yang lebih simple
    protected $validationRules = [
        'purchase_id' => 'required|integer',
        'product_id' => 'required|integer',
        'quantity' => 'required|numeric|greater_than[0]',
        'price' => 'required|numeric|greater_than[0]'
    ];

    protected $validationMessages = [
        'purchase_id' => [
            'required' => 'Purchase ID required',
            'integer' => 'Purchase ID must be integer'
        ],
        'product_id' => [
            'required' => 'Product required',
            'integer' => 'Product must be integer'
        ],
        'quantity' => [
            'required' => 'Quantity required',
            'numeric' => 'Quantity must be numeric',
            'greater_than' => 'Quantity must be greater than 0'
        ],
        'price' => [
            'required' => 'Price required',
            'numeric' => 'Price must be numeric',
            'greater_than' => 'Price must be greater than 0'
        ]
    ];

    /**
 * Get purchase details with product information
 */
public function getDetailsByPurchase($purchaseId)
{
    return $this->select('purchase_details.*, 
                         products.name as product_name, 
                         products.code as product_code,
                         products.unit, 
                         categories.name as category_name')
               ->join('products', 'products.id = purchase_details.product_id')
               ->join('categories', 'categories.id = products.category_id', 'left')
               ->where('purchase_details.purchase_id', $purchaseId)
               ->orderBy('products.name', 'ASC')
               ->findAll();
}

/**
 * Get items that haven't been fully received yet
 */
public function getUnreceivedItems($purchaseId)
{
    $items = $this->getDetailsByPurchase($purchaseId);
    $unreceivedItems = [];
    
    $incomingModel = new \App\Models\IncomingItemModel();
    
    foreach ($items as $item) {
        $receivedQty = $incomingModel->where('purchase_id', $purchaseId)
                                    ->where('product_id', $item['product_id'])
                                    ->selectSum('quantity')
                                    ->first()['quantity'] ?? 0;
        
        if ($receivedQty < $item['quantity']) {
            $item['received_quantity'] = $receivedQty;
            $item['remaining_quantity'] = $item['quantity'] - $receivedQty;
            $unreceivedItems[] = $item;
        }
    }
    
    return $unreceivedItems;
}

/**
 * Check if product exists in purchase
 */
public function checkProductInPurchase($purchaseId, $productId)
{
    return $this->where('purchase_id', $purchaseId)
               ->where('product_id', $productId)
               ->countAllResults() > 0;
}

/**
 * Get purchase detail for specific product
 */
public function getPurchaseDetailByProduct($purchaseId, $productId)
{
    return $this->select('purchase_details.*, 
                         products.name as product_name, 
                         products.code as product_code,
                         products.unit')
               ->join('products', 'products.id = purchase_details.product_id')
               ->where('purchase_details.purchase_id', $purchaseId)
               ->where('purchase_details.product_id', $productId)
               ->first();
}
}
