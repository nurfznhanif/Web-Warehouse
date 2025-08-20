<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use CodeIgniter\Controller;

class Products extends Controller
{
    protected $productModel;
    protected $categoryModel;
    protected $session;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        $perPage = 10;
        $currentPage = $this->request->getVar('page') ? $this->request->getVar('page') : 1;
        $search = $this->request->getVar('search');

        $offset = ($currentPage - 1) * $perPage;

        $products = $this->productModel->getProductsWithCategory($perPage, $offset, $search);
        $totalProducts = $this->productModel->countProductsWithCategory($search);

        $pager = \Config\Services::pager();
        $pager->setPath('products');

        $data = [
            'title' => 'Manajemen Produk',
            'products' => $products,
            'pager' => $pager->makeLinks($currentPage, $perPage, $totalProducts, 'custom_pagination'),
            'search' => $search,
            'total' => $totalProducts
        ];

        return view('products/index', $data);
    }

    public function create()
    {
        $categories = $this->categoryModel->findAll();

        $data = [
            'title' => 'Tambah Produk',
            'categories' => $categories
        ];

        if ($this->request->getMethod() === 'POST') {
            return $this->store();
        }

        return view('products/create', $data);
    }

    public function store()
    {
        $rules = [
            'category_id' => 'required|integer',
            'name' => 'required|min_length[3]|max_length[200]',
            'code' => 'required|min_length[3]|max_length[50]|is_unique[products.code]',
            'unit' => 'required|min_length[2]|max_length[50]',
            'stock' => 'permit_empty|decimal',
            'min_stock' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            $categories = $this->categoryModel->findAll();
            return view('products/create', [
                'title' => 'Tambah Produk',
                'categories' => $categories,
                'validation' => $this->validator,
                'old' => $this->request->getPost()
            ]);
        }

        $productData = [
            'category_id' => $this->request->getPost('category_id'),
            'name' => $this->request->getPost('name'),
            'code' => strtoupper($this->request->getPost('code')),
            'unit' => $this->request->getPost('unit'),
            'stock' => $this->request->getPost('stock') ?: 0,
            'min_stock' => $this->request->getPost('min_stock') ?: 0
        ];

        if ($this->productModel->save($productData)) {
            session()->setFlashdata('success', 'Produk berhasil ditambahkan!');
            return redirect()->to('/products');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan produk!');
            $categories = $this->categoryModel->findAll();
            return view('products/create', [
                'title' => 'Tambah Produk',
                'categories' => $categories,
                'old' => $this->request->getPost()
            ]);
        }
    }

    public function edit($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan!');
            return redirect()->to('/products');
        }

        $categories = $this->categoryModel->findAll();

        $data = [
            'title' => 'Edit Produk',
            'product' => $product,
            'categories' => $categories
        ];

        if ($this->request->getMethod() === 'POST') {
            return $this->update($id);
        }

        return view('products/edit', $data);
    }

    public function update($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan!');
            return redirect()->to('/products');
        }

        $rules = [
            'category_id' => 'required|integer',
            'name' => 'required|min_length[3]|max_length[200]',
            'code' => "required|min_length[3]|max_length[50]|is_unique[products.code,id,{$id}]",
            'unit' => 'required|min_length[2]|max_length[50]',
            'stock' => 'permit_empty|decimal',
            'min_stock' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            $categories = $this->categoryModel->findAll();
            return view('products/edit', [
                'title' => 'Edit Produk',
                'product' => $product,
                'categories' => $categories,
                'validation' => $this->validator,
                'old' => $this->request->getPost()
            ]);
        }

        $productData = [
            'category_id' => $this->request->getPost('category_id'),
            'name' => $this->request->getPost('name'),
            'code' => strtoupper($this->request->getPost('code')),
            'unit' => $this->request->getPost('unit'),
            'stock' => $this->request->getPost('stock') ?: 0,
            'min_stock' => $this->request->getPost('min_stock') ?: 0
        ];

        if ($this->productModel->update($id, $productData)) {
            session()->setFlashdata('success', 'Produk berhasil diupdate!');
            return redirect()->to('/products');
        } else {
            session()->setFlashdata('error', 'Gagal mengupdate produk!');
            $categories = $this->categoryModel->findAll();
            return view('products/edit', [
                'title' => 'Edit Produk',
                'product' => $product,
                'categories' => $categories,
                'old' => $this->request->getPost()
            ]);
        }
    }

    public function delete($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan!');
            return redirect()->to('/products');
        }

        // Check if product has transactions
        $db = \Config\Database::connect();
        $incomingCount = $db->table('incoming_items')->where('product_id', $id)->countAllResults();
        $outgoingCount = $db->table('outgoing_items')->where('product_id', $id)->countAllResults();

        if ($incomingCount > 0 || $outgoingCount > 0) {
            session()->setFlashdata('error', 'Tidak dapat menghapus produk yang memiliki transaksi!');
            return redirect()->to('/products');
        }

        if ($this->productModel->delete($id)) {
            session()->setFlashdata('success', 'Produk berhasil dihapus!');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus produk!');
        }

        return redirect()->to('/products');
    }

    public function view($id)
    {
        $product = $this->productModel->getProductWithCategory($id);
        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan!');
            return redirect()->to('/products');
        }

        // Get recent transactions
        $db = \Config\Database::connect();

        $incomingItems = $db->table('incoming_items i')
            ->select('i.*, p.buyer_name, v.name as vendor_name')
            ->join('purchases p', 'i.purchase_id = p.id')
            ->join('vendors v', 'p.vendor_id = v.id')
            ->where('i.product_id', $id)
            ->orderBy('i.date', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $outgoingItems = $db->table('outgoing_items')
            ->where('product_id', $id)
            ->orderBy('date', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Detail Produk',
            'product' => $product,
            'incoming_items' => $incomingItems,
            'outgoing_items' => $outgoingItems
        ];

        return view('products/view', $data);
    }

    public function lowStock()
    {
        $lowStockProducts = $this->productModel->getLowStockProducts();

        $data = [
            'title' => 'Produk Stok Rendah',
            'products' => $lowStockProducts
        ];

        return view('products/low_stock', $data);
    }

    public function checkStock()
    {
        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');

        $result = $this->productModel->checkStockAvailability($productId, $quantity);

        return $this->response->setJSON($result);
    }

    public function search()
    {
        $keyword = $this->request->getVar('q');
        $categoryId = $this->request->getVar('category_id');

        if (strlen($keyword) < 2) {
            return $this->response->setJSON([]);
        }

        $products = $this->productModel->searchProducts($keyword, $categoryId);

        return $this->response->setJSON($products);
    }
}
