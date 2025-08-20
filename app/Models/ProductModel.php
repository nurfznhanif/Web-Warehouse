<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'category_id',
        'name',
        'code',
        'unit',
        'stock',
        'min_stock'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'category_id' => 'required|integer',
        'name' => 'required|min_length[3]|max_length[200]',
        'code' => 'required|min_length[3]|max_length[50]|is_unique[products.code,id,{id}]',
        'unit' => 'required|min_length[2]|max_length[50]',
        'stock' => 'permit_empty|decimal',
        'min_stock' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [
        'category_id' => [
            'required' => 'Kategori harus dipilih',
            'integer' => 'Kategori tidak valid'
        ],
        'name' => [
            'required' => 'Nama produk harus diisi',
            'min_length' => 'Nama produk minimal 3 karakter',
            'max_length' => 'Nama produk maksimal 200 karakter'
        ],
        'code' => [
            'required' => 'Kode produk harus diisi',
            'min_length' => 'Kode produk minimal 3 karakter',
            'max_length' => 'Kode produk maksimal 50 karakter',
            'is_unique' => 'Kode produk sudah digunakan'
        ],
        'unit' => [
            'required' => 'Satuan harus diisi',
            'min_length' => 'Satuan minimal 2 karakter',
            'max_length' => 'Satuan maksimal 50 karakter'
        ]
    ];

    public function getProductsWithCategory($limit = null, $offset = null, $search = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, c.name as category_name');
        $builder->join('categories c', 'p.category_id = c.id');

        if ($search) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.code', $search)
                ->orLike('c.name', $search)
                ->groupEnd();
        }

        $builder->orderBy('p.created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function countProductsWithCategory($search = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->join('categories c', 'p.category_id = c.id');

        if ($search) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.code', $search)
                ->orLike('c.name', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getProductWithCategory($id)
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, c.name as category_name')
            ->join('categories c', 'p.category_id = c.id')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getLowStockProducts()
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, c.name as category_name')
            ->join('categories c', 'p.category_id = c.id')
            ->where('p.stock <=', 'p.min_stock', false)
            ->orderBy('p.stock', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function updateStock($productId, $quantity, $operation = 'add')
    {
        $product = $this->find($productId);
        if (!$product) {
            return false;
        }

        $newStock = $operation === 'add' ?
            $product['stock'] + $quantity :
            $product['stock'] - $quantity;

        // Prevent negative stock
        if ($newStock < 0) {
            return false;
        }

        return $this->update($productId, ['stock' => $newStock]);
    }

    public function getStockReport()
    {
        return $this->db->table('v_product_stock')
            ->orderBy('stock_status', 'ASC')
            ->orderBy('stock', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getProductsByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function checkStockAvailability($productId, $requestedQuantity)
    {
        $product = $this->find($productId);
        if (!$product) {
            return ['available' => false, 'message' => 'Produk tidak ditemukan'];
        }

        if ($product['stock'] < $requestedQuantity) {
            return [
                'available' => false,
                'message' => "Stok tidak mencukupi. Stok tersedia: {$product['stock']} {$product['unit']}"
            ];
        }

        return ['available' => true, 'message' => 'Stok tersedia'];
    }

    public function getProductStatistics()
    {
        $stats = [];

        // Total products
        $stats['total_products'] = $this->countAll();

        // Low stock products
        $lowStockCount = $this->db->table($this->table)
            ->where('stock <=', 'min_stock', false)
            ->countAllResults();
        $stats['low_stock_products'] = $lowStockCount;

        // Out of stock products
        $outOfStockCount = $this->where('stock', 0)->countAllResults();
        $stats['out_of_stock_products'] = $outOfStockCount;

        // Products by category
        $categoryStats = $this->db->table($this->table . ' p')
            ->select('c.name as category_name, COUNT(p.id) as product_count')
            ->join('categories c', 'p.category_id = c.id')
            ->groupBy('c.id, c.name')
            ->get()
            ->getResultArray();
        $stats['products_by_category'] = $categoryStats;

        return $stats;
    }

    public function searchProducts($keyword, $categoryId = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, c.name as category_name');
        $builder->join('categories c', 'p.category_id = c.id');

        $builder->groupStart()
            ->like('p.name', $keyword)
            ->orLike('p.code', $keyword)
            ->groupEnd();

        if ($categoryId) {
            $builder->where('p.category_id', $categoryId);
        }

        return $builder->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
