<?php

namespace App\Controllers;

use App\Models\OutgoingItemModel;
use App\Models\ProductModel;

class Outgoing extends BaseController
{
    public function index()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        $outgoingModel = new OutgoingItemModel();
        $data['outgoing_items'] = $outgoingModel->getOutgoingItemsWithDetails($startDate, $endDate);
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        
        return $this->renderView('outgoing/index', $data);
    }
    
    public function create()
    {
        $productModel = new ProductModel();
        $data['products'] = $productModel->findAll();
        
        return $this->renderView('outgoing/create', $data);
    }
    
    public function store()
    {
        $outgoingModel = new OutgoingItemModel();
        $productModel = new ProductModel();
        
        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');
        $description = $this->request->getPost('description');
        
        $data = [
            'product_id' => $productId,
            'date' => date('Y-m-d H:i:s'),
            'quantity' => $quantity,
            'description' => $description
        ];
        
        if ($outgoingModel->insert($data)) {
            // Update stok produk (dikurangi)
            if ($productModel->updateStock($productId, -$quantity)) {
                session()->setFlashdata('success', 'Barang keluar berhasil dicatat');
            } else {
                session()->setFlashdata('error', 'Stok tidak mencukupi');
                // Hapus transaksi jika stok tidak mencukupi
                $outgoingModel->delete($outgoingModel->getInsertID());
            }
        } else {
            session()->setFlashdata('error', 'Gagal mencatat barang keluar');
        }
        
        return redirect()->to('/outgoing');
    }
}