<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\IncomingItemModel;
use App\Models\OutgoingItemModel;
use App\Models\PurchaseModel;
use App\Models\VendorModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $productModel;
    protected $categoryModel;
    protected $incomingModel;
    protected $outgoingModel;
    protected $purchaseModel;
    protected $vendorModel;
    protected $userModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->incomingModel = new IncomingItemModel();
        $this->outgoingModel = new OutgoingItemModel();
        $this->purchaseModel = new PurchaseModel();
        $this->vendorModel = new VendorModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Dashboard - Vadhana Warehouse',
            'stats' => $this->getMainStatistics(),
            'chart_data' => $this->getChartData(),
            'recent_incoming' => $this->getRecentIncoming(5),
            'recent_outgoing' => $this->getRecentOutgoing(5),
            'low_stock_products' => $this->getLowStockProducts(10),
            'low_stock_count' => $this->getLowStockCount()
        ];

        // Hanya return satu view saja
        return view('dashboard/index', $data);
    }

    private function getMainStatistics()
    {
        $stats = [];

        // Product statistics
        $stats['total_products'] = $this->productModel->countAll();
        $stats['total_categories'] = $this->categoryModel->countAll();

        // Get low stock and out of stock products
        $lowStockProducts = $this->getLowStockProducts();
        $outOfStockProducts = $this->getOutOfStockProducts();

        $stats['low_stock_products'] = count($lowStockProducts);
        $stats['out_of_stock_products'] = count($outOfStockProducts);

        // Transaction statistics for today
        $today = date('Y-m-d');
        $stats['today_incoming'] = $this->incomingModel->where('DATE(date)', $today)->countAllResults(false);
        $stats['today_outgoing'] = $this->outgoingModel->where('DATE(date)', $today)->countAllResults(false);

        // Transaction statistics for this month
        $thisMonth = date('Y-m');
        $stats['monthly_incoming'] = $this->incomingModel->where('DATE_FORMAT(date, "%Y-%m")', $thisMonth)->countAllResults(false);
        $stats['monthly_outgoing'] = $this->outgoingModel->where('DATE_FORMAT(date, "%Y-%m")', $thisMonth)->countAllResults(false);

        // Purchase statistics
        $stats['total_purchases'] = $this->purchaseModel->countAll();
        $stats['pending_purchases'] = $this->purchaseModel->where('status', 'pending')->countAllResults(false);

        // Vendor statistics
        $stats['total_vendors'] = $this->vendorModel->countAll();

        // User statistics (for admin only)
        if (session()->get('role') === 'admin') {
            $stats['total_users'] = $this->userModel->countAll();
            $stats['active_users'] = $this->userModel->where('status', 'active')->countAllResults(false);
        }

        return $stats;
    }

    private function getChartData()
    {
        $chartData = [];

        // Transaction trends (last 7 days)
        $transactionData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateLabel = date('M d', strtotime("-{$i} days"));

            $incoming = $this->incomingModel->where('DATE(date)', $date)->countAllResults(false);
            $outgoing = $this->outgoingModel->where('DATE(date)', $date)->countAllResults(false);

            $transactionData[] = [
                'date' => $dateLabel,
                'incoming' => $incoming,
                'outgoing' => $outgoing
            ];
        }
        $chartData['transactions'] = $transactionData;

        // Stock status distribution
        $totalProducts = $this->productModel->countAll();
        $lowStockCount = count($this->getLowStockProducts());
        $outOfStockCount = count($this->getOutOfStockProducts());
        $inStockCount = $totalProducts - $lowStockCount - $outOfStockCount;

        $chartData['stock_status'] = [
            'in_stock' => $inStockCount,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount
        ];

        return $chartData;
    }

    private function getRecentIncoming($limit = 5)
    {
        return $this->incomingModel
            ->select('incoming_items.*, products.name as product_name, products.unit')
            ->join('products', 'products.id = incoming_items.product_id')
            ->orderBy('incoming_items.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    private function getRecentOutgoing($limit = 5)
    {
        return $this->outgoingModel
            ->select('outgoing_items.*, products.name as product_name, products.unit')
            ->join('products', 'products.id = outgoing_items.product_id')
            ->orderBy('outgoing_items.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    private function getLowStockProducts($limit = null)
    {
        $builder = $this->productModel
            ->select('products.*, categories.name as category_name')
            ->join('categories', 'categories.id = products.category_id')
            ->where('products.stock <=', 10)
            ->where('products.stock >', 0)
            ->orderBy('products.stock', 'ASC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    private function getOutOfStockProducts()
    {
        return $this->productModel
            ->select('products.*, categories.name as category_name')
            ->join('categories', 'categories.id = products.category_id')
            ->where('products.stock', 0)
            ->findAll();
    }

    private function getLowStockCount()
    {
        return count($this->getLowStockProducts()) + count($this->getOutOfStockProducts());
    }

    public function getTransactionChart()
    {
        $period = $this->request->getGet('period') ?? '7days';
        $data = [];

        switch ($period) {
            case '7days':
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $label = date('M d', strtotime("-{$i} days"));

                    $incoming = $this->incomingModel->where('DATE(date)', $date)->countAllResults(false);
                    $outgoing = $this->outgoingModel->where('DATE(date)', $date)->countAllResults(false);

                    $data[] = [
                        'label' => $label,
                        'incoming' => $incoming,
                        'outgoing' => $outgoing
                    ];
                }
                break;

            case '30days':
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $label = date('M d', strtotime("-{$i} days"));

                    $incoming = $this->incomingModel->where('DATE(date)', $date)->countAllResults(false);
                    $outgoing = $this->outgoingModel->where('DATE(date)', $date)->countAllResults(false);

                    $data[] = [
                        'label' => $label,
                        'incoming' => $incoming,
                        'outgoing' => $outgoing
                    ];
                }
                break;

            case '12months':
                for ($i = 11; $i >= 0; $i--) {
                    $month = date('Y-m', strtotime("-{$i} months"));
                    $label = date('M Y', strtotime("-{$i} months"));

                    $incoming = $this->incomingModel->where('DATE_FORMAT(date, "%Y-%m")', $month)->countAllResults(false);
                    $outgoing = $this->outgoingModel->where('DATE_FORMAT(date, "%Y-%m")', $month)->countAllResults(false);

                    $data[] = [
                        'label' => $label,
                        'incoming' => $incoming,
                        'outgoing' => $outgoing
                    ];
                }
                break;
        }

        return $this->response->setJSON($data);
    }

    public function getStockAlert()
    {
        $lowStockProducts = $this->getLowStockProducts();
        $outOfStockProducts = $this->getOutOfStockProducts();

        $alertCount = count($lowStockProducts) + count($outOfStockProducts);

        $alerts = [];

        // Add out of stock alerts
        foreach ($outOfStockProducts as $product) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "Produk {$product['name']} habis!",
                'product_id' => $product['id']
            ];
        }

        // Add low stock alerts
        foreach ($lowStockProducts as $product) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Produk {$product['name']} stok rendah ({$product['stock']} {$product['unit']})",
                'product_id' => $product['id']
            ];
        }

        return $this->response->setJSON([
            'count' => $alertCount,
            'alerts' => $alerts
        ]);
    }

    public function getQuickStats()
    {
        // For AJAX requests to update dashboard stats in real-time
        $stats = [
            'today_incoming' => $this->incomingModel->where('DATE(date)', date('Y-m-d'))->countAllResults(false),
            'today_outgoing' => $this->outgoingModel->where('DATE(date)', date('Y-m-d'))->countAllResults(false),
            'low_stock_count' => $this->getLowStockCount(),
            'pending_purchases' => $this->purchaseModel->where('status', 'pending')->countAllResults(false),
            'last_updated' => date('H:i:s')
        ];

        return $this->response->setJSON($stats);
    }
}
