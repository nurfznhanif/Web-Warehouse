<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorModel extends Model
{
    protected $table = 'vendors';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'address',
        'phone',
        'email'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[200]',
        'address' => 'permit_empty|max_length[500]',
        'phone' => 'permit_empty|max_length[20]',
        'email' => 'permit_empty|valid_email|max_length[100]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama vendor harus diisi',
            'min_length' => 'Nama vendor minimal 3 karakter',
            'max_length' => 'Nama vendor maksimal 200 karakter'
        ],
        'address' => [
            'max_length' => 'Alamat maksimal 500 karakter'
        ],
        'phone' => [
            'max_length' => 'Nomor telepon maksimal 20 karakter'
        ],
        'email' => [
            'valid_email' => 'Format email tidak valid',
            'max_length' => 'Email maksimal 100 karakter'
        ]
    ];

    public function getVendorsWithPurchaseCount($limit = null, $offset = null, $search = null)
    {
        $builder = $this->db->table($this->table . ' v');
        $builder->select('v.*, COUNT(p.id) as purchase_count, 
                         SUM(CASE WHEN p.status = "pending" THEN 1 ELSE 0 END) as pending_purchases');
        $builder->join('purchases p', 'v.id = p.vendor_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('v.name', $search)
                ->orLike('v.address', $search)
                ->orLike('v.phone', $search)
                ->orLike('v.email', $search)
                ->groupEnd();
        }

        $builder->groupBy('v.id, v.name, v.address, v.phone, v.email, v.created_at, v.updated_at');
        $builder->orderBy('v.created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function countVendorsWithPurchaseCount($search = null)
    {
        $builder = $this->db->table($this->table . ' v');
        $builder->join('purchases p', 'v.id = p.vendor_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('v.name', $search)
                ->orLike('v.address', $search)
                ->orLike('v.phone', $search)
                ->orLike('v.email', $search)
                ->groupEnd();
        }

        $builder->groupBy('v.id');
        return $builder->countAllResults();
    }

    public function getVendorWithPurchases($id)
    {
        $vendor = $this->find($id);
        if (!$vendor) {
            return null;
        }

        // Get purchases from this vendor
        $purchases = $this->db->table('purchases')
            ->where('vendor_id', $id)
            ->orderBy('purchase_date', 'DESC')
            ->get()
            ->getResultArray();

        $vendor['purchases'] = $purchases;
        $vendor['purchase_count'] = count($purchases);

        // Calculate total purchase amount
        $vendor['total_purchase_amount'] = array_sum(array_column($purchases, 'total_amount'));

        return $vendor;
    }

    public function checkPurchaseExists($vendorId)
    {
        return $this->db->table('purchases')
            ->where('vendor_id', $vendorId)
            ->countAllResults() > 0;
    }

    public function getVendorStatistics()
    {
        $stats = [];

        // Total vendors
        $stats['total_vendors'] = $this->countAll();

        // Vendors with purchases
        $vendorsWithPurchases = $this->db->table($this->table . ' v')
            ->join('purchases p', 'v.id = p.vendor_id')
            ->groupBy('v.id')
            ->countAllResults();
        $stats['vendors_with_purchases'] = $vendorsWithPurchases;

        // Inactive vendors (no purchases)
        $stats['inactive_vendors'] = $stats['total_vendors'] - $vendorsWithPurchases;

        // Top vendor by purchase amount
        $topVendor = $this->db->table($this->table . ' v')
            ->select('v.name, SUM(p.total_amount) as total_amount')
            ->join('purchases p', 'v.id = p.vendor_id')
            ->groupBy('v.id, v.name')
            ->orderBy('total_amount', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        $stats['top_vendor'] = $topVendor;

        return $stats;
    }

    public function getPurchasesByVendor($vendorId, $limit = null)
    {
        $builder = $this->db->table('purchases p');
        $builder->select('p.*, COUNT(pd.id) as item_count');
        $builder->join('purchase_details pd', 'p.id = pd.purchase_id', 'left');
        $builder->where('p.vendor_id', $vendorId);
        $builder->groupBy('p.id');
        $builder->orderBy('p.purchase_date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    public function getVendorsForSelect()
    {
        return $this->select('id, name')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function searchVendors($keyword)
    {
        return $this->like('name', $keyword)
            ->orLike('address', $keyword)
            ->orLike('phone', $keyword)
            ->orLike('email', $keyword)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function getVendorPerformanceReport()
    {
        return $this->db->table($this->table . ' v')
            ->select('v.id, v.name, v.address, v.phone, v.email,
                                COUNT(p.id) as total_purchases,
                                SUM(p.total_amount) as total_amount,
                                AVG(p.total_amount) as avg_purchase_amount,
                                MAX(p.purchase_date) as last_purchase_date,
                                COUNT(CASE WHEN p.status = "pending" THEN 1 END) as pending_purchases,
                                COUNT(CASE WHEN p.status = "received" THEN 1 END) as completed_purchases')
            ->join('purchases p', 'v.id = p.vendor_id', 'left')
            ->groupBy('v.id, v.name, v.address, v.phone, v.email')
            ->orderBy('total_amount', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function beforeDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];

        // Check if vendor has purchases
        if ($this->checkPurchaseExists($id)) {
            throw new \Exception('Tidak dapat menghapus vendor yang masih memiliki transaksi pembelian!');
        }

        return $data;
    }

    public function getRecentVendors($limit = 5)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getVendorTrends()
    {
        // Get vendor registration trends over the last 12 months
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

    public function bulkUpdate($vendors)
    {
        $this->db->transStart();

        foreach ($vendors as $vendor) {
            if (isset($vendor['id'])) {
                $this->update($vendor['id'], $vendor);
            } else {
                $this->insert($vendor);
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function getVendorContactInfo($vendorId)
    {
        return $this->select('name, address, phone, email')
            ->where('id', $vendorId)
            ->first();
    }
}
