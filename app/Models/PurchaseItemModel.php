<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseItemModel extends Model
{
    protected $table = 'purchase_items';
    protected $primaryKey = 'id';
    protected $allowedFields = ['purchase_id', 'product_id', 'quantity'];
    protected $useTimestamps = true;
}