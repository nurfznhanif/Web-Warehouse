<?php

namespace App\Controllers;

use App\Models\IncomingItemModel;
use App\Models\OutgoingItemModel;
use App\Models\ProductModel;

class Reports extends BaseController
{
    protected $incomingModel;
    protected $outgoingModel;
    protected $productModel;

    public function __construct()
    {
        $this->incomingModel = new IncomingItemModel();
        $this->outgoingModel = new OutgoingItemModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Laporan - Vadhana Warehouse'
        ];

        return view('reports/index', $data);
    }

    public function incoming()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $data = [
            'title' => 'Laporan Barang Masuk - Vadhana Warehouse',
            'incoming_items' => [],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => [
                'total_quantity' => 0,
                'total_transactions' => 0,
                'date_range' => ''
            ]
        ];

        if ($startDate && $endDate) {
            $data['incoming_items'] = $this->incomingModel->getIncomingItemsWithDetails(null, null, null, $startDate, $endDate);

            // Calculate summary
            $totalQuantity = 0;
            foreach ($data['incoming_items'] as $item) {
                $totalQuantity += $item['quantity'];
            }

            $data['summary'] = [
                'total_quantity' => $totalQuantity,
                'total_transactions' => count($data['incoming_items']),
                'date_range' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate))
            ];
        }

        return view('reports/incoming', $data);
    }

    public function outgoing()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $data = [
            'title' => 'Laporan Barang Keluar - Vadhana Warehouse',
            'outgoing_items' => [],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => [
                'total_quantity' => 0,
                'total_transactions' => 0,
                'date_range' => ''
            ]
        ];

        if ($startDate && $endDate) {
            $data['outgoing_items'] = $this->outgoingModel->getOutgoingItemsWithDetails(null, null, null, $startDate, $endDate);

            // Calculate summary
            $totalQuantity = 0;
            foreach ($data['outgoing_items'] as $item) {
                $totalQuantity += $item['quantity'];
            }

            $data['summary'] = [
                'total_quantity' => $totalQuantity,
                'total_transactions' => count($data['outgoing_items']),
                'date_range' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate))
            ];
        }

        return view('reports/outgoing', $data);
    }

    public function stock()
    {
        $products = $this->productModel->getProductsWithCategory();

        // Calculate stock statistics
        $totalProducts = count($products);
        $inStock = 0;
        $lowStock = 0;
        $outOfStock = 0;
        $totalValue = 0;

        foreach ($products as $product) {
            $stock = $product['stock'] ?? 0;
            $minStock = $product['min_stock'] ?? 10;
            $price = $product['price'] ?? 0;

            $totalValue += $stock * $price;

            if ($stock <= 0) {
                $outOfStock++;
            } elseif ($stock <= $minStock) {
                $lowStock++;
            } else {
                $inStock++;
            }
        }

        $data = [
            'title' => 'Laporan Stok Barang - Vadhana Warehouse',
            'products' => $products,
            'statistics' => [
                'total_products' => $totalProducts,
                'in_stock' => $inStock,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'total_value' => $totalValue
            ]
        ];

        return view('reports/stock', $data);
    }
}
