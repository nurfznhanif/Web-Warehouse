<?php

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
        'status' => 'permit_empty|in_list[pending,received,cancelled]',
        'notes' => 'permit_empty|max_length[1000]'
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
            'in_list' => 'Status tidak valid'
        ],
        'notes' => [
            'max_length' => 'Catatan maksimal 1000 karakter'
        ]
    ];

    public function getPurchasesWithDetails($limit = null, $offset = null, $search = null, $status = null)
    {
        $builder = $this->select('purchases.*, vendors.name as vendor_name, vendors.address as vendor_address,
                                 COUNT(pd.id) as item_count, 
                                 COALESCE(SUM(pd.total), 0) as calculated_total')
                       ->join('vendors', 'vendors.id = purchases.vendor_id')
                       ->join('purchase_details pd', 'pd.purchase_id = purchases.id', 'left')
                       ->groupBy('purchases.id, vendors.name, vendors.address');

        if ($search) {
            $builder->groupStart()
                ->like('vendors.name', $search)
                ->orLike('purchases.buyer_name', $search)
                ->orLike('purchases.id', $search)
                ->groupEnd();
        }

        if ($status && $status !== 'all') {
            $builder->where('purchases.status', $status);
        }

        $builder->orderBy('purchases.created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    public function countPurchasesWithDetails($search = null, $status = null)
    {
        $builder = $this->join('vendors', 'vendors.id = purchases.vendor_id');

        if ($search) {
            $builder->groupStart()
                ->like('vendors.name', $search)
                ->orLike('purchases.buyer_name', $search)
                ->orLike('purchases.id', $search)
                ->groupEnd();
        }

        if ($status && $status !== 'all') {
            $builder->where('purchases.status', $status);
        }

        return $builder->countAllResults();
    }

    public function getPurchaseWithDetails($id)
    {
        $purchase = $this->select('purchases.*, vendors.name as vendor_name, vendors.address as vendor_address,
                                  vendors.phone as vendor_phone, vendors.email as vendor_email')
                        ->join('vendors', 'vendors.id = purchases.vendor_id')
                        ->where('purchases.id', $id)
                        ->first();

        if (!$purchase) {
            return null;
        }

        // Get purchase details
        $detailModel = new PurchaseDetailModel();
        $purchase['details'] = $detailModel->getDetailsByPurchase($id);
        $purchase['statistics'] = $detailModel->getPurchaseDetailStatistics($id);

        return $purchase;
    }

    public function calculateTotalAmount($purchaseId)
    {
        $detailModel = new PurchaseDetailModel();
        $total = $this->db->table('purchase_details')
                         ->selectSum('total')
                         ->where('purchase_id', $purchaseId)
                         ->get()
                         ->getRow()
                         ->total ?? 0;

        return $this->update($purchaseId, ['total_amount' => $total]);
    }

    public function getPurchaseStatistics()
    {
        $stats = [];

        // Total purchases
        $stats['total_purchases'] = $this->countAll();

        // Purchases by status
        $stats['pending_purchases'] = $this->where('status', 'pending')->countAllResults(false);
        $stats['received_purchases'] = $this->where('status', 'received')->countAllResults(false);
        $stats['cancelled_purchases'] = $this->where('status', 'cancelled')->countAllResults(false);

        // Total purchase amount
        $totalAmount = $this->selectSum('total_amount')->first();
        $stats['total_amount'] = $totalAmount['total_amount'] ?? 0;

        // Average purchase amount
        if ($stats['total_purchases'] > 0) {
            $stats['average_amount'] = $stats['total_amount'] / $stats['total_purchases'];
        } else {
            $stats['average_amount'] = 0;
        }

        // Recent purchases (last 30 days)
        $stats['recent_purchases'] = $this->where('purchase_date >=', date('Y-m-d', strtotime('-30 days')))
                                         ->countAllResults(false);

        // Top vendor by purchase count
        $topVendor = $this->select('vendors.name, COUNT(purchases.id) as purchase_count')
                         ->join('vendors', 'vendors.id = purchases.vendor_id')
                         ->groupBy('vendors.id, vendors.name')
                         ->orderBy('purchase_count', 'DESC')
                         ->first();
        $stats['top_vendor'] = $topVendor;

        return $stats;
    }

    public function getPurchasesByDate($startDate, $endDate)
    {
        return $this->select('purchases.*, vendors.name as vendor_name')
                   ->join('vendors', 'vendors.id = purchases.vendor_id')
                   ->where('purchase_date >=', $startDate)
                   ->where('purchase_date <=', $endDate)
                   ->orderBy('purchase_date', 'DESC')
                   ->findAll();
    }

    public function getPurchasesByVendor($vendorId, $limit = null)
    {
        $builder = $this->select('purchases.*, COUNT(pd.id) as item_count')
                       ->join('purchase_details pd', 'pd.purchase_id = purchases.id', 'left')
                       ->where('purchases.vendor_id', $vendorId)
                       ->groupBy('purchases.id')
                       ->orderBy('purchases.purchase_date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    public function updateStatus($purchaseId, $status)
    {
        $validStatuses = ['pending', 'received', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update($purchaseId, ['status' => $status]);
    }

    public function getMonthlyPurchaseData($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            
            $count = $this->where('YEAR(purchase_date)', $year)
                         ->where('MONTH(purchase_date)', $month)
                         ->countAllResults(false);
            
            $amount = $this->where('YEAR(purchase_date)', $year)
                          ->where('MONTH(purchase_date)', $month)
                          ->selectSum('total_amount')
                          ->first()['total_amount'] ?? 0;

            $monthlyData[] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'count' => $count,
                'amount' => $amount
            ];
        }

        return $monthlyData;
    }

    public function getRecentPurchases($limit = 5)
    {
        return $this->select('purchases.*, vendors.name as vendor_name')
                   ->join('vendors', 'vendors.id = purchases.vendor_id')
                   ->orderBy('purchases.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function duplicatePurchase($purchaseId)
    {
        $this->db->transStart();

        try {
            // Get original purchase
            $originalPurchase = $this->find($purchaseId);
            if (!$originalPurchase) {
                throw new \Exception('Purchase not found');
            }

            // Create new purchase
            $newPurchaseData = [
                'vendor_id' => $originalPurchase['vendor_id'],
                'purchase_date' => date('Y-m-d'),
                'buyer_name' => $originalPurchase['buyer_name'],
                'status' => 'pending',
                'notes' => 'Duplikat dari Purchase #' . $purchaseId
            ];

            $newPurchaseId = $this->insert($newPurchaseData);

            // Copy purchase details
            $detailModel = new PurchaseDetailModel();
            $originalDetails = $detailModel->where('purchase_id', $purchaseId)->findAll();

            foreach ($originalDetails as $detail) {
                $newDetailData = [
                    'purchase_id' => $newPurchaseId,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                    'total' => $detail['total']
                ];
                $detailModel->insert($newDetailData);
            }

            // Update total amount
            $this->calculateTotalAmount($newPurchaseId);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return ['success' => true, 'purchase_id' => $newPurchaseId];

        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deletePurchase($purchaseId)
    {
        $this->db->transStart();

        try {
            // Check if purchase has been received
            $purchase = $this->find($purchaseId);
            if (!$purchase) {
                throw new \Exception('Purchase not found');
            }

            if ($purchase['status'] === 'received') {
                throw new \Exception('Cannot delete received purchase');
            }

            // Delete purchase details first
            $this->db->table('purchase_details')->where('purchase_id', $purchaseId)->delete();

            // Delete purchase
            $this->delete($purchaseId);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return ['success' => true];

        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function beforeDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        
        // Check if purchase has incoming items
        $hasIncomingItems = $this->db->table('incoming_items')
                                    ->where('purchase_id', $id)
                                    ->countAllResults() > 0;
        
        if ($hasIncomingItems) {
            throw new \Exception('Tidak dapat menghapus pembelian yang sudah memiliki transaksi barang masuk!');
        }
        
        return $data;
    }
}