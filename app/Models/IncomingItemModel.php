<?php

namespace App\Models;

use CodeIgniter\Model;

class IncomingItemModel extends Model
{
    protected $table = 'incoming_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'purchase_id',
        'product_id',
        'date',
        'quantity',
        'notes',
        'created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'purchase_id' => 'required|integer',
        'product_id' => 'required|integer',
        'date' => 'permit_empty|valid_date',
        'quantity' => 'required|decimal|greater_than[0]',
        'notes' => 'permit_empty|max_length[500]',
        'created_by' => 'required|integer'
    ];

    protected $validationMessages = [
        'purchase_id' => [
            'required' => 'Purchase ID harus diisi',
            'integer' => 'Purchase ID tidak valid'
        ],
        'product_id' => [
            'required' => 'Produk harus dipilih',
            'integer' => 'Produk tidak valid'
        ],
        'quantity' => [
            'required' => 'Jumlah harus diisi',
            'decimal' => 'Jumlah harus berupa angka',
            'greater_than' => 'Jumlah harus lebih dari 0'
        ],
        'notes' => [
            'max_length' => 'Catatan maksimal 500 karakter'
        ],
        'created_by' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID tidak valid'
        ]
    ];

    public function getIncomingItemsWithDetails($limit = null, $offset = null, $search = null, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' i');
        $builder->select('i.*, p.name as product_name, p.code as product_code, p.unit,
                         c.name as category_name, pur.buyer_name, v.name as vendor_name,
                         u.full_name as created_by_name');
        $builder->join('products p', 'i.product_id = p.id');
        $builder->join('categories c', 'p.category_id = c.id');
        $builder->join('purchases pur', 'i.purchase_id = pur.id');
        $builder->join('vendors v', 'pur.vendor_id = v.id');
        $builder->join('users u', 'i.created_by = u.id');

        if ($search) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.code', $search)
                ->orLike('v.name', $search)
                ->orLike('pur.buyer_name', $search)
                ->groupEnd();
        }

        if ($dateFrom) {
            $builder->where('DATE(i.date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(i.date) <=', $dateTo);
        }

        $builder->orderBy('i.date', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function countIncomingItemsWithDetails($search = null, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' i');
        $builder->join('products p', 'i.product_id = p.id');
        $builder->join('categories c', 'p.category_id = c.id');
        $builder->join('purchases pur', 'i.purchase_id = pur.id');
        $builder->join('vendors v', 'pur.vendor_id = v.id');

        if ($search) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.code', $search)
                ->orLike('v.name', $search)
                ->orLike('pur.buyer_name', $search)
                ->groupEnd();
        }

        if ($dateFrom) {
            $builder->where('DATE(i.date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(i.date) <=', $dateTo);
        }

        return $builder->countAllResults();
    }

    public function getIncomingItemWithDetails($id)
    {
        return $this->db->table($this->table . ' i')
            ->select('i.*, p.name as product_name, p.code as product_code, p.unit,
                                c.name as category_name, pur.buyer_name, pur.purchase_date,
                                v.name as vendor_name, v.address as vendor_address,
                                u.full_name as created_by_name')
            ->join('products p', 'i.product_id = p.id')
            ->join('categories c', 'p.category_id = c.id')
            ->join('purchases pur', 'i.purchase_id = pur.id')
            ->join('vendors v', 'pur.vendor_id = v.id')
            ->join('users u', 'i.created_by = u.id')
            ->where('i.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getRecentTransactions($limit = 10)
    {
        return $this->db->table($this->table . ' i')
            ->select('i.*, p.name as product_name, p.unit, v.name as vendor_name')
            ->join('products p', 'i.product_id = p.id')
            ->join('purchases pur', 'i.purchase_id = pur.id')
            ->join('vendors v', 'pur.vendor_id = v.id')
            ->orderBy('i.date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getIncomingItemsByDateRange($dateFrom, $dateTo)
    {
        return $this->db->table('v_incoming_items_report')
            ->where('DATE(date) >=', $dateFrom)
            ->where('DATE(date) <=', $dateTo)
            ->orderBy('date', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getMonthlyIncomingReport($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $report = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%04d-%02d', $year, $month);

            $data = $this->db->table($this->table . ' i')
                ->select('COUNT(i.id) as transaction_count, SUM(i.quantity) as total_quantity')
                ->where('DATE_FORMAT(i.date, "%Y-%m")', $monthStr)
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

    public function getIncomingStatistics()
    {
        $stats = [];

        // Today's incoming
        $today = date('Y-m-d');
        $todayStats = $this->db->table($this->table)
            ->select('COUNT(id) as count, SUM(quantity) as total_qty')
            ->where('DATE(date)', $today)
            ->get()
            ->getRowArray();
        $stats['today'] = $todayStats;

        // This week's incoming
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $weekStats = $this->db->table($this->table)
            ->select('COUNT(id) as count, SUM(quantity) as total_qty')
            ->where('DATE(date) >=', $weekStart)
            ->where('DATE(date) <=', $weekEnd)
            ->get()
            ->getRowArray();
        $stats['this_week'] = $weekStats;

        // This month's incoming
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $monthStats = $this->db->table($this->table)
            ->select('COUNT(id) as count, SUM(quantity) as total_qty')
            ->where('DATE(date) >=', $monthStart)
            ->where('DATE(date) <=', $monthEnd)
            ->get()
            ->getRowArray();
        $stats['this_month'] = $monthStats;

        // Top products by incoming quantity this month
        $topProducts = $this->db->table($this->table . ' i')
            ->select('p.name, p.code, SUM(i.quantity) as total_quantity, COUNT(i.id) as transaction_count')
            ->join('products p', 'i.product_id = p.id')
            ->where('DATE(i.date) >=', $monthStart)
            ->where('DATE(i.date) <=', $monthEnd)
            ->groupBy('p.id, p.name, p.code')
            ->orderBy('total_quantity', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        $stats['top_products'] = $topProducts;

        return $stats;
    }

    public function validatePurchaseQuantity($purchaseId, $productId, $quantity)
    {
        // Get purchase detail for this product
        $purchaseDetail = $this->db->table('purchase_details')
            ->where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->get()
            ->getRowArray();

        if (!$purchaseDetail) {
            return ['valid' => false, 'message' => 'Produk tidak ditemukan dalam purchase order'];
        }

        // Get total quantity already received
        $receivedQuantity = $this->db->table($this->table)
            ->selectSum('quantity')
            ->where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->get()
            ->getRow()
            ->quantity ?? 0;

        $remainingQuantity = $purchaseDetail['quantity'] - $receivedQuantity;

        if ($quantity > $remainingQuantity) {
            return [
                'valid' => false,
                'message' => "Jumlah melebihi sisa yang harus diterima. Sisa: {$remainingQuantity}"
            ];
        }

        return ['valid' => true, 'remaining' => $remainingQuantity];
    }

    public function processIncomingItem($data)
    {
        $this->db->transStart();

        try {
            // Validate purchase quantity
            $validation = $this->validatePurchaseQuantity(
                $data['purchase_id'],
                $data['product_id'],
                $data['quantity']
            );

            if (!$validation['valid']) {
                throw new \Exception($validation['message']);
            }

            // Insert incoming item
            $incomingId = $this->insert($data);

            if (!$incomingId) {
                throw new \Exception('Gagal menyimpan data barang masuk');
            }

            // Update product stock - will be handled by database trigger

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal');
            }

            return ['success' => true, 'id' => $incomingId];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function bulkReceiveItems($purchaseId, $items)
    {
        $this->db->transStart();

        try {
            foreach ($items as $item) {
                $itemData = [
                    'purchase_id' => $purchaseId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'date' => $item['date'] ?? date('Y-m-d H:i:s'),
                    'notes' => $item['notes'] ?? '',
                    'created_by' => $item['created_by']
                ];

                $result = $this->processIncomingItem($itemData);
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Bulk receive gagal');
            }

            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getIncomingItemsByProduct($productId, $limit = null)
    {
        $builder = $this->db->table($this->table . ' i');
        $builder->select('i.*, pur.buyer_name, v.name as vendor_name, u.full_name as created_by_name');
        $builder->join('purchases pur', 'i.purchase_id = pur.id');
        $builder->join('vendors v', 'pur.vendor_id = v.id');
        $builder->join('users u', 'i.created_by = u.id');
        $builder->where('i.product_id', $productId);
        $builder->orderBy('i.date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    public function getIncomingItemsByPurchase($purchaseId)
    {
        return $this->db->table($this->table . ' i')
            ->select('i.*, p.name as product_name, p.code as product_code, p.unit')
            ->join('products p', 'i.product_id = p.id')
            ->where('i.purchase_id', $purchaseId)
            ->orderBy('i.date', 'DESC')
            ->get()
            ->getResultArray();
    }
}
