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
        'name' => 'required|min_length[3]|max_length[255]',
        'code' => 'required|min_length[3]|max_length[50]|is_unique[products.code,id,{id}]',
        'unit' => 'required|max_length[20]',
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
            'max_length' => 'Nama produk maksimal 255 karakter'
        ],
        'code' => [
            'required' => 'Kode produk harus diisi',
            'min_length' => 'Kode produk minimal 3 karakter',
            'max_length' => 'Kode produk maksimal 50 karakter',
            'is_unique' => 'Kode produk sudah digunakan'
        ],
        'unit' => [
            'required' => 'Satuan harus diisi',
            'max_length' => 'Satuan maksimal 20 karakter'
        ],
        'stock' => [
            'decimal' => 'Stok harus berupa angka',
            'greater_than_equal_to' => 'Stok tidak boleh negatif'
        ],
        'min_stock' => [
            'decimal' => 'Minimum stok harus berupa angka',
            'greater_than_equal_to' => 'Minimum stok tidak boleh negatif'
        ],
    ];

    protected $beforeInsert = ['setDefaultValues'];
    protected $beforeUpdate = ['setDefaultValues'];

    protected function setDefaultValues(array $data)
    {
        if (isset($data['data']['code'])) {
            $data['data']['code'] = strtoupper($data['data']['code']);
        }
        
        if (!isset($data['data']['stock']) || $data['data']['stock'] === '') {
            $data['data']['stock'] = 0;
        }
        
        if (!isset($data['data']['min_stock']) || $data['data']['min_stock'] === '') {
            $data['data']['min_stock'] = 10;
        }
        
        return $data;
    }

    public function getProductsWithCategory($limit = null, $offset = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, c.name as category_name');
        $builder->join('categories c', 'p.category_id = c.id', 'left');
        $builder->orderBy('p.created_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }

    public function getProductWithDetails($id)
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, c.name as category_name')
            ->join('categories c', 'p.category_id = c.id', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }

    public function updateStock($productId, $quantity, $operation = 'add')
    {
        $product = $this->find($productId);
        if (!$product) {
            return false;
        }
        
        $newStock = $operation === 'add' 
            ? $product['stock'] + $quantity 
            : $product['stock'] - $quantity;
        
        // Pastikan stok tidak minus
        if ($newStock < 0) {
            return false;
        }
        
        return $this->update($productId, ['stock' => $newStock]);
    }

    public function addStock($productId, $quantity)
    {
        return $this->updateStock($productId, $quantity, 'add');
    }

    public function reduceStock($productId, $quantity)
    {
        return $this->updateStock($productId, $quantity, 'subtract');
    }

    public function getLowStockProducts($limit = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, c.name as category_name');
        $builder->join('categories c', 'p.category_id = c.id', 'left');
        $builder->where('p.stock <=', 'p.min_stock', false);
        $builder->orderBy('p.stock', 'ASC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    public function getOutOfStockProducts($limit = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, c.name as category_name');
        $builder->join('categories c', 'p.category_id = c.id', 'left');
        $builder->where('p.stock <=', 0);
        $builder->orderBy('p.updated_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    public function getStockStatistics()
    {
        $total = $this->countAll();
        
        $inStock = $this->db->table($this->table)
            ->where('stock >', 'min_stock', false)
            ->countAllResults();
            
        $lowStock = $this->db->table($this->table)
            ->where('stock >', 0)
            ->where('stock <=', 'min_stock', false)
            ->countAllResults();
            
        $outOfStock = $this->db->table($this->table)
            ->where('stock <=', 0)
            ->countAllResults();
        
        return [
            'total' => $total,
            'in_stock' => $inStock,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock
        ];
    }

    public function getProductStatistics($productId)
    {
        // Get incoming total
        $incomingTotal = $this->db->table('incoming_items')
            ->selectSum('quantity', 'total')
            ->where('product_id', $productId)
            ->get()
            ->getRow()
            ->total ?? 0;
        
        // Get outgoing total
        $outgoingTotal = $this->db->table('outgoing_items')
            ->selectSum('quantity', 'total')
            ->where('product_id', $productId)
            ->get()
            ->getRow()
            ->total ?? 0;
        
        // Get transaction counts
        $incomingCount = $this->db->table('incoming_items')
            ->where('product_id', $productId)
            ->countAllResults();
            
        $outgoingCount = $this->db->table('outgoing_items')
            ->where('product_id', $productId)
            ->countAllResults();
        
        return [
            'total_incoming' => $incomingTotal,
            'total_outgoing' => $outgoingTotal,
            'net_stock' => $incomingTotal - $outgoingTotal,
            'incoming_transactions' => $incomingCount,
            'outgoing_transactions' => $outgoingCount,
            'total_transactions' => $incomingCount + $outgoingCount
        ];
    }

    public function hasTransactions($productId)
    {
        $incomingCount = $this->db->table('incoming_items')
            ->where('product_id', $productId)
            ->countAllResults();
            
        $outgoingCount = $this->db->table('outgoing_items')
            ->where('product_id', $productId)
            ->countAllResults();
        
        return ($incomingCount + $outgoingCount) > 0;
    }

    public function getProductsForSelect($categoryId = null)
    {
        $builder = $this->select('id, name, code, unit, stock');
        
        if ($categoryId) {
            $builder->where('category_id', $categoryId);
        }
        
        return $builder->where('stock >', 0)
                      ->orderBy('name', 'ASC')
                      ->findAll();
    }

    public function searchProducts($keyword, $limit = 10)
    {
        return $this->like('name', $keyword)
                   ->orLike('code', $keyword)
                   ->limit($limit)
                   ->findAll();
    }

    public function getTopProducts($type = 'incoming', $limit = 10, $dateFrom = null, $dateTo = null)
    {
        $table = $type === 'incoming' ? 'incoming_items' : 'outgoing_items';
        
        $builder = $this->db->table($table . ' t');
        $builder->select('p.id, p.name, p.code, p.unit, SUM(t.quantity) as total_quantity, COUNT(t.id) as transaction_count');
        $builder->join('products p', 't.product_id = p.id');
        
        if ($dateFrom) {
            $builder->where('DATE(t.date) >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('DATE(t.date) <=', $dateTo);
        }
        
        $builder->groupBy('p.id, p.name, p.code, p.unit');
        $builder->orderBy('total_quantity', 'DESC');
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }

    public function getProductMovement($productId, $dateFrom = null, $dateTo = null)
    {
        $movements = [];
        
        // Get incoming items
        $incomingBuilder = $this->db->table('incoming_items i');
        $incomingBuilder->select('i.date, i.quantity, "incoming" as type, i.description, p.purchase_date, v.name as vendor_name');
        $incomingBuilder->join('purchases p', 'i.purchase_id = p.id', 'left');
        $incomingBuilder->join('vendors v', 'p.vendor_id = v.id', 'left');
        $incomingBuilder->where('i.product_id', $productId);
        
        if ($dateFrom) {
            $incomingBuilder->where('DATE(i.date) >=', $dateFrom);
        }
        if ($dateTo) {
            $incomingBuilder->where('DATE(i.date) <=', $dateTo);
        }
        
        $incoming = $incomingBuilder->get()->getResultArray();
        
        // Get outgoing items
        $outgoingBuilder = $this->db->table('outgoing_items o');
        $outgoingBuilder->select('o.date, o.quantity, "outgoing" as type, o.description, NULL as purchase_date, NULL as vendor_name');
        $outgoingBuilder->where('o.product_id', $productId);
        
        if ($dateFrom) {
            $outgoingBuilder->where('DATE(o.date) >=', $dateFrom);
        }
        if ($dateTo) {
            $outgoingBuilder->where('DATE(o.date) <=', $dateTo);
        }
        
        $outgoing = $outgoingBuilder->get()->getResultArray();
        
        // Merge and sort by date
        $movements = array_merge($incoming, $outgoing);
        usort($movements, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $movements;
    }

    public function generateProductCode($categoryId = null)
    {
        $prefix = 'PRD';
        
        if ($categoryId) {
            $category = $this->db->table('categories')
                                ->select('name')
                                ->where('id', $categoryId)
                                ->get()
                                ->getRow();
            
            if ($category) {
                $prefix = strtoupper(substr($category->name, 0, 3));
            }
        }
        
        // Get next sequence number
        $lastProduct = $this->db->table($this->table)
            ->select('code')
            ->like('code', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();
        
        $sequence = 1;
        if ($lastProduct) {
            $lastNumber = (int)substr($lastProduct->code, strlen($prefix));
            $sequence = $lastNumber + 1;
        }
        
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function bulkUpdateStock($updates)
    {
        $this->db->transStart();
        
        try {
            foreach ($updates as $update) {
                $this->update($update['id'], ['stock' => $update['stock']]);
            }
            
            $this->db->transComplete();
            return $this->db->transStatus();
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return false;
        }
    }

    public function getStockAlert($threshold = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, c.name as category_name');
        $builder->join('categories c', 'p.category_id = c.id', 'left');
        
        if ($threshold) {
            $builder->where('p.stock <=', $threshold);
        } else {
            $builder->where('p.stock <=', 'p.min_stock', false);
        }
        
        $builder->orderBy('p.stock', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    public function getProductReport($dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, c.name as category_name,
                         COALESCE(SUM(ii.quantity), 0) as total_incoming,
                         COALESCE(SUM(oi.quantity), 0) as total_outgoing,
                         (COALESCE(SUM(ii.quantity), 0) - COALESCE(SUM(oi.quantity), 0)) as net_movement');
        $builder->join('categories c', 'p.category_id = c.id', 'left');
        $builder->join('incoming_items ii', 'p.id = ii.product_id', 'left');
        $builder->join('outgoing_items oi', 'p.id = oi.product_id', 'left');
        
        if ($dateFrom) {
            $builder->where('(ii.date IS NULL OR DATE(ii.date) >=', $dateFrom . ')');
            $builder->where('(oi.date IS NULL OR DATE(oi.date) >=', $dateFrom . ')');
        }
        
        if ($dateTo) {
            $builder->where('(ii.date IS NULL OR DATE(ii.date) <=', $dateTo . ')');
            $builder->where('(oi.date IS NULL OR DATE(oi.date) <=', $dateTo . ')');
        }
        
        $builder->groupBy('p.id, p.name, p.code, p.unit, p.stock, p.min_stock, c.name');
        $builder->orderBy('p.name', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}