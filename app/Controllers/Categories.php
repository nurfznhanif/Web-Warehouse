<?php

namespace App\Controllers;

use App\Models\CategoryModel;

class Categories extends BaseController
{
    public function index()
    {
        if (!$this->isAdmin()) {
            session()->setFlashdata('error', 'Hanya admin yang dapat mengelola kategori');
            return redirect()->to('/dashboard');
        }
        
        $categoryModel = new CategoryModel();
        $data['categories'] = $categoryModel->findAll();
        
        return $this->renderView('categories/index', $data);
    }
    
    public function store()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $categoryModel = new CategoryModel();
        
        $data = [
            'name' => $this->request->getPost('name')
        ];
        
        if ($categoryModel->save($data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Kategori berhasil ditambahkan']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menambahkan kategori']);
        }
    }
    
    public function update($id)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $categoryModel = new CategoryModel();
        
        $data = [
            'name' => $this->request->getPost('name')
        ];
        
        if ($categoryModel->update($id, $data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Kategori berhasil diperbarui']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal memperbarui kategori']);
        }
    }
    
    public function delete($id)
    {
        if (!$this->isAdmin()) {
            session()->setFlashdata('error', 'Hanya admin yang dapat menghapus kategori');
            return redirect()->to('/categories');
        }
        
        $categoryModel = new CategoryModel();
        $categoryModel->delete($id);
        
        session()->setFlashdata('success', 'Kategori berhasil dihapus');
        return redirect()->to('/categories');
    }
}