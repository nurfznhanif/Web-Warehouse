<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\IncomingItemModel;
use App\Models\OutgoingItemModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $productModel = new ProductModel();
        $incomingModel = new IncomingItemModel();
        $outgoingModel = new OutgoingItemModel();
        
        $data = [
            'total_products' => $productModel->countAll(),
            'today_incoming' => $incomingModel->where('date >=', date('Y-m-d 00:00:00'))->countAllResults(),
            'today_outgoing' => $outgoingModel->where('date >=', date('Y-m-d 00:00:00'))->countAllResults(),
            'low_stock_products' => $productModel->where('stock <', 10)->findAll()
        ];
        
        return $this->renderView('dashboard', $data);
    }
}