<?php

namespace App\Models;

use CodeIgniter\Model;

class OutgoingItemModel extends Model
{
    protected $table = 'outgoing_items';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_id', 'date', 'quantity', 'description'];
    protected $useTimestamps = true;

    public function getOutgoingItemsWithDetails($startDate = null, $endDate = null)
    {
        $builder = $this->select('outgoing_items.*, products.name as product_name, products.code as product_code')
            ->join('products', 'products.id = outgoing_items.product_id');

        if ($startDate && $endDate) {
            $builder->where('outgoing_items.date >=', $startDate)
                ->where('outgoing_items.date <=', $endDate);
        }

        return $builder->findAll();
    }
}