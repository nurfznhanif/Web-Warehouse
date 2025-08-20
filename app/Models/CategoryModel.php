<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[categories.name,id,{id}]',
        'description' => 'permit_empty|max_length[500]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama kategori harus diisi',
            'min_length' => 'Nama kategori minimal 3 karakter',
            'max_length' => 'Nama kategori maksimal 100 karakter',
            'is_unique' => 'Nama kategori sudah digunakan'
        ],
        'description' => [
            'max_length' => 'Deskripsi maksimal 500 karakter'
        ]
    ];

    public function getCategoriesWithProductCount($limit = null, $offset = null, $search = null)
    {
        $builder = $this->db->table($this->table . ' c');
        $builder->select('c.*, COUNT(p.id) as product_count');
        $builder->join('products p', 'c.id = p.category_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('c.name', $search)
                ->orLike('c.description', $search)
                ->groupEnd();
        }

        $builder->groupBy('c.id, c.name, c.description, c.created_at, c.updated_at');
        $builder->orderBy('c.created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function countCategoriesWithProductCount($search = null)
    {
        $builder = $this->db->table($this->table . ' c');
        $builder->join('products p', 'c.id = p.category_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('c.name', $search)
                ->orLike('c.description', $search)
                ->groupEnd();
        }

        $builder->groupBy('c.id');
        return $builder->countAllResults();
    }

    public function getCategoryWithProducts($id)
    {
        $category = $this->find($id);
        if (!$category) {
            return null;
        }

        // Get products in this category
        $products = $this->db->table('products')
            ->where('category_id', $id)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $category['products'] = $products;
        $category['product_count'] = count($products);

        return $category;
    }

    public function checkProductExists($categoryId)
    {
        return $this->db->table('products')
            ->where('category_id', $categoryId)
            ->countAllResults() > 0;
    }

    public function getCategoryStatistics()
    {
        $stats = [];

        // Total categories
        $stats['total_categories'] = $this->countAll();

        // Categories with products
        $categoriesWithProducts = $this->db->table($this->table . ' c')
            ->join('products p', 'c.id = p.category_id')
            ->groupBy('c.id')
            ->countAllResults();
        $stats['categories_with_products'] = $categoriesWithProducts;

        // Empty categories
        $stats['empty_categories'] = $stats['total_categories'] - $categoriesWithProducts;

        // Most used category
        $mostUsed = $this->db->table($this->table . ' c')
            ->select('c.name, COUNT(p.id) as product_count')
            ->join('products p', 'c.id = p.category_id')
            ->groupBy('c.id, c.name')
            ->orderBy('product_count', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        $stats['most_used_category'] = $mostUsed;

        return $stats;
    }

    public function getProductsByCategory($categoryId, $limit = null)
    {
        $builder = $this->db->table('products p');
        $builder->select('p.*, c.name as category_name');
        $builder->join('categories c', 'p.category_id = c.id');
        $builder->where('p.category_id', $categoryId);
        $builder->orderBy('p.name', 'ASC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    public function getCategoriesForSelect()
    {
        return $this->select('id, name')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function searchCategories($keyword)
    {
        return $this->like('name', $keyword)
            ->orLike('description', $keyword)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function getCategoryUsageReport()
    {
        return $this->db->table($this->table . ' c')
            ->select('c.id, c.name, c.description, 
                                COUNT(p.id) as total_products,
                                SUM(p.stock) as total_stock,
                                COUNT(CASE WHEN p.stock <= p.min_stock THEN 1 END) as low_stock_products')
            ->join('products p', 'c.id = p.category_id', 'left')
            ->groupBy('c.id, c.name, c.description')
            ->orderBy('total_products', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function beforeDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];

        // Check if category has products
        if ($this->checkProductExists($id)) {
            throw new \Exception('Tidak dapat menghapus kategori yang masih memiliki produk!');
        }

        return $data;
    }

    public function getRecentCategories($limit = 5)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getCategoryTrends()
    {
        // Get category creation trends over the last 12 months
        $trends = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));

            $count = $this->db->table($this->table)
                ->where('DATE_FORMAT(created_at, "%Y-%m")', $month)
                ->countAllResults();

            $trends[] = [
                'month' => $monthLabel,
                'count' => $count
            ];
        }

        return $trends;
    }

    public function bulkUpdate($categories)
    {
        $this->db->transStart();

        foreach ($categories as $category) {
            if (isset($category['id'])) {
                $this->update($category['id'], $category);
            } else {
                $this->insert($category);
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
