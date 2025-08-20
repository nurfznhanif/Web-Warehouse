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
        'code' => 'required|min_length[2]|max_length[50]|is_unique[products.code,id,{id}]',
        'unit' => 'required|min_length[1]|max_length[20]',
        'stock' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'min_stock' => 'permit_empty|decimal|greater_than_equal_to[0]'
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
            'min_length' => 'Kode produk minimal 2 karakter',
            'max_length' => 'Kode produk maksimal 50 karakter',
            'is_unique' => 'Kode produk sudah digunakan'
        ],
        'unit' => [
            'required' => 'Satuan harus diisi',
            'min_length' => 'Satuan minimal 1 karakter',
            'max_length' => 'Satuan maksimal 20 karakter'
        ],
        'stock' => [
            'decimal' => 'Stok harus berupa angka',
            'greater_than_equal_to' => 'Stok tidak boleh negatif'
        ],
        'min_stock' => [
            'decimal' => 'Minimum stok harus berupa angka',
            'greater_than_equal_to' => 'Minimum stok tidak boleh negatif'
        ]
    ];

    public function getProductsWithCategory($limit = null, $offset = null, $search = null)
    {
        $builder = $this->select('products.*, categories.name as category_name')
                       ->join('categories', 'categories.id = products.category_id');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('categories.name', $search)
                ->groupEnd();
        }

        $builder->orderBy('products.created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    public function countProductsWithCategory($search = null)
    {
        $builder = $this->join('categories', 'categories.id = products.category_id');

        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->orLike('categories.name', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getProductWithCategory($id)
    {
        return $this->select('products.*, categories.name as category_name')
                   ->join('categories', 'categories.id = products.category_id')
                   ->where('products.id', $id)
                   ->first();
    }

    public function getLowStockProducts($limit = null)
    {
        $builder = $this->select('products.*, categories.name as category_name')
                       ->join('categories', 'categories.id = products.category_id')
                       ->where('products.stock <=', 'products.min_stock', false)
                       ->orderBy('products.stock', 'ASC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    public function getOutOfStockProducts()
    {
        return $this->select('products.*, categories.name as category_name')
                   ->join('categories', 'categories.id = products.category_id')
                   ->where('products.stock', 0)
                   ->findAll();
    }

    public function updateStock($productId, $quantity, $operation = 'add')
    {
        $product = $this->find($productId);
        if (!$product) {
            return false;
        }

        if ($operation === 'add') {
            $newStock = $product['stock'] + $quantity;
        } else {
            $newStock = $product['stock'] - $quantity;
            
            // Prevent negative stock
            if ($newStock < 0) {
                return false;
            }
        }

        return $this->update($productId, ['stock' => $newStock]);
    }

    public function searchProducts($keyword, $limit = 10)
    {
        return $this->select('products.*, categories.name as category_name')
                   ->join('categories', 'categories.id = products.category_id')
                   ->groupStart()
                       ->like('products.name', $keyword)
                       ->orLike('products.code', $keyword)
                   ->groupEnd()
                   ->limit($limit)
                   ->findAll();
    }

    public function getProductStatistics()
    {
        $stats = [];
        
        // Total products
        $stats['total_products'] = $this->countAll();
        
        // Products by category
        $stats['by_category'] = $this->select('categories.name as category_name, COUNT(products.id) as count')
                                    ->join('categories', 'categories.id = products.category_id')
                                    ->groupBy('categories.id, categories.name')
                                    ->findAll();
        
        // Stock statistics
        $stats['low_stock_products'] = $this->where('stock <=', 'min_stock', false)->countAllResults(false);
        $stats['out_of_stock_products'] = $this->where('stock', 0)->countAllResults(false);
        $stats['in_stock_products'] = $this->where('stock >', 'min_stock', false)->countAllResults(false);
        
        // Total stock value (would need price field)
        $stats['total_stock_quantity'] = $this->selectSum('stock')->first()['stock'] ?? 0;

        return $stats;
    }

    public function getProductsByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getProductsForSelect()
    {
        return $this->select('id, name, code, unit, stock')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function checkStockAvailability($productId, $requiredQuantity)
    {
        $product = $this->find($productId);
        
        if (!$product) {
            return ['available' => false, 'message' => 'Produk tidak ditemukan'];
        }
        
        if ($product['stock'] < $requiredQuantity) {
            return [
                'available' => false, 
                'message' => "Stok tidak mencukupi. Stok tersedia: {$product['stock']} {$product['unit']}"
            ];
        }
        
        return ['available' => true, 'message' => 'Stok mencukupi'];
    }

    public function getTopProducts($limit = 10, $period = '30 days')
    {
        // This would require transaction history
        // For now, return products ordered by stock movement
        return $this->select('products.*, categories.name as category_name')
                   ->join('categories', 'categories.id = products.category_id')
                   ->orderBy('products.updated_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getProductMovement($productId, $startDate = null, $endDate = null)
    {
        // This would require joining with incoming and outgoing items
        $db = \Config\Database::connect();
        
        $incoming = $db->table('incoming_items')
                      ->select('date, quantity, "incoming" as type, notes as description')
                      ->where('product_id', $productId);
        
        if ($startDate) {
            $incoming->where('date >=', $startDate);
        }
        if ($endDate) {
            $incoming->where('date <=', $endDate);
        }
        
        $outgoing = $db->table('outgoing_items')
                      ->select('date, quantity, "outgoing" as type, description')
                      ->where('product_id', $productId);
        
        if ($startDate) {
            $outgoing->where('date >=', $startDate);
        }
        if ($endDate) {
            $outgoing->where('date <=', $endDate);
        }
        
        // Combine both queries
        $incomingResults = $incoming->get()->getResultArray();
        $outgoingResults = $outgoing->get()->getResultArray();
        
        $movements = array_merge($incomingResults, $outgoingResults);
        
        // Sort by date
        usort($movements, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $movements;
    }

    public function bulkUpdateStock($updates)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        foreach ($updates as $update) {
            if (isset($update['id']) && isset($update['stock'])) {
                $this->update($update['id'], ['stock' => $update['stock']]);
            }
        }
        
        $db->transComplete();
        return $db->transStatus();
    }

    public function getStockReport()
    {
        return $this->select('products.id, products.name, products.code, products.unit, 
                             products.stock, products.min_stock, categories.name as category_name,
                             CASE 
                                 WHEN products.stock = 0 THEN "out_of_stock"
                                 WHEN products.stock <= products.min_stock THEN "low_stock"
                                 ELSE "in_stock"
                             END as stock_status')
                   ->join('categories', 'categories.id = products.category_id')
                   ->orderBy('categories.name', 'ASC')
                   ->orderBy('products.name', 'ASC')
                   ->findAll();
    }

    public function beforeDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        
        // Check if product has transactions
        $db = \Config\Database::connect();
        
        $hasIncoming = $db->table('incoming_items')->where('product_id', $id)->countAllResults() > 0;
        $hasOutgoing = $db->table('outgoing_items')->where('product_id', $id)->countAllResults() > 0;
        $hasPurchaseDetails = $db->table('purchase_details')->where('product_id', $id)->countAllResults() > 0;
        
        if ($hasIncoming || $hasOutgoing || $hasPurchaseDetails) {
            throw new \Exception('Tidak dapat menghapus produk yang sudah memiliki transaksi!');
        }
        
        return $data;
    }

    public function generateProductCode($categoryId)
    {
        $categoryModel = new \App\Models\CategoryModel();
        $category = $categoryModel->find($categoryId);
        
        if (!$category) {
            return null;
        }
        
        // Generate code based on category name initials
        $words = explode(' ', $category['name']);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        // Get last product number for this category
        $lastProduct = $this->where('category_id', $categoryId)
                           ->like('code', $initials, 'after')
                           ->orderBy('id', 'DESC')
                           ->first();
        
        $nextNumber = 1;
        if ($lastProduct) {
            // Extract number from last code
            $lastCode = $lastProduct['code'];
            $numberPart = preg_replace('/[^0-9]/', '', $lastCode);
            if ($numberPart) {
                $nextNumber = intval($numberPart) + 1;
            }
        }
        
        return $initials . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}