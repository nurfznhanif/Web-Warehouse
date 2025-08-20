<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;

class Products extends BaseController
{
    public function index()
    {
        $productModel = new ProductModel();
        $data['products'] = $productModel->getProductsWithCategory();
        
        return $this->renderView('products/index', $data);
    }
    
    public function create()
    {
        $categoryModel = new CategoryModel();
        $data['categories'] = $categoryModel->findAll();
        
        return $this->renderView('products/create', $data);
    }
    
    public function store()
    {
        $productModel = new ProductModel();
        
        $data = [
            'category_id' => $this->request->getPost('category_id'),
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'unit' => $this->request->getPost('unit'),
            'stock' => $this->request->getPost('stock') ?? 0
        ];
        
        if ($productModel->save($data)) {
            session()->setFlashdata('success', 'Produk berhasil ditambahkan');
            return redirect()->to('/products');
        } else {
            session()->setFlashdata('errors', $productModel->errors());
            return redirect()->back()->withInput();
        }
    }
    
    public function edit($id)
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();
        
        $data['product'] = $productModel->find($id);
        $data['categories'] = $categoryModel->findAll();
        
        return $this->renderView('products/edit', $data);
    }
    
    public function update($id)
    {
        $productModel = new ProductModel();
        
        $data = [
            'category_id' => $this->request->getPost('category_id'),
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'unit' => $this->request->getPost('unit'),
            'stock' => $this->request->getPost('stock')
        ];
        
        if ($productModel->update($id, $data)) {
            session()->setFlashdata('success', 'Produk berhasil diperbarui');
            return redirect()->to('/products');
        } else {
            session()->setFlashdata('errors', $productModel->errors());
            return redirect()->back()->withInput();
        }
    }
    
    public function delete($id)
    {
        if (!$this->isAdmin()) {
            session()->setFlashdata('error', 'Hanya admin yang dapat menghapus produk');
            return redirect()->to('/products');
        }
        
        $productModel = new ProductModel();
        $productModel->delete($id);
        
        session()->setFlashdata('success', 'Produk berhasil dihapus');
        return redirect()->to('/products');
    }
}