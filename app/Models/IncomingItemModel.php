<?php

namespace App\Models;

use CodeIgniter\Model;

class IncomingItemModel extends Model
{
    protected $table = 'incoming_items';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_id', 'purchase_id', 'date', 'quantity'];
    protected $useTimestamps = true;

    public function getIncomingItemsWithDetails($startDate = null, $endDate = null)
    {
        $builder = $this->select('incoming_items.*, products.name as product_name, products.code as product_code, purchases.vendor_name')
            ->join('products', 'products.id = incoming_items.product_id')
            ->join('purchases', 'purchases.id = incoming_items.purchase_id');

        if ($startDate && $endDate) {
            $builder->where('incoming_items.date >=', $startDate)
                ->where('incoming_items.date <=', $endDate);
        }

        return $builder->findAll();
    }
}