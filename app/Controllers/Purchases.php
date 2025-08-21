<?php

namespace App\Controllers;

use App\Models\PurchaseModel;
use App\Models\PurchaseDetailModel;
use App\Models\ProductModel;
use App\Models\VendorModel;

class Purchases extends BaseController
{
    protected $purchaseModel;
    protected $purchaseDetailModel;
    protected $productModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->purchaseModel = new PurchaseModel();
        $this->purchaseDetailModel = new PurchaseDetailModel();
        $this->productModel = new ProductModel();
        $this->vendorModel = new VendorModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Daftar Pembelian - Warehouse Management System',
            'purchases' => $this->purchaseModel->getPurchasesWithDetails()
        ];

        return view('purchases/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Pembelian - Warehouse Management System',
            'products' => $this->productModel->findAll(),
            'vendors' => $this->vendorModel->getVendorsForSelect(),
            'validation' => session()->getFlashdata('validation')
        ];

        return view('purchases/create', $data);
    }

    public function store()
    {
        // Debug: Log data yang diterima
        log_message('debug', 'POST data: ' . print_r($this->request->getPost(), true));

        // Validasi input
        $rules = [
            'vendor_id' => 'required|integer',
            'purchase_date' => 'required|valid_date',
            'buyer_name' => 'required|min_length[3]|max_length[100]',
            'product_id.*' => 'required|integer',
            'quantity.*' => 'required|decimal|greater_than[0]',
            'price.*' => 'required|decimal|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', 'Data tidak valid: ' . implode(', ', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $this->purchaseModel->db->transStart();

        try {
            // Simpan data pembelian utama
            $purchaseData = [
                'vendor_id' => $this->request->getPost('vendor_id'),
                'purchase_date' => $this->request->getPost('purchase_date'),
                'buyer_name' => $this->request->getPost('buyer_name'),
                'status' => 'pending',
                'total_amount' => 0,
                'notes' => null
            ];

            log_message('debug', 'Purchase data: ' . print_r($purchaseData, true));

            $purchaseId = $this->purchaseModel->insert($purchaseData);

            if (!$purchaseId) {
                $errors = $this->purchaseModel->errors();
                log_message('error', 'Purchase insert error: ' . print_r($errors, true));
                throw new \Exception('Gagal menyimpan data pembelian: ' . implode(', ', $errors));
            }

            log_message('debug', 'Purchase created with ID: ' . $purchaseId);

            // Simpan item pembelian
            $productIds = $this->request->getPost('product_id');
            $quantities = $this->request->getPost('quantity');
            $prices = $this->request->getPost('price');
            $totalAmount = 0;

            if (empty($productIds) || !is_array($productIds)) {
                throw new \Exception('Tidak ada item pembelian');
            }

            foreach ($productIds as $index => $productId) {
                if (!empty($productId) && !empty($quantities[$index]) && !empty($prices[$index])) {
                    $quantity = floatval($quantities[$index]);
                    $price = floatval($prices[$index]);
                    $subtotal = $quantity * $price;

                    $itemData = [
                        'purchase_id' => $purchaseId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $subtotal
                    ];

                    log_message('debug', 'Item data ' . $index . ': ' . print_r($itemData, true));

                    // Insert manual ke database untuk debugging
                    $result = $this->purchaseModel->db->table('purchase_details')->insert($itemData);

                    if (!$result) {
                        $error = $this->purchaseModel->db->error();
                        log_message('error', 'Detail insert error: ' . print_r($error, true));
                        throw new \Exception('Gagal menyimpan item pembelian: ' . $error['message']);
                    }

                    $totalAmount += $subtotal;
                    log_message('debug', 'Item saved successfully, total so far: ' . $totalAmount);
                }
            }

            if ($totalAmount == 0) {
                throw new \Exception('Tidak ada item valid untuk disimpan');
            }

            // Update total amount
            $this->purchaseModel->update($purchaseId, ['total_amount' => $totalAmount]);

            $this->purchaseModel->db->transComplete();

            if ($this->purchaseModel->db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            session()->setFlashdata('success', 'Pembelian berhasil dicatat dengan ' . count($productIds) . ' item');
            return redirect()->to('/purchases');
        } catch (\Exception $e) {
            $this->purchaseModel->db->transRollback();
            log_message('error', 'Store purchase error: ' . $e->getMessage());
            session()->setFlashdata('error', $e->getMessage());
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

        $data = [
            'title' => 'Detail Pembelian - Warehouse Management System',
            'purchase' => $purchase,
            'items' => $this->purchaseDetailModel->select('purchase_details.*, products.name as product_name, products.code as product_code')
                ->join('products', 'products.id = purchase_details.product_id')
                ->where('purchase_id', $id)
                ->findAll(),
            'vendor' => $this->vendorModel->find($purchase['vendor_id'])
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

        if ($purchase['status'] === 'received') {
            session()->setFlashdata('error', 'Pembelian yang sudah diterima tidak dapat diedit');
            return redirect()->to('/purchases');
        }

        $data = [
            'title' => 'Edit Pembelian - Warehouse Management System',
            'purchase' => $purchase,
            'products' => $this->productModel->findAll(),
            'vendors' => $this->vendorModel->getVendorsForSelect(),
            'items' => $this->purchaseDetailModel->where('purchase_id', $id)->findAll(),
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

        if ($purchase['status'] === 'received') {
            session()->setFlashdata('error', 'Pembelian yang sudah diterima tidak dapat diedit');
            return redirect()->to('/purchases');
        }

        // Debug: Log data yang diterima
        log_message('debug', 'UPDATE POST data: ' . print_r($this->request->getPost(), true));

        // Validasi input
        $rules = [
            'vendor_id' => 'required|integer',
            'purchase_date' => 'required|valid_date',
            'buyer_name' => 'required|min_length[3]|max_length[100]',
            'product_id.*' => 'required|integer',
            'quantity.*' => 'required|decimal|greater_than[0]',
            'price.*' => 'required|decimal|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', 'Data tidak valid: ' . implode(', ', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $this->purchaseModel->db->transStart();

        try {
            // Update data pembelian utama
            $purchaseData = [
                'vendor_id' => $this->request->getPost('vendor_id'),
                'purchase_date' => $this->request->getPost('purchase_date'),
                'buyer_name' => $this->request->getPost('buyer_name')
                // notes tidak diupdate karena tidak ada di form
            ];

            log_message('debug', 'Updating purchase data: ' . print_r($purchaseData, true));

            $updateResult = $this->purchaseModel->update($id, $purchaseData);
            if (!$updateResult) {
                $errors = $this->purchaseModel->errors();
                log_message('error', 'Purchase update error: ' . print_r($errors, true));
                throw new \Exception('Gagal update data pembelian: ' . implode(', ', $errors));
            }

            // Hapus item lama
            log_message('debug', 'Deleting old items for purchase ID: ' . $id);
            $deleteResult = $this->purchaseModel->db->table('purchase_details')->where('purchase_id', $id)->delete();
            if (!$deleteResult) {
                $error = $this->purchaseModel->db->error();
                log_message('error', 'Delete old items error: ' . print_r($error, true));
                throw new \Exception('Gagal hapus item lama: ' . $error['message']);
            }

            // Simpan item baru
            $productIds = $this->request->getPost('product_id');
            $quantities = $this->request->getPost('quantity');
            $prices = $this->request->getPost('price');
            $totalAmount = 0;

            if (empty($productIds) || !is_array($productIds)) {
                throw new \Exception('Tidak ada item pembelian untuk disimpan');
            }

            foreach ($productIds as $index => $productId) {
                if (!empty($productId) && !empty($quantities[$index]) && !empty($prices[$index])) {
                    $quantity = floatval($quantities[$index]);
                    $price = floatval($prices[$index]);
                    $subtotal = $quantity * $price;

                    $itemData = [
                        'purchase_id' => $id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $subtotal
                    ];

                    log_message('debug', 'Inserting new item ' . $index . ': ' . print_r($itemData, true));

                    $insertResult = $this->purchaseModel->db->table('purchase_details')->insert($itemData);
                    if (!$insertResult) {
                        $error = $this->purchaseModel->db->error();
                        log_message('error', 'Insert new item error: ' . print_r($error, true));
                        throw new \Exception('Gagal simpan item baru: ' . $error['message']);
                    }

                    $totalAmount += $subtotal;
                }
            }

            if ($totalAmount == 0) {
                throw new \Exception('Tidak ada item valid untuk disimpan');
            }

            // Update total amount
            log_message('debug', 'Updating total amount to: ' . $totalAmount);
            $updateTotalResult = $this->purchaseModel->update($id, ['total_amount' => $totalAmount]);
            if (!$updateTotalResult) {
                $errors = $this->purchaseModel->errors();
                log_message('error', 'Update total error: ' . print_r($errors, true));
                throw new \Exception('Gagal update total: ' . implode(', ', $errors));
            }

            $this->purchaseModel->db->transComplete();

            if ($this->purchaseModel->db->transStatus() === false) {
                log_message('error', 'Transaction failed in update method');
                throw new \Exception('Transaksi database gagal');
            }

            session()->setFlashdata('success', 'Pembelian berhasil diperbarui');
            return redirect()->to('/purchases');
        } catch (\Exception $e) {
            $this->purchaseModel->db->transRollback();
            log_message('error', 'Update purchase error: ' . $e->getMessage());
            log_message('error', 'Update purchase trace: ' . $e->getTraceAsString());
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function delete($id)
    {
        $purchase = $this->purchaseModel->find($id);

        if (!$purchase) {
            session()->setFlashdata('error', 'Data pembelian tidak ditemukan');
            return redirect()->to('/purchases');
        }

        if ($purchase['status'] === 'received') {
            session()->setFlashdata('error', 'Pembelian yang sudah diterima tidak dapat dihapus');
            return redirect()->to('/purchases');
        }

        $this->purchaseModel->db->transStart();

        try {
            log_message('debug', 'Deleting purchase ID: ' . $id);

            // Hapus item pembelian dulu
            $deleteItemsResult = $this->purchaseModel->db->table('purchase_details')->where('purchase_id', $id)->delete();
            if (!$deleteItemsResult) {
                $error = $this->purchaseModel->db->error();
                log_message('error', 'Delete items error: ' . print_r($error, true));
                throw new \Exception('Gagal hapus item pembelian: ' . $error['message']);
            }

            // Hapus pembelian
            $deletePurchaseResult = $this->purchaseModel->delete($id);
            if (!$deletePurchaseResult) {
                $errors = $this->purchaseModel->errors();
                log_message('error', 'Delete purchase error: ' . print_r($errors, true));
                throw new \Exception('Gagal hapus pembelian: ' . implode(', ', $errors));
            }

            $this->purchaseModel->db->transComplete();

            if ($this->purchaseModel->db->transStatus() === false) {
                log_message('error', 'Transaction failed in delete method');
                throw new \Exception('Transaksi database gagal');
            }

            session()->setFlashdata('success', 'Pembelian berhasil dihapus');
        } catch (\Exception $e) {
            $this->purchaseModel->db->transRollback();
            log_message('error', 'Delete purchase error: ' . $e->getMessage());
            session()->setFlashdata('error', $e->getMessage());
        }

        return redirect()->to('/purchases');
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

        if ($this->purchaseModel->updateStatus($id, $status)) {
            $statusText = [
                'pending' => 'pending',
                'received' => 'diterima',
                'cancelled' => 'dibatalkan'
            ];

            session()->setFlashdata('success', 'Status pembelian berhasil diubah menjadi ' . $statusText[$status]);
        } else {
            session()->setFlashdata('error', 'Gagal mengubah status pembelian');
        }

        return redirect()->to('/purchases/view/' . $id);
    }

    public function duplicate($id)
    {
        $result = $this->purchaseModel->duplicatePurchase($id);

        if ($result['success']) {
            session()->setFlashdata('success', 'Pembelian berhasil diduplikasi');
            return redirect()->to('/purchases/view/' . $result['purchase_id']);
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back();
        }
    }
}
