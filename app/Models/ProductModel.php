<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $allowedFields = ['category_id', 'name', 'code', 'unit', 'stock'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'category_id' => 'required',
        'name' => 'required|min_length[3]',
        'code' => 'required|is_unique[products.code,id,{id}]',
        'unit' => 'required',
        'stock' => 'permit_empty|decimal'
    ];

    public function getProductsWithCategory()
    {
        return $this->select('products.*, categories.name as category_name')
            ->join('categories', 'categories.id = products.category_id')
            ->findAll();
    }

    public function updateStock($productId, $quantity)
    {
        $product = $this->find($productId);
        if ($product) {
            $newStock = $product['stock'] + $quantity;
            // Pastikan stok tidak minus
            if ($newStock < 0) {
                return false;
            }
            return $this->update($productId, ['stock' => $newStock]);
        }
        return false;
    }
}