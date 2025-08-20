<?php

namespace App\Controllers;

use App\Models\IncomingItemModel;
use App\Models\OutgoingItemModel;
use App\Models\ProductModel;

class Reports extends BaseController
{
    public function incoming()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        $incomingModel = new IncomingItemModel();
        $data['incoming_items'] = $incomingModel->getIncomingItemsWithDetails($startDate, $endDate);
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        
        return $this->renderView('reports/incoming', $data);
    }
    
    public function outgoing()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        $outgoingModel = new OutgoingItemModel();
        $data['outgoing_items'] = $outgoingModel->getOutgoingItemsWithDetails($startDate, $endDate);
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        
        return $this->renderView('reports/outgoing', $data);
    }
    
    public function stock()
    {
        $productModel = new ProductModel();
        $data['products'] = $productModel->getProductsWithCategory();
        
        return $this->renderView('reports/stock', $data);
    }
}