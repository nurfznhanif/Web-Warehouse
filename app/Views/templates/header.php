<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Gudang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="<?= base_url('/dashboard') ?>" class="text-xl font-bold">Sistem Manajemen Gudang</a>
            <div class="flex items-center space-x-4">
                <span>Halo, <?= session()->get('name') ?></span>
                <a href="<?= base_url('/logout') ?>" class="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white min-h-screen">
            <ul class="p-4 space-y-2">
                <li>
                    <a href="<?= base_url('/dashboard') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Dashboard</a>
                </li>
                <li>
                    <a href="<?= base_url('/products') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Data Barang</a>
                </li>
                <li>
                    <a href="<?= base_url('/categories') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Kategori Barang</a>
                </li>
                <li>
                    <a href="<?= base_url('/purchases') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Pembelian</a>
                </li>
                <li>
                    <a href="<?= base_url('/incoming') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Barang Masuk</a>
                </li>
                <li>
                    <a href="<?= base_url('/outgoing') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Barang Keluar</a>
                </li>
                <li class="pt-4 border-t border-gray-700">
                    <span class="block py-2 px-4 text-gray-400">Laporan</span>
                    <ul class="pl-4 space-y-2">
                        <li>
                            <a href="<?= base_url('/reports/incoming') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Barang Masuk</a>
                        </li>
                        <li>
                            <a href="<?= base_url('/reports/outgoing') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Barang Keluar</a>
                        </li>
                        <li>
                            <a href="<?= base_url('/reports/stock') ?>" class="block py-2 px-4 rounded hover:bg-gray-700">Stok Barang</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>