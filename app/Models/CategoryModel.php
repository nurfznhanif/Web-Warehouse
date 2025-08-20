<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[categories.name,id,{id}]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama kategori harus diisi',
            'min_length' => 'Nama kategori minimal 3 karakter',
            'max_length' => 'Nama kategori maksimal 100 karakter',
            'is_unique' => 'Nama kategori sudah ada'
        ]
    ];

    public function getCategoriesWithProductCount()
    {
        return $this->select('categories.*, COUNT(products.id) as product_count')
                   ->join('products', 'products.category_id = categories.id', 'left')
                   ->groupBy('categories.id, categories.name, categories.created_at, categories.updated_at')
                   ->orderBy('categories.name', 'ASC')
                   ->findAll();
    }

    public function getCategoryWithProducts($id)
    {
        $category = $this->find($id);
        if (!$category) {
            return null;
        }

        $productModel = new \App\Models\ProductModel();
        $category['products'] = $productModel->where('category_id', $id)->findAll();
        $category['product_count'] = count($category['products']);

        return $category;
    }

    public function searchCategories($keyword)
    {
        return $this->like('name', $keyword)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getCategoryStatistics()
    {
        $stats = [];
        
        // Total categories
        $stats['total_categories'] = $this->countAll();
        
        // Categories with products
        $categoriesWithProducts = $this->select('categories.id')
                                      ->join('products', 'products.category_id = categories.id')
                                      ->groupBy('categories.id')
                                      ->countAllResults();
        $stats['categories_with_products'] = $categoriesWithProducts;
        
        // Empty categories
        $stats['empty_categories'] = $stats['total_categories'] - $categoriesWithProducts;
        
        // Most used category
        $mostUsed = $this->select('categories.name, COUNT(products.id) as product_count')
                        ->join('products', 'products.category_id = categories.id')
                        ->groupBy('categories.id, categories.name')
                        ->orderBy('product_count', 'DESC')
                        ->first();
        $stats['most_used_category'] = $mostUsed;

        return $stats;
    }

    public function beforeDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        
        // Check if category has products
        $productModel = new \App\Models\ProductModel();
        $productCount = $productModel->where('category_id', $id)->countAllResults();
        
        if ($productCount > 0) {
            throw new \Exception('Tidak dapat menghapus kategori yang masih memiliki produk!');
        }
        
        return $data;
    }

    public function getCategoriesForSelect()
    {
        return $this->select('id, name')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getPopularCategories($limit = 5)
    {
        return $this->select('categories.*, COUNT(products.id) as product_count')
                   ->join('products', 'products.category_id = categories.id', 'left')
                   ->groupBy('categories.id, categories.name, categories.created_at, categories.updated_at')
                   ->orderBy('product_count', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function bulkInsert($categories)
    {
        $this->db->transStart();
        
        foreach ($categories as $category) {
            if (!empty($category['name'])) {
                // Check if category already exists
                $existing = $this->where('name', $category['name'])->first();
                if (!$existing) {
                    $this->insert(['name' => $category['name']]);
                }
            }
        }
        
        $this->db->transComplete();
        return $this->db->transStatus();
    }

    public function getCategoryTrends()
    {
        // Get category creation trends over the last 12 months
        $trends = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));
            
            $count = $this->where('DATE_FORMAT(created_at, "%Y-%m")', $month)
                         ->countAllResults(false);
            
            $trends[] = [
                'month' => $monthLabel,
                'count' => $count
            ];
        }
        
        return $trends;
    }
}