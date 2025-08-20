<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\IncomingItemModel;
use App\Models\OutgoingItemModel;
use App\Models\PurchaseModel;
use CodeIgniter\Controller;

class Dashboard extends Controller
{
    protected $productModel;
    protected $categoryModel;
    protected $incomingItemModel;
    protected $outgoingItemModel;
    protected $purchaseModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->incomingItemModel = new IncomingItemModel();
        $this->outgoingItemModel = new OutgoingItemModel();
        $this->purchaseModel = new PurchaseModel();
    }

    public function index()
    {
        // Get statistics
        $stats = $this->getDashboardStats();

        // Get recent transactions
        $recentIncoming = $this->incomingItemModel->getRecentTransactions(5);
        $recentOutgoing = $this->outgoingItemModel->getRecentTransactions(5);

        // Get low stock products
        $lowStockProducts = $this->productModel->getLowStockProducts();

        // Get chart data
        $chartData = $this->getChartData();

        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recent_incoming' => $recentIncoming,
            'recent_outgoing' => $recentOutgoing,
            'low_stock_products' => $lowStockProducts,
            'chart_data' => $chartData,
            'low_stock_count' => count($lowStockProducts)
        ];

        return view('dashboard/index', $data);
    }

    private function getDashboardStats()
    {
        $db = \Config\Database::connect();

        // Total products
        $totalProducts = $this->productModel->countAll();

        // Total categories
        $totalCategories = $this->categoryModel->countAll();

        // Low stock products
        $lowStockProducts = $db->table('products')
            ->where('stock <=', 'min_stock', false)
            ->countAllResults();

        // Out of stock products
        $outOfStockProducts = $db->table('products')
            ->where('stock', 0)
            ->countAllResults();

        // Today's transactions
        $today = date('Y-m-d');
        $todayIncoming = $db->table('incoming_items')
            ->where('DATE(date)', $today)
            ->countAllResults();

        $todayOutgoing = $db->table('outgoing_items')
            ->where('DATE(date)', $today)
            ->countAllResults();

        // This month's transactions
        $thisMonth = date('Y-m');
        $monthlyIncoming = $db->table('incoming_items')
            ->where('DATE_FORMAT(date, "%Y-%m")', $thisMonth)
            ->countAllResults();

        $monthlyOutgoing = $db->table('outgoing_items')
            ->where('DATE_FORMAT(date, "%Y-%m")', $thisMonth)
            ->countAllResults();

        // Pending purchases
        $pendingPurchases = $db->table('purchases')
            ->where('status', 'pending')
            ->countAllResults();

        // Total stock value (simplified calculation)
        $totalStockValue = $db->table('products')
            ->selectSum('stock')
            ->get()
            ->getRow()
            ->stock ?? 0;

        return [
            'total_products' => $totalProducts,
            'total_categories' => $totalCategories,
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
            'today_incoming' => $todayIncoming,
            'today_outgoing' => $todayOutgoing,
            'monthly_incoming' => $monthlyIncoming,
            'monthly_outgoing' => $monthlyOutgoing,
            'pending_purchases' => $pendingPurchases,
            'total_stock_value' => $totalStockValue
        ];
    }

    private function getChartData()
    {
        $db = \Config\Database::connect();

        // Last 7 days transaction data
        $transactionData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateLabel = date('M d', strtotime("-{$i} days"));

            $incoming = $db->table('incoming_items')
                ->where('DATE(date)', $date)
                ->countAllResults();

            $outgoing = $db->table('outgoing_items')
                ->where('DATE(date)', $date)
                ->countAllResults();

            $transactionData[] = [
                'date' => $dateLabel,
                'incoming' => $incoming,
                'outgoing' => $outgoing
            ];
        }

        // Products by category
        $categoryData = $db->table('products p')
            ->select('c.name as category_name, COUNT(p.id) as product_count')
            ->join('categories c', 'p.category_id = c.id')
            ->groupBy('c.id, c.name')
            ->orderBy('product_count', 'DESC')
            ->get()
            ->getResultArray();

        // Stock status distribution
        $stockStatus = [
            'in_stock' => $db->table('products')->where('stock >', 'min_stock', false)->countAllResults(),
            'low_stock' => $db->table('products')->where('stock <=', 'min_stock', false)->where('stock >', 0)->countAllResults(),
            'out_of_stock' => $db->table('products')->where('stock', 0)->countAllResults()
        ];

        return [
            'transactions' => $transactionData,
            'categories' => $categoryData,
            'stock_status' => $stockStatus
        ];
    }

    public function getTransactionChart()
    {
        $period = $this->request->getVar('period') ?? '7days';
        $db = \Config\Database::connect();

        $data = [];

        switch ($period) {
            case '7days':
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $dateLabel = date('M d', strtotime("-{$i} days"));

                    $incoming = $db->table('incoming_items')
                        ->selectSum('quantity')
                        ->where('DATE(date)', $date)
                        ->get()
                        ->getRow()
                        ->quantity ?? 0;

                    $outgoing = $db->table('outgoing_items')
                        ->selectSum('quantity')
                        ->where('DATE(date)', $date)
                        ->get()
                        ->getRow()
                        ->quantity ?? 0;

                    $data[] = [
                        'label' => $dateLabel,
                        'incoming' => (float)$incoming,
                        'outgoing' => (float)$outgoing
                    ];
                }
                break;

            case '30days':
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $dateLabel = date('M d', strtotime("-{$i} days"));

                    $incoming = $db->table('incoming_items')
                        ->selectSum('quantity')
                        ->where('DATE(date)', $date)
                        ->get()
                        ->getRow()
                        ->quantity ?? 0;

                    $outgoing = $db->table('outgoing_items')
                        ->selectSum('quantity')
                        ->where('DATE(date)', $date)
                        ->get()
                        ->getRow()
                        ->quantity ?? 0;

                    $data[] = [
                        'label' => $dateLabel,
                        'incoming' => (float)$incoming,
                        'outgoing' => (float)$outgoing
                    ];
                }
                break;

            case '12months':
                for ($i = 11; $i >= 0; $i--) {
                    $month = date('Y-m', strtotime("-{$i} months"));
                    $monthLabel = date('M Y', strtotime("-{$i} months"));

                    $incoming = $db->table('incoming_items')
                        ->selectSum('quantity')
                        ->where('DATE_FORMAT(date, "%Y-%m")', $month)
                        ->get()
                        ->getRow()
                        ->quantity ?? 0;

                    $outgoing = $db->table('outgoing_items')
                        ->selectSum('quantity')
                        ->where('DATE_FORMAT(date, "%Y-%m")', $month)
                        ->get()
                        ->getRow()
                        ->quantity ?? 0;

                    $data[] = [
                        'label' => $monthLabel,
                        'incoming' => (float)$incoming,
                        'outgoing' => (float)$outgoing
                    ];
                }
                break;
        }

        return $this->response->setJSON($data);
    }

    public function getStockAlert()
    {
        $lowStockProducts = $this->productModel->getLowStockProducts();

        return $this->response->setJSON([
            'count' => count($lowStockProducts),
            'products' => array_slice($lowStockProducts, 0, 5) // Limit to 5 for notification
        ]);
    }
}
