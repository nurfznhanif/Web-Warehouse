<?php

namespace App\Models;

use CodeIgniter\Model;

class OutgoingItemModel extends Model
{
    protected $table = 'outgoing_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'product_id',
        'date',
        'quantity',
        'purpose',
        'recipient',
        'notes',
        'created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'product_id' => 'required|integer',
        'date' => 'permit_empty|valid_date',
        'quantity' => 'required|decimal|greater_than[0]',
        'purpose' => 'permit_empty|max_length[200]',
        'recipient' => 'permit_empty|max_length[100]',
        'notes' => 'permit_empty|max_length[500]',
        'created_by' => 'required|integer'
    ];

    protected $validationMessages = [
        'product_id' => [
            'required' => 'Produk harus dipilih',
            'integer' => 'Produk tidak valid'
        ],
        'quantity' => [
            'required' => 'Jumlah harus diisi',
            'decimal' => 'Jumlah harus berupa angka',
            'greater_than' => 'Jumlah harus lebih dari 0'
        ],
        'purpose' => [
            'max_length' => 'Tujuan maksimal 200 karakter'
        ],
        'recipient' => [
            'max_length' => 'Penerima maksimal 100 karakter'
        ],
        'notes' => [
            'max_length' => 'Catatan maksimal 500 karakter'
        ],
        'created_by' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID tidak valid'
        ]
    ];

    public function getOutgoingItemsWithDetails($limit = null, $offset = null, $search = null, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' o');
        $builder->select('o.*, p.name as product_name, p.code as product_code, p.unit,
                         c.name as category_name, u.full_name as created_by_name');
        $builder->join('products p', 'o.product_id = p.id');
        $builder->join('categories c', 'p.category_id = c.id');
        $builder->join('users u', 'o.created_by = u.id');

        if ($search) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.code', $search)
                ->orLike('o.purpose', $search)
                ->orLike('o.recipient', $search)
                ->groupEnd();
        }

        if ($dateFrom) {
            $builder->where('DATE(o.date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(o.date) <=', $dateTo);
        }

        $builder->orderBy('o.date', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function countOutgoingItemsWithDetails($search = null, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' o');
        $builder->join('products p', 'o.product_id = p.id');
        $builder->join('categories c', 'p.category_id = c.id');

        if ($search) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.code', $search)
                ->orLike('o.purpose', $search)
                ->orLike('o.recipient', $search)
                ->groupEnd();
        }

        if ($dateFrom) {
            $builder->where('DATE(o.date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(o.date) <=', $dateTo);
        }

        return $builder->countAllResults();
    }

    public function getOutgoingItemWithDetails($id)
    {
        return $this->db->table($this->table . ' o')
            ->select('o.*, p.name as product_name, p.code as product_code, p.unit,
                                p.stock as current_stock, c.name as category_name,
                                u.full_name as created_by_name, u.username as created_by_username')
            ->join('products p', 'o.product_id = p.id')
            ->join('categories c', 'p.category_id = c.id')
            ->join('users u', 'o.created_by = u.id')
            ->where('o.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getRecentTransactions($limit = 10)
    {
        return $this->db->table($this->table . ' o')
            ->select('o.*, p.name as product_name, p.unit, u.full_name as created_by_name')
            ->join('products p', 'o.product_id = p.id')
            ->join('users u', 'o.created_by = u.id')
            ->orderBy('o.date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getOutgoingItemsByDateRange($dateFrom, $dateTo)
    {
        return $this->db->table('v_outgoing_items_report')
            ->where('DATE(date) >=', $dateFrom)
            ->where('DATE(date) <=', $dateTo)
            ->orderBy('date', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getMonthlyOutgoingReport($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $report = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%04d-%02d', $year, $month);

            $data = $this->db->table($this->table . ' o')
                ->select('COUNT(o.id) as transaction_count, SUM(o.quantity) as total_quantity')
                ->where('DATE_FORMAT(o.date, "%Y-%m")', $monthStr)
                ->get()
                ->getRowArray();

            $report[] = [
                'month' => date('F', mktime(0, 0, 0, $month, 1)),
                'month_num' => $month,
                'year' => $year,
                'transaction_count' => (int)$data['transaction_count'],
                'total_quantity' => (float)$data['total_quantity']
            ];
        }

        return $report;
    }

    public function getOutgoingStatistics()
    {
        $stats = [];

        // Today's outgoing
        $today = date('Y-m-d');
        $todayStats = $this->db->table($this->table)
            ->select('COUNT(id) as count, SUM(quantity) as total_qty')
            ->where('DATE(date)', $today)
            ->get()
            ->getRowArray();
        $stats['today'] = $todayStats;

        // This week's outgoing
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $weekStats = $this->db->table($this->table)
            ->select('COUNT(id) as count, SUM(quantity) as total_qty')
            ->where('DATE(date) >=', $weekStart)
            ->where('DATE(date) <=', $weekEnd)
            ->get()
            ->getRowArray();
        $stats['this_week'] = $weekStats;

        // This month's outgoing
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $monthStats = $this->db->table($this->table)
            ->select('COUNT(id) as count, SUM(quantity) as total_qty')
            ->where('DATE(date) >=', $monthStart)
            ->where('DATE(date) <=', $monthEnd)
            ->get()
            ->getRowArray();
        $stats['this_month'] = $monthStats;

        // Top products by outgoing quantity this month
        $topProducts = $this->db->table($this->table . ' o')
            ->select('p.name, p.code, SUM(o.quantity) as total_quantity, COUNT(o.id) as transaction_count')
            ->join('products p', 'o.product_id = p.id')
            ->where('DATE(o.date) >=', $monthStart)
            ->where('DATE(o.date) <=', $monthEnd)
            ->groupBy('p.id, p.name, p.code')
            ->orderBy('total_quantity', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        $stats['top_products'] = $topProducts;

        // Most common purposes
        $topPurposes = $this->db->table($this->table)
            ->select('purpose, COUNT(id) as count')
            ->where('purpose IS NOT NULL')
            ->where('purpose !=', '')
            ->where('DATE(date) >=', $monthStart)
            ->where('DATE(date) <=', $monthEnd)
            ->groupBy('purpose')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        $stats['top_purposes'] = $topPurposes;

        return $stats;
    }

    public function processOutgoingItem($data)
    {
        $this->db->transStart();

        try {
            // Check stock availability
            $productModel = new \App\Models\ProductModel();
            $stockCheck = $productModel->checkStockAvailability($data['product_id'], $data['quantity']);

            if (!$stockCheck['available']) {
                throw new \Exception($stockCheck['message']);
            }

            // Insert outgoing item
            $outgoingId = $this->insert($data);

            if (!$outgoingId) {
                throw new \Exception('Gagal menyimpan data barang keluar');
            }

            // Update product stock - will be handled by database trigger

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'id' => $outgoingId];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getOutgoingItemsByProduct($productId, $limit = null)
    {
        $builder = $this->db->table($this->table . ' o');
        $builder->select('o.*, u.full_name as created_by_name');
        $builder->join('users u', 'o.created_by = u.id');
        $builder->where('o.product_id', $productId);
        $builder->orderBy('o.date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    public function getOutgoingItemsByPurpose($purpose, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' o');
        $builder->select('o.*, p.name as product_name, p.code as product_code, p.unit');
        $builder->join('products p', 'o.product_id = p.id');
        $builder->where('o.purpose', $purpose);

        if ($dateFrom) {
            $builder->where('DATE(o.date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(o.date) <=', $dateTo);
        }

        $builder->orderBy('o.date', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function getOutgoingItemsByRecipient($recipient, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' o');
        $builder->select('o.*, p.name as product_name, p.code as product_code, p.unit');
        $builder->join('products p', 'o.product_id = p.id');
        $builder->where('o.recipient', $recipient);

        if ($dateFrom) {
            $builder->where('DATE(o.date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(o.date) <=', $dateTo);
        }

        $builder->orderBy('o.date', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function bulkOutgoingItems($items)
    {
        $this->db->transStart();

        try {
            foreach ($items as $item) {
                $result = $this->processOutgoingItem($item);
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Bulk outgoing gagal');
            }

            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getProductUsageAnalysis($productId, $months = 12)
    {
        $monthsAgo = date('Y-m-d', strtotime("-{$months} months"));

        return $this->db->table($this->table)
            ->select('DATE_FORMAT(date, "%Y-%m") as month,
                                SUM(quantity) as total_quantity,
                                COUNT(id) as transaction_count,
                                AVG(quantity) as avg_quantity')
            ->where('product_id', $productId)
            ->where('DATE(date) >=', $monthsAgo)
            ->groupBy('DATE_FORMAT(date, "%Y-%m")')
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getPopularPurposes($limit = 10)
    {
        return $this->db->table($this->table)
            ->select('purpose, COUNT(id) as usage_count, SUM(quantity) as total_quantity')
            ->where('purpose IS NOT NULL')
            ->where('purpose !=', '')
            ->groupBy('purpose')
            ->orderBy('usage_count', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getTopRecipients($limit = 10, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('recipient, COUNT(id) as transaction_count, SUM(quantity) as total_quantity');
        $builder->where('recipient IS NOT NULL');
        $builder->where('recipient !=', '');

        if ($dateFrom) {
            $builder->where('DATE(date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(date) <=', $dateTo);
        }

        $builder->groupBy('recipient');
        $builder->orderBy('total_quantity', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    public function validateStockBeforeOutgoing($productId, $quantity, $excludeId = null)
    {
        $productModel = new \App\Models\ProductModel();
        $product = $productModel->find($productId);

        if (!$product) {
            return ['valid' => false, 'message' => 'Produk tidak ditemukan'];
        }

        $availableStock = $product['stock'];

        // If updating existing outgoing item, add back the previous quantity
        if ($excludeId) {
            $existingItem = $this->find($excludeId);
            if ($existingItem) {
                $availableStock += $existingItem['quantity'];
            }
        }

        if ($quantity > $availableStock) {
            return [
                'valid' => false,
                'message' => "Stok tidak mencukupi. Stok tersedia: {$availableStock} {$product['unit']}"
            ];
        }

        return ['valid' => true];
    }
}
