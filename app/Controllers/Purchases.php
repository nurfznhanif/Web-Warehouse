<?php

namespace App\Controllers;

use App\Models\PurchaseModel;
use App\Models\PurchaseItemModel;
use App\Models\ProductModel;
use App\Models\VendorModel;

class Purchases extends BaseController
{
    public function index()
    {
        $purchaseModel = new PurchaseModel();
        $data = [
            'title' => 'Manajemen Pembelian - Warehouse Management System',
            'purchases' => $purchaseModel->getPurchasesWithDetails()
        ];
        
        return view('purchases/index', $data);
    }
    
    public function create()
    {
        $productModel = new ProductModel();
        $vendorModel = new VendorModel();
        
        $data = [
            'title' => 'Tambah Pembelian Baru - Warehouse Management System',
            'products' => $productModel->findAll(),
            'vendors' => $vendorModel->findAll()
        ];
        
        return view('purchases/create', $data);
    }
    
    public function store()
    {
        $purchaseModel = new PurchaseModel();
        $purchaseItemModel = new PurchaseItemModel();
        
        // Validasi input
        $rules = [
            'vendor_id' => 'required|integer|is_not_unique[vendors.id]',
            'purchase_date' => 'required|valid_date',
            'buyer_name' => 'required|min_length[3]|max_length[100]',
            'notes' => 'permit_empty|max_length[1000]',
            'product_id' => 'required',
            'quantity' => 'required'
        ];

        $messages = [
            'vendor_id' => [
                'required' => 'Vendor harus dipilih',
                'integer' => 'Vendor tidak valid',
                'is_not_unique' => 'Vendor yang dipilih tidak valid'
            ],
            'product_id' => [
                'required' => 'Minimal harus ada satu produk'
            ],
            'quantity' => [
                'required' => 'Jumlah produk harus diisi'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Validasi items
        $productIds = $this->request->getPost('product_id');
        $quantities = $this->request->getPost('quantity');
        
        if (empty($productIds) || empty($quantities)) {
            session()->setFlashdata('errors', ['Minimal harus ada satu item produk']);
            return redirect()->back()->withInput();
        }

        // Check if there's at least one valid item
        $hasValidItem = false;
        foreach ($productIds as $index => $productId) {
            if (!empty($productId) && !empty($quantities[$index]) && $quantities[$index] > 0) {
                $hasValidItem = true;
                break;
            }
        }

        if (!$hasValidItem) {
            session()->setFlashdata('errors', ['Minimal harus ada satu item dengan produk dan jumlah yang valid']);
            return redirect()->back()->withInput();
        }

        // Start transaction
        $purchaseModel->db->transStart();

        try {
            // Simpan data pembelian
            $purchaseData = [
                'vendor_id' => $this->request->getPost('vendor_id'),
                'purchase_date' => $this->request->getPost('purchase_date'),
                'buyer_name' => $this->request->getPost('buyer_name'),
                'notes' => $this->request->getPost('notes'),
                'status' => 'pending'
            ];
            
            $purchaseId = $purchaseModel->insert($purchaseData);
            
            if (!$purchaseId) {
                throw new \Exception('Gagal menyimpan data pembelian');
            }

            // Simpan item pembelian
            $totalAmount = 0;
            foreach ($productIds as $index => $productId) {
                if (!empty($productId) && !empty($quantities[$index]) && $quantities[$index] > 0) {
                    $itemData = [
                        'purchase_id' => $purchaseId,
                        'product_id' => $productId,
                        'quantity' => $quantities[$index]
                    ];
                    
                    if (!$purchaseItemModel->insert($itemData)) {
                        throw new \Exception('Gagal menyimpan item pembelian');
                    }
                }
            }

            $purchaseModel->db->transCommit();
            
            session()->setFlashdata('success', 'Pembelian berhasil dicatat');
            return redirect()->to('/purchases');
            
        } catch (\Exception $e) {
            $purchaseModel->db->transRollback();
            session()->setFlashdata('errors', [$e->getMessage()]);
            return redirect()->back()->withInput();
        }
    }
    
    public function view($id)
    {
        $purchaseModel = new PurchaseModel();
        $purchaseItemModel = new PurchaseItemModel();
        
        $purchase = $purchaseModel->getPurchaseWithDetails($id);
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }
        
        $data = [
            'title' => 'Detail Pembelian - Warehouse Management System',
            'purchase' => $purchase,
            'items' => $purchaseItemModel->select('purchase_items.*, products.name as product_name, products.code as product_code')
                ->join('products', 'products.id = purchase_items.product_id')
                ->where('purchase_id', $id)
                ->findAll()
        ];
        
        return view('purchases/view', $data);
    }

    public function edit($id)
    {
        $purchaseModel = new PurchaseModel();
        $productModel = new ProductModel();
        $vendorModel = new VendorModel();
        $purchaseItemModel = new PurchaseItemModel();
        
        $purchase = $purchaseModel->find($id);
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }
        
        $data = [
            'title' => 'Edit Pembelian - Warehouse Management System',
            'purchase' => $purchase,
            'products' => $productModel->findAll(),
            'vendors' => $vendorModel->findAll(),
            'items' => $purchaseItemModel->where('purchase_id', $id)->findAll()
        ];
        
        return view('purchases/edit', $data);
    }

    public function update($id)
    {
        $purchaseModel = new PurchaseModel();
        $purchaseItemModel = new PurchaseItemModel();
        
        $purchase = $purchaseModel->find($id);
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        // Validasi input (sama seperti store)
        $rules = [
            'vendor_id' => 'required|integer|is_not_unique[vendors.id]',
            'purchase_date' => 'required|valid_date',
            'buyer_name' => 'required|min_length[3]|max_length[100]',
            'notes' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Start transaction
        $purchaseModel->db->transStart();

        try {
            // Update data pembelian
            $purchaseData = [
                'vendor_id' => $this->request->getPost('vendor_id'),
                'purchase_date' => $this->request->getPost('purchase_date'),
                'buyer_name' => $this->request->getPost('buyer_name'),
                'notes' => $this->request->getPost('notes')
            ];
            
            if (!$purchaseModel->update($id, $purchaseData)) {
                throw new \Exception('Gagal mengupdate data pembelian');
            }

            // Delete existing items and re-insert
            $purchaseItemModel->where('purchase_id', $id)->delete();

            // Insert new items
            $productIds = $this->request->getPost('product_id');
            $quantities = $this->request->getPost('quantity');
            
            if (!empty($productIds) && !empty($quantities)) {
                foreach ($productIds as $index => $productId) {
                    if (!empty($productId) && !empty($quantities[$index]) && $quantities[$index] > 0) {
                        $itemData = [
                            'purchase_id' => $id,
                            'product_id' => $productId,
                            'quantity' => $quantities[$index]
                        ];
                        
                        if (!$purchaseItemModel->insert($itemData)) {
                            throw new \Exception('Gagal menyimpan item pembelian');
                        }
                    }
                }
            }

            $purchaseModel->db->transCommit();
            
            session()->setFlashdata('success', 'Pembelian berhasil diupdate');
            return redirect()->to('/purchases');
            
        } catch (\Exception $e) {
            $purchaseModel->db->transRollback();
            session()->setFlashdata('errors', [$e->getMessage()]);
            return redirect()->back()->withInput();
        }
    }

    public function delete($id)
    {
        $purchaseModel = new PurchaseModel();
        $purchaseItemModel = new PurchaseItemModel();
        
        $purchase = $purchaseModel->find($id);
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        // Check if purchase can be deleted (only pending status can be deleted)
        if ($purchase['status'] !== 'pending') {
            session()->setFlashdata('error', 'Hanya pembelian dengan status pending yang dapat dihapus');
            return redirect()->to('/purchases');
        }

        // Start transaction
        $purchaseModel->db->transStart();

        try {
            // Delete purchase items first
            $purchaseItemModel->where('purchase_id', $id)->delete();
            
            // Delete purchase
            if (!$purchaseModel->delete($id)) {
                throw new \Exception('Gagal menghapus data pembelian');
            }

            $purchaseModel->db->transCommit();
            
            session()->setFlashdata('success', 'Pembelian berhasil dihapus');
            return redirect()->to('/purchases');
            
        } catch (\Exception $e) {
            $purchaseModel->db->transRollback();
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/purchases');
        }
    }

    public function updateStatus($id, $status)
    {
        $purchaseModel = new PurchaseModel();
        
        $purchase = $purchaseModel->find($id);
        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        $validStatuses = ['pending', 'received', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            session()->setFlashdata('error', 'Status tidak valid');
            return redirect()->to('/purchases');
        }

        if ($purchaseModel->update($id, ['status' => $status])) {
            session()->setFlashdata('success', "Status pembelian berhasil diubah menjadi {$status}");
        } else {
            session()->setFlashdata('error', 'Gagal mengubah status pembelian');
        }

        return redirect()->to('/purchases');
    }

    public function duplicate($id)
    {
        $purchaseModel = new PurchaseModel();
        $purchaseItemModel = new PurchaseItemModel();
        
        $originalPurchase = $purchaseModel->find($id);
        if (!$originalPurchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        $originalItems = $purchaseItemModel->where('purchase_id', $id)->findAll();

        // Start transaction
        $purchaseModel->db->transStart();

        try {
            // Create new purchase
            $newPurchaseData = [
                'vendor_id' => $originalPurchase['vendor_id'],
                'purchase_date' => date('Y-m-d'),
                'buyer_name' => $originalPurchase['buyer_name'],
                'status' => 'pending',
                'notes' => 'Duplikat dari Purchase #' . $originalPurchase['id']
            ];
            
            $newPurchaseId = $purchaseModel->insert($newPurchaseData);
            
            if (!$newPurchaseId) {
                throw new \Exception('Gagal menduplikasi data pembelian');
            }

            // Duplicate items
            foreach ($originalItems as $item) {
                $newItemData = [
                    'purchase_id' => $newPurchaseId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity']
                ];
                
                if (!$purchaseItemModel->insert($newItemData)) {
                    throw new \Exception('Gagal menduplikasi item pembelian');
                }
            }

            $purchaseModel->db->transCommit();
            
            session()->setFlashdata('success', 'Pembelian berhasil diduplikasi');
            return redirect()->to('/purchases/edit/' . $newPurchaseId);
            
        } catch (\Exception $e) {
            $purchaseModel->db->transRollback();
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/purchases');
        }
    }

    // API method for AJAX requests
    public function getVendorInfo($vendorId)
    {
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->find($vendorId);
        
        if ($vendor) {
            return $this->response->setJSON([
                'success' => true,
                'data' => $vendor
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vendor tidak ditemukan'
            ]);
        }
    }
}