<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\IncomingItemModel;
use App\Models\OutgoingItemModel;

class Products extends BaseController
{
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $categoryFilter = $this->request->getGet('category_id');
        $stockStatus = $this->request->getGet('stock_status');
        $sort = $this->request->getGet('sort') ?? 'created_at';
        $order = $this->request->getGet('order') ?? 'desc';

        // Build query
        $builder = $this->productModel->builder();
        $builder->select('products.*, categories.name as category_name');
        $builder->join('categories', 'categories.id = products.category_id', 'left');

        // Apply filters
        if ($search) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.code', $search)
                ->groupEnd();
        }

        if ($categoryFilter) {
            $builder->where('products.category_id', $categoryFilter);
        }

        if ($stockStatus) {
            switch ($stockStatus) {
                case 'out_of_stock':
                    $builder->where('products.stock <=', 0);
                    break;
                case 'low_stock':
                    $builder->where('products.stock >', 0)
                        ->where('products.stock <=', 'products.min_stock', false);
                    break;
                case 'in_stock':
                    $builder->where('products.stock >', 'products.min_stock', false);
                    break;
            }
        }

        // Apply sorting
        $allowedSort = ['name', 'code', 'stock', 'created_at'];
        if (in_array($sort, $allowedSort)) {
            $builder->orderBy('products.' . $sort, $order);
        }

        $data = [
            'title' => 'Produk - Vadhana Warehouse',
            'products' => $builder->get()->getResultArray(),
            'categories' => $this->categoryModel->findAll(),
            'search' => $search,
            'category_filter' => $categoryFilter,
            'stock_status' => $stockStatus,
            'sort' => $sort,
            'order' => $order
        ];

        return view('products/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Produk - vadhana Warehouse',
            'categories' => $this->categoryModel->findAll()
        ];

        return view('products/create', $data);
    }

    public function store()
    {
        $rules = [
            'category_id' => 'required|integer|is_not_unique[categories.id]',
            'name' => 'required|min_length[3]|max_length[255]',
            'code' => 'required|min_length[3]|max_length[50]|is_unique[products.code]',
            'unit' => 'required|max_length[20]',
            'stock' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'min_stock' => 'permit_empty|decimal|greater_than_equal_to[0]'
        ];

        $messages = [
            'category_id' => [
                'required' => 'Kategori harus dipilih.',
                'integer' => 'Kategori tidak valid.',
                'is_not_unique' => 'Kategori tidak ditemukan.'
            ],
            'name' => [
                'required' => 'Nama produk harus diisi.',
                'min_length' => 'Nama produk minimal 3 karakter.',
                'max_length' => 'Nama produk maksimal 255 karakter.'
            ],
            'code' => [
                'required' => 'Kode produk harus diisi.',
                'min_length' => 'Kode produk minimal 3 karakter.',
                'max_length' => 'Kode produk maksimal 50 karakter.',
                'is_unique' => 'Kode produk sudah digunakan.'
            ],
            'unit' => [
                'required' => 'Satuan harus dipilih.',
                'max_length' => 'Satuan maksimal 20 karakter.'
            ],
            'stock' => [
                'decimal' => 'Stok harus berupa angka.',
                'greater_than_equal_to' => 'Stok tidak boleh negatif.'
            ],
            'min_stock' => [
                'decimal' => 'Minimum stok harus berupa angka.',
                'greater_than_equal_to' => 'Minimum stok tidak boleh negatif.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'category_id' => $this->request->getPost('category_id'),
            'name' => trim($this->request->getPost('name')),
            'code' => strtoupper(trim($this->request->getPost('code'))),
            'unit' => $this->request->getPost('unit'),
            'stock' => $this->request->getPost('stock') ?: 0,
            'min_stock' => $this->request->getPost('min_stock') ?: 10
        ];

        try {
            if ($this->productModel->save($data)) {
                session()->setFlashdata('success', 'Produk berhasil ditambahkan');
                return redirect()->to('/products');
            } else {
                $errors = $this->productModel->errors();
                if (!empty($errors)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('errors', $errors);
                }

                session()->setFlashdata('error', 'Gagal menambahkan produk');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving product: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat menyimpan produk');
            return redirect()->back()->withInput();
        }
    }

    public function view($id)
    {
        $product = $this->productModel->getProductWithDetails($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Detail Produk - ' . $product['name'],
            'product' => $product
        ];

        return view('products/view', $data);
    }

    public function edit($id)
    {
        $product = $this->productModel->getProductWithDetails($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Produk - ' . $product['name'],
            'product' => $product,
            'categories' => $this->categoryModel->findAll()
        ];

        return view('products/edit', $data);
    }

    public function update($id)
    {
        // Pastikan produk ada
        $product = $this->productModel->find($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Debug: log data yang diterima
        log_message('debug', 'Update product data received: ' . json_encode($this->request->getPost()));

        $rules = [
            'category_id' => 'required|integer|is_not_unique[categories.id]',
            'name' => 'required|min_length[3]|max_length[255]',
            'code' => 'required|min_length[3]|max_length[50]',
            'unit' => 'required|max_length[20]',
            'min_stock' => 'permit_empty|decimal|greater_than_equal_to[0]'
        ];

        $messages = [
            'category_id' => [
                'required' => 'Kategori harus dipilih.',
                'integer' => 'Kategori tidak valid.',
                'is_not_unique' => 'Kategori tidak ditemukan.'
            ],
            'name' => [
                'required' => 'Nama produk harus diisi.',
                'min_length' => 'Nama produk minimal 3 karakter.',
                'max_length' => 'Nama produk maksimal 255 karakter.'
            ],
            'code' => [
                'required' => 'Kode produk harus diisi.',
                'min_length' => 'Kode produk minimal 3 karakter.',
                'max_length' => 'Kode produk maksimal 50 karakter.'
            ],
            'unit' => [
                'required' => 'Satuan harus dipilih.',
                'max_length' => 'Satuan maksimal 20 karakter.'
            ],
            'min_stock' => [
                'decimal' => 'Minimum stok harus berupa angka.',
                'greater_than_equal_to' => 'Minimum stok tidak boleh negatif.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            log_message('debug', 'Validation errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'category_id' => (int)$this->request->getPost('category_id'),
            'name' => trim($this->request->getPost('name')),
            'code' => strtoupper(trim($this->request->getPost('code'))),
            'unit' => $this->request->getPost('unit'),
            'min_stock' => $this->request->getPost('min_stock') ? (float)$this->request->getPost('min_stock') : 10
        ];

        // Debug: log data yang akan diupdate
        log_message('debug', 'Data to update: ' . json_encode($data));

        try {
            if ($this->productModel->update($id, $data)) {
                log_message('info', 'Product updated successfully: ID ' . $id);
                session()->setFlashdata('success', 'Produk berhasil diperbarui');
                return redirect()->to('/products');
            } else {
                $errors = $this->productModel->errors();
                log_message('error', 'Model validation errors: ' . json_encode($errors));

                if (!empty($errors)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('errors', $errors);
                }

                session()->setFlashdata('error', 'Gagal memperbarui produk. Tidak ada perubahan yang terdeteksi.');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating product: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            session()->setFlashdata('error', 'Terjadi kesalahan saat memperbarui produk: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function delete($id)
    {
        // Only admin can delete
        if (!$this->isAdmin()) {
            session()->setFlashdata('error', 'Hanya admin yang dapat menghapus produk');
            return redirect()->to('/products');
        }

        $product = $this->productModel->find($id);

        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan');
            return redirect()->to('/products');
        }

        // Check if product has transactions
        if ($this->productModel->hasTransactions($id)) {
            session()->setFlashdata('error', 'Tidak dapat menghapus produk yang memiliki riwayat transaksi');
            return redirect()->to('/products');
        }

        try {
            if ($this->productModel->delete($id)) {
                session()->setFlashdata('success', 'Produk berhasil dihapus');
            } else {
                session()->setFlashdata('error', 'Gagal menghapus produk');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting product: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat menghapus produk');
        }

        return redirect()->to('/products');
    }

    public function lowStock()
    {
        $products = $this->productModel->getLowStockProducts();

        $data = [
            'title' => 'Produk Stok Rendah',
            'products' => $products
        ];

        return view('products/low_stock', $data);
    }

    protected function isAdmin()
    {
        return session()->get('role') === 'admin';
    }
}

// API Controller untuk AJAX requests
class ProductsApi extends BaseController
{
    protected $productModel;
    protected $incomingModel;
    protected $outgoingModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->incomingModel = new IncomingItemModel();
        $this->outgoingModel = new OutgoingItemModel();
    }

    public function getTransactions($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $type = $this->request->getGet('type') ?? 'all';

        $transactions = [];
        $statistics = [
            'total_incoming' => 0,
            'total_outgoing' => 0,
            'net_stock' => 0
        ];

        try {
            // Get incoming transactions
            if ($type === 'all' || $type === 'incoming') {
                $incoming = $this->incomingModel->getByProductId($id);
                foreach ($incoming as $item) {
                    $transactions[] = [
                        'type' => 'incoming',
                        'date' => $item['date'],
                        'quantity' => (float)$item['quantity'],
                        'unit' => $item['unit'],
                        'description' => $item['description'] ?? 'Barang masuk'
                    ];
                    $statistics['total_incoming'] += (float)$item['quantity'];
                }
            }

            // Get outgoing transactions
            if ($type === 'all' || $type === 'outgoing') {
                $outgoing = $this->outgoingModel->getByProductId($id);
                foreach ($outgoing as $item) {
                    $transactions[] = [
                        'type' => 'outgoing',
                        'date' => $item['date'],
                        'quantity' => (float)$item['quantity'],
                        'unit' => $item['unit'],
                        'description' => $item['description'] ?? 'Barang keluar'
                    ];
                    $statistics['total_outgoing'] += (float)$item['quantity'];
                }
            }

            // Sort by date (newest first)
            usort($transactions, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Limit to last 50 transactions
            $transactions = array_slice($transactions, 0, 50);

            $statistics['net_stock'] = $statistics['total_incoming'] - $statistics['total_outgoing'];

            return $this->response->setJSON([
                'success' => true,
                'transactions' => $transactions,
                'statistics' => $statistics
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting transactions: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal memuat data transaksi'
            ]);
        }
    }

    public function getStatistics($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        try {
            $statistics = $this->productModel->getProductStatistics($id);

            return $this->response->setJSON([
                'success' => true,
                'statistics' => $statistics
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal memuat statistik'
            ]);
        }
    }

    public function checkCode()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $code = $this->request->getPost('code');
        $excludeId = $this->request->getPost('exclude_id');

        if (!$code) {
            return $this->response->setJSON([
                'exists' => false,
                'message' => 'Kode tidak boleh kosong'
            ]);
        }

        try {
            $builder = $this->productModel->builder();
            $builder->where('code', strtoupper(trim($code)));

            if ($excludeId) {
                $builder->where('id !=', $excludeId);
            }

            $exists = $builder->countAllResults() > 0;

            return $this->response->setJSON([
                'exists' => $exists,
                'message' => $exists ? 'Kode sudah digunakan' : 'Kode tersedia'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error checking code: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'exists' => true,
                'message' => 'Error checking code'
            ]);
        }
    }
}
