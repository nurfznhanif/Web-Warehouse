<?php
// app/Models/PurchaseModel.php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseModel extends Model
{
    protected $table = 'purchases';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'vendor_id',
        'purchase_date',
        'buyer_name',
        'total_amount',
        'status',
        'notes'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'vendor_id' => 'required|integer',
        'purchase_date' => 'required|valid_date',
        'buyer_name' => 'required|min_length[3]|max_length[100]',
        'total_amount' => 'permit_empty|decimal',
        'status' => 'required|in_list[pending,received,cancelled]',
        'notes' => 'permit_empty|max_length[500]'
    ];

    protected $validationMessages = [
        'vendor_id' => [
            'required' => 'Vendor harus dipilih',
            'integer' => 'Vendor tidak valid'
        ],
        'purchase_date' => [
            'required' => 'Tanggal pembelian harus diisi',
            'valid_date' => 'Format tanggal tidak valid'
        ],
        'buyer_name' => [
            'required' => 'Nama pembeli harus diisi',
            'min_length' => 'Nama pembeli minimal 3 karakter',
            'max_length' => 'Nama pembeli maksimal 100 karakter'
        ],
        'status' => [
            'required' => 'Status harus dipilih',
            'in_list' => 'Status harus pending, received, atau cancelled'
        ],
        'notes' => [
            'max_length' => 'Catatan maksimal 500 karakter'
        ]
    ];

    public function getPurchasesWithDetails($limit = null, $offset = null, $search = null, $status = null, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, v.name as vendor_name, v.address as vendor_address,
                         COUNT(pd.id) as item_count,
                         SUM(pd.quantity) as total_quantity');
        $builder->join('vendors v', 'p.vendor_id = v.id');
        $builder->join('purchase_details pd', 'p.id = pd.purchase_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('v.name', $search)
                ->orLike('p.buyer_name', $search)
                ->orLike('p.notes', $search)
                ->groupEnd();
        }

        if ($status) {
            $builder->where('p.status', $status);
        }

        if ($dateFrom) {
            $builder->where('DATE(p.purchase_date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(p.purchase_date) <=', $dateTo);
        }

        $builder->groupBy('p.id, v.name, v.address');
        $builder->orderBy('p.purchase_date', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function countPurchasesWithDetails($search = null, $status = null, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->join('vendors v', 'p.vendor_id = v.id');

        if ($search) {
            $builder->groupStart()
                ->like('v.name', $search)
                ->orLike('p.buyer_name', $search)
                ->orLike('p.notes', $search)
                ->groupEnd();
        }

        if ($status) {
            $builder->where('p.status', $status);
        }

        if ($dateFrom) {
            $builder->where('DATE(p.purchase_date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(p.purchase_date) <=', $dateTo);
        }

        return $builder->countAllResults();
    }

    public function getPurchaseWithDetails($id)
    {
        $purchase = $this->db->table($this->table . ' p')
            ->select('p.*, v.name as vendor_name, v.address as vendor_address,
                                    v.phone as vendor_phone, v.email as vendor_email')
            ->join('vendors v', 'p.vendor_id = v.id')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();

        if ($purchase) {
            // Get purchase details with product info
            $details = $this->db->table('purchase_details pd')
                ->select('pd.*, pr.name as product_name, pr.code as product_code,
                                        pr.unit, c.name as category_name')
                ->join('products pr', 'pd.product_id = pr.id')
                ->join('categories c', 'pr.category_id = c.id')
                ->where('pd.purchase_id', $id)
                ->get()
                ->getResultArray();

            $purchase['details'] = $details;
            $purchase['detail_count'] = count($details);
        }

        return $purchase;
    }

    public function getPurchaseStatistics()
    {
        $stats = [];

        // Total purchases
        $stats['total_purchases'] = $this->countAll();

        // Purchases by status
        $stats['pending_purchases'] = $this->where('status', 'pending')->countAllResults();
        $stats['received_purchases'] = $this->where('status', 'received')->countAllResults();
        $stats['cancelled_purchases'] = $this->where('status', 'cancelled')->countAllResults();

        // This month's purchases
        $thisMonth = date('Y-m');
        $monthlyPurchases = $this->where('DATE_FORMAT(purchase_date, "%Y-%m")', $thisMonth)
            ->countAllResults();
        $stats['monthly_purchases'] = $monthlyPurchases;

        // Total purchase amount this month
        $monthlyAmount = $this->db->table($this->table)
            ->selectSum('total_amount')
            ->where('DATE_FORMAT(purchase_date, "%Y-%m")', $thisMonth)
            ->where('status !=', 'cancelled')
            ->get()
            ->getRow()
            ->total_amount ?? 0;
        $stats['monthly_amount'] = $monthlyAmount;

        return $stats;
    }

    public function getMonthlyPurchaseReport($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $report = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%04d-%02d', $year, $month);

            $data = $this->db->table($this->table)
                ->select('COUNT(id) as purchase_count, 
                                    SUM(CASE WHEN status != "cancelled" THEN total_amount ELSE 0 END) as total_amount,
                                    COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
                                    COUNT(CASE WHEN status = "received" THEN 1 END) as received_count,
                                    COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_count')
                ->where('DATE_FORMAT(purchase_date, "%Y-%m")', $monthStr)
                ->get()
                ->getRowArray();

            $report[] = [
                'month' => date('F', mktime(0, 0, 0, $month, 1)),
                'month_num' => $month,
                'year' => $year,
                'purchase_count' => (int)$data['purchase_count'],
                'total_amount' => (float)$data['total_amount'],
                'pending_count' => (int)$data['pending_count'],
                'received_count' => (int)$data['received_count'],
                'cancelled_count' => (int)$data['cancelled_count']
            ];
        }

        return $report;
    }

    public function getPendingPurchases($limit = null)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, v.name as vendor_name');
        $builder->join('vendors v', 'p.vendor_id = v.id');
        $builder->where('p.status', 'pending');
        $builder->orderBy('p.purchase_date', 'ASC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    public function updateStatus($purchaseId, $status)
    {
        return $this->update($purchaseId, ['status' => $status]);
    }

    public function calculateTotalAmount($purchaseId)
    {
        $total = $this->db->table('purchase_details')
            ->selectSum('total')
            ->where('purchase_id', $purchaseId)
            ->get()
            ->getRow()
            ->total ?? 0;

        return $this->update($purchaseId, ['total_amount' => $total]);
    }

    public function getPurchasesByVendor($vendorId, $status = null)
    {
        $builder = $this->where('vendor_id', $vendorId);

        if ($status) {
            $builder->where('status', $status);
        }

        return $builder->orderBy('purchase_date', 'DESC')->findAll();
    }

    public function getRecentPurchases($limit = 10)
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, v.name as vendor_name')
            ->join('vendors v', 'p.vendor_id = v.id')
            ->orderBy('p.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
