<?php

namespace App\Controllers;

use App\Models\CategoryModel;

class Categories extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Kategori',
            'categories' => $this->categoryModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('categories/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Kategori'
        ];

        return view('categories/create', $data);
    }

    public function store()
    {
        // Check if request is AJAX
        $isAjax = $this->request->isAJAX();

        // Get POST data
        $name = $this->request->getPost('name');

        // Basic validation
        if (empty($name)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama kategori harus diisi'
                ]);
            }
            session()->setFlashdata('error', 'Nama kategori harus diisi');
            return redirect()->to('/categories/create');
        }

        $name = trim($name);

        if (strlen($name) < 2) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama kategori minimal 2 karakter'
                ]);
            }
            session()->setFlashdata('error', 'Nama kategori minimal 2 karakter');
            return redirect()->to('/categories/create');
        }

        if (strlen($name) > 100) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama kategori maksimal 100 karakter'
                ]);
            }
            session()->setFlashdata('error', 'Nama kategori maksimal 100 karakter');
            return redirect()->to('/categories/create');
        }

        // Check if name already exists
        $existing = $this->categoryModel->where('name', $name)->first();
        if ($existing) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama kategori sudah ada'
                ]);
            }
            session()->setFlashdata('error', 'Nama kategori sudah ada');
            return redirect()->to('/categories/create');
        }

        // Prepare data
        $data = [
            'name' => $name
        ];

        try {
            // Save to database
            $insertId = $this->categoryModel->insert($data);

            if ($insertId) {
                // Success
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Kategori berhasil ditambahkan',
                        'id' => $insertId
                    ]);
                }
                session()->setFlashdata('success', 'Kategori berhasil ditambahkan');
                return redirect()->to('/categories');
            } else {
                // Failed
                $errors = $this->categoryModel->errors();
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Gagal menyimpan ke database';

                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => $errorMsg
                    ]);
                }
                session()->setFlashdata('error', $errorMsg);
                return redirect()->to('/categories/create');
            }
        } catch (\Exception $e) {
            // Exception occurred
            $errorMsg = 'Terjadi kesalahan: ' . $e->getMessage();

            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMsg
                ]);
            }
            session()->setFlashdata('error', $errorMsg);
            return redirect()->to('/categories/create');
        }
    }

    public function edit($id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            session()->setFlashdata('error', 'Kategori tidak ditemukan');
            return redirect()->to('/categories');
        }

        $data = [
            'title' => 'Edit Kategori',
            'category' => $category
        ];

        return view('categories/edit', $data);
    }

    public function update($id)
    {
        // Check if request is AJAX
        $isAjax = $this->request->isAJAX();

        $category = $this->categoryModel->find($id);

        if (!$category) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ]);
            }
            session()->setFlashdata('error', 'Kategori tidak ditemukan');
            return redirect()->to('/categories');
        }

        // Get POST data
        $name = $this->request->getPost('name');

        // Basic validation
        if (empty($name)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama kategori harus diisi'
                ]);
            }
            session()->setFlashdata('error', 'Nama kategori harus diisi');
            return redirect()->to('/categories/edit/' . $id);
        }

        $name = trim($name);

        if (strlen($name) < 2) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama kategori minimal 2 karakter'
                ]);
            }
            session()->setFlashdata('error', 'Nama kategori minimal 2 karakter');
            return redirect()->to('/categories/edit/' . $id);
        }

        if (strlen($name) > 100) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama kategori maksimal 100 karakter'
                ]);
            }
            session()->setFlashdata('error', 'Nama kategori maksimal 100 karakter');
            return redirect()->to('/categories/edit/' . $id);
        }

        // Check if name already exists (exclude current record)
        $existing = $this->categoryModel->where('name', $name)->where('id !=', $id)->first();
        if ($existing) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama kategori sudah ada'
                ]);
            }
            session()->setFlashdata('error', 'Nama kategori sudah ada');
            return redirect()->to('/categories/edit/' . $id);
        }

        // Prepare data
        $data = [
            'name' => $name
        ];

        try {
            // Update database
            $result = $this->categoryModel->update($id, $data);

            if ($result) {
                // Success
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Kategori berhasil diperbarui'
                    ]);
                }
                session()->setFlashdata('success', 'Kategori berhasil diperbarui');
                return redirect()->to('/categories');
            } else {
                // Failed
                $errors = $this->categoryModel->errors();
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Gagal memperbarui data';

                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => $errorMsg
                    ]);
                }
                session()->setFlashdata('error', $errorMsg);
                return redirect()->to('/categories/edit/' . $id);
            }
        } catch (\Exception $e) {
            // Exception occurred
            $errorMsg = 'Terjadi kesalahan: ' . $e->getMessage();

            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMsg
                ]);
            }
            session()->setFlashdata('error', $errorMsg);
            return redirect()->to('/categories/edit/' . $id);
        }
    }

    public function delete($id)
    {
        // Check if user is admin
        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'Hanya admin yang dapat menghapus kategori');
            return redirect()->to('/categories');
        }

        $category = $this->categoryModel->find($id);

        if (!$category) {
            session()->setFlashdata('error', 'Kategori tidak ditemukan');
            return redirect()->to('/categories');
        }

        // Check if category is being used by products
        try {
            $productModel = new \App\Models\ProductModel();
            $productCount = $productModel->where('category_id', $id)->countAllResults();

            if ($productCount > 0) {
                session()->setFlashdata('error', "Tidak dapat menghapus kategori '{$category['name']}' karena masih digunakan oleh {$productCount} produk");
                return redirect()->to('/categories');
            }
        } catch (\Exception $e) {
            // Products table might not exist, continue with deletion
        }

        try {
            if ($this->categoryModel->delete($id)) {
                session()->setFlashdata('success', "Kategori '{$category['name']}' berhasil dihapus");
            } else {
                session()->setFlashdata('error', 'Gagal menghapus kategori');
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->to('/categories');
    }

    // API method for getting categories (for dropdown, etc.)
    public function getCategories()
    {
        $categories = $this->categoryModel->select('id, name')
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $categories
        ]);
    }
}
