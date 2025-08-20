<?php

namespace App\Controllers;

use App\Models\PurchaseModel;
use App\Models\PurchaseItemModel;
use App\Models\ProductModel;

class Purchases extends BaseController
{
    public function index()
    {
        $purchaseModel = new PurchaseModel();
        $data['purchases'] = $purchaseModel->getPurchasesWithItems();
        
        return $this->renderView('purchases/index', $data);
    }
    
    public function create()
    {
        $productModel = new ProductModel();
        $data['products'] = $productModel->findAll();
        
        return $this->renderView('purchases/create', $data);
    }
    
    public function store()
    {
        $purchaseModel = new PurchaseModel();
        $purchaseItemModel = new PurchaseItemModel();
        
        // Simpan data pembelian
        $purchaseData = [
            'vendor_name' => $this->request->getPost('vendor_name'),
            'vendor_address' => $this->request->getPost('vendor_address'),
            'purchase_date' => $this->request->getPost('purchase_date'),
            'buyer_name' => $this->request->getPost('buyer_name')
        ];
        
        if ($purchaseId = $purchaseModel->insert($purchaseData)) {
            // Simpan item pembelian
            $productIds = $this->request->getPost('product_id');
            $quantities = $this->request->getPost('quantity');
            
            foreach ($productIds as $index => $productId) {
                if (!empty($productId) && !empty($quantities[$index])) {
                    $itemData = [
                        'purchase_id' => $purchaseId,
                        'product_id' => $productId,
                        'quantity' => $quantities[$index]
                    ];
                    $purchaseItemModel->insert($itemData);
                }
            }
            
            session()->setFlashdata('success', 'Pembelian berhasil dicatat');
            return redirect()->to('/purchases');
        } else {
            session()->setFlashdata('errors', $purchaseModel->errors());
            return redirect()->back()->withInput();
        }
    }
    
    public function view($id)
    {
        $purchaseModel = new PurchaseModel();
        $purchaseItemModel = new PurchaseItemModel();
        
        $data['purchase'] = $purchaseModel->find($id);
        $data['items'] = $purchaseItemModel->select('purchase_items.*, products.name as product_name, products.code as product_code')
            ->join('products', 'products.id = purchase_items.product_id')
            ->where('purchase_id', $id)
            ->findAll();
        
        return $this->renderView('purchases/view', $data);
    }
}