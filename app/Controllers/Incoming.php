<?php

namespace App\Controllers;

use App\Models\IncomingItemModel;
use App\Models\PurchaseModel;
use App\Models\ProductModel;

class Incoming extends BaseController
{
    public function index()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        $incomingModel = new IncomingItemModel();
        $data['incoming_items'] = $incomingModel->getIncomingItemsWithDetails($startDate, $endDate);
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        
        return $this->renderView('incoming/index', $data);
    }
    
    public function create()
    {
        $purchaseModel = new PurchaseModel();
        $data['purchases'] = $purchaseModel->findAll();
        
        return $this->renderView('incoming/create', $data);
    }
    
    public function getPurchaseItems($purchaseId)
    {
        $purchaseItemModel = new PurchaseItemModel();
        $items = $purchaseItemModel->select('purchase_items.*, products.name as product_name, products.code as product_code')
            ->join('products', 'products.id = purchase_items.product_id')
            ->where('purchase_id', $purchaseId)
            ->findAll();
            
        return $this->response->setJSON($items);
    }
    
    public function store()
    {
        $incomingModel = new IncomingItemModel();
        $productModel = new ProductModel();
        
        $purchaseId = $this->request->getPost('purchase_id');
        $productIds = $this->request->getPost('product_id');
        $quantities = $this->request->getPost('quantity');
        
        foreach ($productIds as $index => $productId) {
            if (!empty($productId) && !empty($quantities[$index])) {
                $data = [
                    'product_id' => $productId,
                    'purchase_id' => $purchaseId,
                    'date' => date('Y-m-d H:i:s'),
                    'quantity' => $quantities[$index]
                ];
                
                if ($incomingModel->insert($data)) {
                    // Update stok produk
                    $productModel->updateStock($productId, $quantities[$index]);
                }
            }
        }
        
        session()->setFlashdata('success', 'Barang masuk berhasil dicatat');
        return redirect()->to('/incoming');
    }
}