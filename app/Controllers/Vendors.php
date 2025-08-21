<?php

namespace App\Controllers;

use App\Models\VendorModel;

class Vendors extends BaseController
{
    protected $vendorModel;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
    }

    public function index()
    {
        $perPage = 20;
        $currentPage = $this->request->getGet('page') ?? 1;
        $search = $this->request->getGet('search');

        $offset = ($currentPage - 1) * $perPage;
        $vendors = $this->vendorModel->getVendorsWithPurchaseCount($perPage, $offset, $search);
        $totalItems = $this->vendorModel->countVendorsWithPurchaseCount($search);

        $pager = \Config\Services::pager();
        $pager->setPath('vendors');

        $data = [
            'title' => 'Vendor - Vadhana Warehouse',
            'vendors' => $vendors,
            'pager' => $pager->makeLinks($currentPage, $perPage, $totalItems),
            'search' => $search,
            'total_items' => $totalItems,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'statistics' => $this->vendorModel->getVendorStatistics()
        ];

        return view('vendors/index', $data); // ✅ CUKUP INI SAJA
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Vendor Baru - Warehouse Management System',
            'validation' => session()->getFlashdata('validation')
        ];

        return view('vendors/create', $data); // ✅ CUKUP INI SAJA
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[200]',
            'address' => 'permit_empty|max_length[500]',
            'phone' => 'permit_empty|max_length[20]',
            'email' => 'permit_empty|valid_email|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $data = [
            'name' => trim($this->request->getPost('name')),
            'address' => trim($this->request->getPost('address')),
            'phone' => trim($this->request->getPost('phone')),
            'email' => trim($this->request->getPost('email'))
        ];

        // Check if vendor name already exists
        $existingVendor = $this->vendorModel->where('name', $data['name'])->first();
        if ($existingVendor) {
            session()->setFlashdata('error', 'Vendor dengan nama tersebut sudah ada!');
            return redirect()->back()->withInput();
        }

        try {
            $vendorId = $this->vendorModel->insert($data);

            if ($vendorId) {
                session()->setFlashdata('success', 'Vendor berhasil ditambahkan!');
                return redirect()->to('/vendors');
            } else {
                session()->setFlashdata('error', 'Gagal menambahkan vendor!');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        $vendor = $this->vendorModel->find($id);

        if (!$vendor) {
            session()->setFlashdata('error', 'Vendor tidak ditemukan!');
            return redirect()->to('/vendors');
        }

        // Get vendor with purchase count for additional info
        $vendorWithPurchases = $this->vendorModel->getVendorWithPurchases($id);

        $data = [
            'title' => 'Edit Vendor - Warehouse Management System',
            'vendor' => $vendorWithPurchases ?? $vendor,
            'validation' => session()->getFlashdata('validation')
        ];

        return view('vendors/edit', $data); // ✅ CUKUP INI SAJA
    }

    public function update($id)
    {
        $vendor = $this->vendorModel->find($id);

        if (!$vendor) {
            session()->setFlashdata('error', 'Vendor tidak ditemukan!');
            return redirect()->to('/vendors');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[200]',
            'address' => 'permit_empty|max_length[500]',
            'phone' => 'permit_empty|max_length[20]',
            'email' => 'permit_empty|valid_email|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $data = [
            'name' => trim($this->request->getPost('name')),
            'address' => trim($this->request->getPost('address')),
            'phone' => trim($this->request->getPost('phone')),
            'email' => trim($this->request->getPost('email'))
        ];

        // Check if vendor name already exists (excluding current vendor)
        $existingVendor = $this->vendorModel->where('name', $data['name'])
            ->where('id !=', $id)
            ->first();
        if ($existingVendor) {
            session()->setFlashdata('error', 'Vendor dengan nama tersebut sudah ada!');
            return redirect()->back()->withInput();
        }

        try {
            $result = $this->vendorModel->update($id, $data);

            if ($result) {
                session()->setFlashdata('success', 'Vendor berhasil diperbarui!');
                return redirect()->to('/vendors');
            } else {
                session()->setFlashdata('error', 'Gagal memperbarui vendor!');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function delete($id)
    {
        $vendor = $this->vendorModel->find($id);

        if (!$vendor) {
            session()->setFlashdata('error', 'Vendor tidak ditemukan!');
            return redirect()->to('/vendors');
        }

        try {
            // Check if vendor has purchases
            if ($this->vendorModel->checkPurchaseExists($id)) {
                session()->setFlashdata('error', 'Tidak dapat menghapus vendor yang masih memiliki transaksi pembelian!');
                return redirect()->to('/vendors');
            }

            $result = $this->vendorModel->delete($id);

            if ($result) {
                session()->setFlashdata('success', 'Vendor "' . $vendor['name'] . '" berhasil dihapus!');
            } else {
                session()->setFlashdata('error', 'Gagal menghapus vendor!');
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->to('/vendors');
    }

    public function view($id)
    {
        $vendor = $this->vendorModel->getVendorWithPurchases($id);

        if (!$vendor) {
            session()->setFlashdata('error', 'Vendor tidak ditemukan!');
            return redirect()->to('/vendors');
        }

        // Get recent purchases from this vendor
        $recentPurchases = $this->vendorModel->getPurchasesByVendor($id, 10);

        $data = [
            'title' => 'Detail Vendor - ' . $vendor['name'],
            'vendor' => $vendor,
            'recent_purchases' => $recentPurchases
        ];

        return view('vendors/view', $data); // ✅ CUKUP INI SAJA
    }

    public function search()
    {
        $keyword = $this->request->getGet('q');

        if (empty($keyword)) {
            return $this->response->setJSON([]);
        }

        $vendors = $this->vendorModel->searchVendors($keyword);

        return $this->response->setJSON($vendors);
    }

    public function getVendorsForSelect()
    {
        $vendors = $this->vendorModel->getVendorsForSelect();

        return $this->response->setJSON($vendors);
    }

    public function performanceReport()
    {
        $report = $this->vendorModel->getVendorPerformanceReport();

        $data = [
            'title' => 'Laporan Performa Vendor - Warehouse Management System',
            'vendors' => $report,
            'statistics' => $this->vendorModel->getVendorStatistics()
        ];

        return view('vendors/performance_report', $data); // ✅ CUKUP INI SAJA
    }

    public function exportReport()
    {
        $format = $this->request->getGet('format') ?? 'excel';
        $vendors = $this->vendorModel->getVendorPerformanceReport();

        if ($format === 'excel') {
            return $this->exportToExcel($vendors);
        } else if ($format === 'pdf') {
            return $this->exportToPDF($vendors);
        } else {
            return $this->exportToCSV($vendors);
        }
    }

    private function exportToCSV($vendors)
    {
        $filename = 'vendor_report_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, [
            'ID',
            'Nama Vendor',
            'Alamat',
            'Telepon',
            'Email',
            'Total Pembelian',
            'Total Nilai',
            'Rata-rata Nilai',
            'Pembelian Terakhir',
            'Pending',
            'Selesai'
        ]);

        // CSV Data
        foreach ($vendors as $vendor) {
            fputcsv($output, [
                $vendor['id'],
                $vendor['name'],
                $vendor['address'],
                $vendor['phone'],
                $vendor['email'],
                $vendor['total_purchases'],
                number_format($vendor['total_amount'], 0, ',', '.'),
                number_format($vendor['avg_purchase_amount'], 0, ',', '.'),
                $vendor['last_purchase_date'] ? date('d/m/Y', strtotime($vendor['last_purchase_date'])) : '-',
                $vendor['pending_purchases'],
                $vendor['completed_purchases']
            ]);
        }

        fclose($output);
        exit;
    }

    private function exportToExcel($vendors)
    {
        // For now, we'll use CSV format as Excel alternative
        // You can integrate with PhpSpreadsheet library for proper Excel format
        return $this->exportToCSV($vendors);
    }

    private function exportToPDF($vendors)
    {
        // For now, we'll redirect to CSV
        // You can integrate with TCPDF or similar library for PDF generation
        return $this->exportToCSV($vendors);
    }

    public function bulkAction()
    {
        $action = $this->request->getPost('action');
        $vendorIds = $this->request->getPost('vendor_ids');

        if (empty($vendorIds) || !is_array($vendorIds)) {
            session()->setFlashdata('error', 'Pilih vendor yang akan diproses!');
            return redirect()->back();
        }

        switch ($action) {
            case 'delete':
                return $this->bulkDelete($vendorIds);
            case 'export':
                return $this->bulkExport($vendorIds);
            default:
                session()->setFlashdata('error', 'Aksi tidak valid!');
                return redirect()->back();
        }
    }

    private function bulkDelete($vendorIds)
    {
        $deletedCount = 0;
        $errors = [];

        foreach ($vendorIds as $id) {
            try {
                $vendor = $this->vendorModel->find($id);
                if (!$vendor) {
                    continue;
                }

                if ($this->vendorModel->checkPurchaseExists($id)) {
                    $errors[] = "Vendor '{$vendor['name']}' memiliki transaksi dan tidak dapat dihapus";
                    continue;
                }

                if ($this->vendorModel->delete($id)) {
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error menghapus vendor ID {$id}: " . $e->getMessage();
            }
        }

        if ($deletedCount > 0) {
            session()->setFlashdata('success', "{$deletedCount} vendor berhasil dihapus!");
        }

        if (!empty($errors)) {
            session()->setFlashdata('error', implode('<br>', $errors));
        }

        return redirect()->back();
    }

    private function bulkExport($vendorIds)
    {
        $vendors = $this->vendorModel->whereIn('id', $vendorIds)->findAll();

        $filename = 'vendors_export_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['ID', 'Nama', 'Alamat', 'Telepon', 'Email', 'Dibuat', 'Diupdate']);

        foreach ($vendors as $vendor) {
            fputcsv($output, [
                $vendor['id'],
                $vendor['name'],
                $vendor['address'],
                $vendor['phone'],
                $vendor['email'],
                $vendor['created_at'],
                $vendor['updated_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
