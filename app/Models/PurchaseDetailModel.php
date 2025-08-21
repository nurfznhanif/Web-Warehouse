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
}
