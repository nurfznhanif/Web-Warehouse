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
        $sql = "
            SELECT 
                v.id, v.name, v.address, v.phone, v.email, v.created_at, v.updated_at,
                COUNT(p.id) as purchase_count,
                SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending_purchases
            FROM {$this->table} v 
            LEFT JOIN purchases p ON v.id = p.vendor_id
        ";
        
        $whereConditions = [];
        $binds = [];
        
        if ($search) {
            $whereConditions[] = "(v.name LIKE ? OR v.address LIKE ? OR v.phone LIKE ? OR v.email LIKE ?)";
            $searchParam = '%' . $search . '%';
            $binds = array_merge($binds, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " GROUP BY v.id, v.name, v.address, v.phone, v.email, v.created_at, v.updated_at";
        $sql .= " ORDER BY v.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . $limit;
            if ($offset) {
                $sql .= " OFFSET " . $offset;
            }
        }
        
        return $this->db->query($sql, $binds)->getResultArray();
    }

    public function countVendorsWithPurchaseCount($search = null)
    {
        $sql = "
            SELECT COUNT(DISTINCT v.id) as total
            FROM {$this->table} v 
            LEFT JOIN purchases p ON v.id = p.vendor_id
        ";
        
        $binds = [];
        
        if ($search) {
            $sql .= " WHERE (v.name LIKE ? OR v.address LIKE ? OR v.phone LIKE ? OR v.email LIKE ?)";
            $searchParam = '%' . $search . '%';
            $binds = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        return $this->db->query($sql, $binds)->getRow()->total;
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

        // Vendors with purchases - gunakan raw query yang aman
        $vendorsWithPurchases = $this->db->query("
            SELECT COUNT(DISTINCT v.id) as count 
            FROM {$this->table} v 
            INNER JOIN purchases p ON v.id = p.vendor_id
        ")->getRow()->count;
        $stats['vendors_with_purchases'] = $vendorsWithPurchases;

        // Inactive vendors (no purchases)
        $stats['inactive_vendors'] = $stats['total_vendors'] - $vendorsWithPurchases;

        // Top vendor by purchase amount - gunakan raw query yang aman
        $topVendor = $this->db->query("
            SELECT v.name, SUM(p.total_amount) as total_amount
            FROM {$this->table} v 
            INNER JOIN purchases p ON v.id = p.vendor_id
            GROUP BY v.id, v.name
            ORDER BY total_amount DESC
            LIMIT 1
        ")->getRowArray();
        $stats['top_vendor'] = $topVendor;

        return $stats;
    }

    public function getPurchasesByVendor($vendorId, $limit = null)
    {
        $sql = "
            SELECT p.*, COUNT(pd.id) as item_count
            FROM purchases p
            LEFT JOIN purchase_details pd ON p.id = pd.purchase_id
            WHERE p.vendor_id = ?
            GROUP BY p.id
            ORDER BY p.purchase_date DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT " . $limit;
        }
        
        return $this->db->query($sql, [$vendorId])->getResultArray();
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
        $sql = "
            SELECT 
                v.id, v.name, v.address, v.phone, v.email,
                COUNT(p.id) as total_purchases,
                COALESCE(SUM(p.total_amount), 0) as total_amount,
                COALESCE(AVG(p.total_amount), 0) as avg_purchase_amount,
                MAX(p.purchase_date) as last_purchase_date,
                COUNT(CASE WHEN p.status = 'pending' THEN 1 END) as pending_purchases,
                COUNT(CASE WHEN p.status = 'received' THEN 1 END) as completed_purchases
            FROM {$this->table} v
            LEFT JOIN purchases p ON v.id = p.vendor_id
            GROUP BY v.id, v.name, v.address, v.phone, v.email
            ORDER BY total_amount DESC
        ";
        
        return $this->db->query($sql)->getResultArray();
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