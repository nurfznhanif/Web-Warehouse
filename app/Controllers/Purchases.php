<?php

namespace App\Controllers;

use App\Models\PurchaseModel;
use App\Models\PurchaseItemModel;
use App\Models\ProductModel;
use App\Models\VendorModel;

class Purchases extends BaseController
{
    protected $purchaseModel;
    protected $purchaseItemModel;
    protected $productModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->purchaseModel = new PurchaseModel();
        $this->purchaseItemModel = new PurchaseItemModel();
        $this->productModel = new ProductModel();
        $this->vendorModel = new VendorModel();
    }

    public function index()
    {
        $perPage = 20;
        $currentPage = $this->request->getGet('page') ?? 1;
        $search = $this->request->getGet('search');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $offset = ($currentPage - 1) * $perPage;
        
        // Get purchases with details
        $purchases = $this->purchaseModel->getPurchasesWithDetails($perPage, $offset, $search, null, $startDate, $endDate);
        $totalPurchases = $this->purchaseModel->countPurchasesWithDetails($search, $startDate, $endDate);
        
        // Create pagination
        $pager = \Config\Services::pager();
        $pager->setPath('purchases');
        
        // Get statistics
        $statistics = $this->purchaseModel->getPurchaseStatistics();
        
        $data = [
            'title' => 'Manajemen Pembelian - Warehouse Management System',
            'purchases' => $purchases,
            'pager' => $pager->makeLinks($currentPage, $perPage, $totalPurchases),
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'statistics' => $statistics
        ];

        return view('purchases/index', $data);
    }
    
    public function create()
    {
        $data = [
            'title' => 'Tambah Pembelian - Warehouse Management System',
            'vendors' => $this->vendorModel->findAll(),
            'products' => $this->productModel->findAll(),
            'validation' => session()->getFlashdata('validation')
        ];
        
        return view('purchases/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'vendor_name' => 'required|min_length[3]|max_length[100]',
            'vendor_address' => 'required|min_length[10]|max_length[500]',
            'purchase_date' => 'required|valid_date',
            'buyer_name' => 'required|min_length[3]|max_length[100]',
            'product_name.*' => 'required|min_length[2]|max_length[200]',
            'quantity.*' => 'required|decimal|greater_than[0]',
            'unit_price.*' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'notes' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Simpan data pembelian utama
            $purchaseData = [
                'vendor_name' => $this->request->getPost('vendor_name'),
                'vendor_address' => $this->request->getPost('vendor_address'),
                'vendor_phone' => $this->request->getPost('vendor_phone'),
                'purchase_date' => $this->request->getPost('purchase_date'),
                'buyer_name' => $this->request->getPost('buyer_name'),
                'notes' => $this->request->getPost('notes'),
                'status' => 'pending',
                'user_id' => session()->get('user_id')
            ];
            
            $purchaseId = $this->purchaseModel->insert($purchaseData);
            
            if (!$purchaseId) {
                throw new \Exception('Gagal menyimpan data pembelian');
            }

            // Simpan detail items pembelian
            $productNames = $this->request->getPost('product_name');
            $quantities = $this->request->getPost('quantity');
            $unitPrices = $this->request->getPost('unit_price');
            $specifications = $this->request->getPost('specification');
            $units = $this->request->getPost('unit');
            
            $totalAmount = 0;

            if (!empty($productNames)) {
                foreach ($productNames as $index => $productName) {
                    if (!empty($productName) && !empty($quantities[$index]) && $quantities[$index] > 0) {
                        $quantity = (float) $quantities[$index];
                        $unitPrice = !empty($unitPrices[$index]) ? (float) $unitPrices[$index] : 0;
                        $total = $quantity * $unitPrice;
                        $totalAmount += $total;

                        $itemData = [
                            'purchase_id' => $purchaseId,
                            'product_name' => $productName,
                            'specification' => $specifications[$index] ?? null,
                            'quantity' => $quantity,
                            'unit' => $units[$index] ?? 'pcs',
                            'unit_price' => $unitPrice,
                            'total_price' => $total
                        ];
                        
                        if (!$this->purchaseItemModel->insert($itemData)) {
                            throw new \Exception('Gagal menyimpan item pembelian');
                        }
                    }
                }
            }

            // Update total amount di purchase
            $this->purchaseModel->update($purchaseId, ['total_amount' => $totalAmount]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            session()->setFlashdata('success', 'Pembelian berhasil dicatat dengan total ' . count($productNames) . ' item');
            return redirect()->to('/purchases');

        } catch (\Exception $e) {
            $db->transRollback();
            session()->setFlashdata('errors', ['Terjadi kesalahan: ' . $e->getMessage()]);
            return redirect()->back()->withInput();
        }
    }
    
    public function view($id)
    {
        $purchase = $this->purchaseModel->find($id);
        
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        $items = $this->purchaseItemModel->where('purchase_id', $id)->findAll();
        
        $data = [
            'title' => 'Detail Pembelian - Warehouse Management System',
            'purchase' => $purchase,
            'items' => $items
        ];
        
        return view('purchases/view', $data);
    }

    public function edit($id)
    {
        $purchase = $this->purchaseModel->find($id);
        
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        $items = $this->purchaseItemModel->where('purchase_id', $id)->findAll();
        
        $data = [
            'title' => 'Edit Pembelian - Warehouse Management System',
            'purchase' => $purchase,
            'items' => $items,
            'vendors' => $this->vendorModel->findAll(),
            'products' => $this->productModel->findAll(),
            'validation' => session()->getFlashdata('validation')
        ];
        
        return view('purchases/edit', $data);
    }

    public function update($id)
    {
        $purchase = $this->purchaseModel->find($id);
        
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        $rules = [
            'vendor_name' => 'required|min_length[3]|max_length[100]',
            'vendor_address' => 'required|min_length[10]|max_length[500]',
            'purchase_date' => 'required|valid_date',
            'buyer_name' => 'required|min_length[3]|max_length[100]',
            'product_name.*' => 'required|min_length[2]|max_length[200]',
            'quantity.*' => 'required|decimal|greater_than[0]',
            'unit_price.*' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'status' => 'permit_empty|in_list[pending,received,cancelled]',
            'notes' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update data pembelian utama
            $purchaseData = [
                'vendor_name' => $this->request->getPost('vendor_name'),
                'vendor_address' => $this->request->getPost('vendor_address'),
                'vendor_phone' => $this->request->getPost('vendor_phone'),
                'purchase_date' => $this->request->getPost('purchase_date'),
                'buyer_name' => $this->request->getPost('buyer_name'),
                'status' => $this->request->getPost('status') ?? 'pending',
                'notes' => $this->request->getPost('notes')
            ];
            
            if (!$this->purchaseModel->update($id, $purchaseData)) {
                throw new \Exception('Gagal mengupdate data pembelian');
            }

            // Hapus item lama
            $this->purchaseItemModel->where('purchase_id', $id)->delete();

            // Simpan item baru
            $productNames = $this->request->getPost('product_name');
            $quantities = $this->request->getPost('quantity');
            $unitPrices = $this->request->getPost('unit_price');
            $specifications = $this->request->getPost('specification');
            $units = $this->request->getPost('unit');
            
            $totalAmount = 0;

            if (!empty($productNames)) {
                foreach ($productNames as $index => $productName) {
                    if (!empty($productName) && !empty($quantities[$index]) && $quantities[$index] > 0) {
                        $quantity = (float) $quantities[$index];
                        $unitPrice = !empty($unitPrices[$index]) ? (float) $unitPrices[$index] : 0;
                        $total = $quantity * $unitPrice;
                        $totalAmount += $total;

                        $itemData = [
                            'purchase_id' => $id,
                            'product_name' => $productName,
                            'specification' => $specifications[$index] ?? null,
                            'quantity' => $quantity,
                            'unit' => $units[$index] ?? 'pcs',
                            'unit_price' => $unitPrice,
                            'total_price' => $total
                        ];
                        
                        if (!$this->purchaseItemModel->insert($itemData)) {
                            throw new \Exception('Gagal menyimpan item pembelian');
                        }
                    }
                }
            }

            // Update total amount
            $this->purchaseModel->update($id, ['total_amount' => $totalAmount]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            session()->setFlashdata('success', 'Data pembelian berhasil diperbarui');
            return redirect()->to('/purchases/view/' . $id);

        } catch (\Exception $e) {
            $db->transRollback();
            session()->setFlashdata('errors', ['Terjadi kesalahan: ' . $e->getMessage()]);
            return redirect()->back()->withInput();
        }
    }

    public function updateStatus($id, $status)
    {
        $purchase = $this->purchaseModel->find($id);
        
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        $validStatuses = ['pending', 'received', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            session()->setFlashdata('error', 'Status tidak valid');
            return redirect()->back();
        }

        if ($this->purchaseModel->update($id, ['status' => $status])) {
            $statusText = match($status) {
                'received' => 'Diterima',
                'cancelled' => 'Dibatalkan',
                default => 'Pending'
            };
            session()->setFlashdata('success', "Status pembelian berhasil diubah menjadi: {$statusText}");
        } else {
            session()->setFlashdata('error', 'Gagal mengubah status pembelian');
        }

        return redirect()->to('/purchases/view/' . $id);
    }

    public function delete($id)
    {
        // Hanya admin yang bisa menghapus
        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'Anda tidak memiliki akses untuk menghapus data pembelian');
            return redirect()->to('/purchases');
        }

        $purchase = $this->purchaseModel->find($id);
        
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Hapus items terlebih dahulu
            $this->purchaseItemModel->where('purchase_id', $id)->delete();
            
            // Hapus purchase
            if (!$this->purchaseModel->delete($id)) {
                throw new \Exception('Gagal menghapus data pembelian');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            session()->setFlashdata('success', 'Data pembelian berhasil dihapus');

        } catch (\Exception $e) {
            $db->transRollback();
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->to('/purchases');
    }

    public function exportPDF($id)
    {
        $purchase = $this->purchaseModel->find($id);
        
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        $items = $this->purchaseItemModel->where('purchase_id', $id)->findAll();
        
        // Load PDF library (jika menggunakan TCPDF atau Dompdf)
        // Implementasi export PDF dapat disesuaikan dengan library yang digunakan
        
        $data = [
            'purchase' => $purchase,
            'items' => $items
        ];

        // Generate PDF
        // return view('purchases/pdf_template', $data);
        
        session()->setFlashdata('info', 'Fitur export PDF akan segera tersedia');
        return redirect()->to('/purchases/view/' . $id);
    }

    // API endpoints untuk AJAX
    public function getStatistics()
    {
        $statistics = $this->purchaseModel->getPurchaseStatistics();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $statistics
        ]);
    }

    public function searchVendors()
    {
        $keyword = $this->request->getGet('q');
        
        if (empty($keyword)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Keyword pencarian diperlukan'
            ]);
        }

        $vendors = $this->vendorModel->like('name', $keyword)
                                   ->orLike('address', $keyword)
                                   ->limit(10)
                                   ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $vendors
        ]);
    }
}