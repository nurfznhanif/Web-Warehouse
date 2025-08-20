<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseModel extends Model
{
    protected $table = 'purchases';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vendor_name', 'vendor_address', 'purchase_date', 'buyer_name'];
    protected $useTimestamps = true;

    public function getPurchasesWithItems()
    {
        return $this->select('purchases.*, COUNT(purchase_items.id) as item_count')
            ->join('purchase_items', 'purchase_items.purchase_id = purchases.id', 'left')
            ->groupBy('purchases.id')
            ->findAll();
    }
}