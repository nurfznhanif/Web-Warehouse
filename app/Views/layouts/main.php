<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Warehouse Management System' ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification {
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-blue-800 text-white w-64 sidebar-transition">
            <div class="p-4">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-warehouse text-2xl"></i>
                    <h1 class="text-xl font-bold">Warehouse MS</h1>
                </div>

                <!-- User Info -->
                <div class="bg-blue-700 rounded-lg p-3 mb-6">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-circle text-xl"></i>
                        <div>
                            <p class="font-medium"><?= session()->get('full_name') ?></p>
                            <p class="text-sm text-blue-200"><?= ucfirst(session()->get('role')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="space-y-2">
                    <a href="<?= base_url('/dashboard') ?>"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 transition-colors <?= (uri_string() == 'dashboard') ? 'bg-blue-700' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="<?= base_url('/categories') ?>"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 transition-colors <?= (strpos(uri_string(), 'categories') !== false) ? 'bg-blue-700' : '' ?>">
                        <i class="fas fa-tags"></i>
                        <span>Kategori</span>
                    </a>

                    <a href="<?= base_url('/products') ?>"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 transition-colors <?= (strpos(uri_string(), 'products') !== false) ? 'bg-blue-700' : '' ?>">
                        <i class="fas fa-box"></i>
                        <span>Produk</span>
                    </a>

                    <a href="<?= base_url('/vendors') ?>"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 transition-colors <?= (strpos(uri_string(), 'vendors') !== false) ? 'bg-blue-700' : '' ?>">
                        <i class="fas fa-truck"></i>
                        <span>Vendor</span>
                    </a>

                    <a href="<?= base_url('/purchases') ?>"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 transition-colors <?= (strpos(uri_string(), 'purchases') !== false) ? 'bg-blue-700' : '' ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pembelian</span>
                    </a>

                    <div class="border-t border-blue-700 my-4"></div>

                    <a href="<?= base_url('/incoming-items') ?>"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 transition-colors <?= (strpos(uri_string(), 'incoming-items') !== false) ? 'bg-blue-700' : '' ?>">
                        <i class="fas fa-arrow-down text-green-400"></i>
                        <span>Barang Masuk</span>
                    </a>

                    <a href="<?= base_url('/outgoing-items') ?>"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 transition-colors <?= (strpos(uri_string(), 'outgoing-items') !== false) ? 'bg-blue-700' : '' ?>">
                        <i class="fas fa-arrow-up text-red-400"></i>
                        <span>Barang Keluar</span>
                    </a>

                    <div class="border-t border-blue-700 my-4"></div>

                    <a href="<?= base_url('/reports') ?>"
                        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-700 transition-colors <?= (strpos(uri_string(), 'reports') !== false) ? 'bg-blue-700' : '' ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Laporan</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <button id="sidebar-toggle" class="md:hidden text-gray-600 hover:text-gray-900">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800"><?= $title ?? 'Dashboard' ?></h2>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="text-gray-600 hover:text-gray-900 relative">
                                <i class="fas fa-bell text-lg"></i>
                                <?php if (isset($low_stock_count) && $low_stock_count > 0): ?>
                                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        <?= $low_stock_count ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                        </div>

                        <!-- User Menu -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-600 hover:text-gray-900">
                                <i class="fas fa-user-circle text-lg"></i>
                                <span class="hidden md:block"><?= session()->get('full_name') ?></span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="py-1">
                                    <a href="<?= base_url('/auth/profile') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-2"></i>Profile
                                    </a>
                                    <?php if (session()->get('role') === 'admin'): ?>
                                        <a href="<?= base_url('/auth/register') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-user-plus mr-2"></i>Tambah User
                                        </a>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-100"></div>
                                    <a href="<?= base_url('/auth/logout') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="notification bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mx-6 mt-4" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?= session()->getFlashdata('success') ?></span>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="notification bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mx-6 mt-4" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span><?= session()->getFlashdata('error') ?></span>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="notification bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mx-6 mt-4" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span><?= session()->getFlashdata('warning') ?></span>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Main Content Area -->
            <main class="flex-1 p-6 fade-in">
                <?= $this->renderSection('content') ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <p>&copy; <?= date('Y') ?> Warehouse Management System. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });

        // Auto-hide flash messages
        setTimeout(function() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(function(notification) {
                notification.style.opacity = '0';
                setTimeout(function() {
                    notification.remove();
                }, 300);
            });
        }, 5000);

        // Confirm delete actions
        function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
            return confirm(message);
        }

        // Format number inputs
        function formatNumber(input) {
            let value = input.value.replace(/[^\d.]/g, '');
            input.value = value;
        }

        // Loading state for forms
        function showLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            button.disabled = true;

            setTimeout(function() {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 3000);
        }

        // Search functionality
        function initSearch() {
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    if (this.value.length >= 3 || this.value.length === 0) {
                        // Auto-submit form or make AJAX request
                        this.form.submit();
                    }
                });
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initSearch();
        });
    </script>

    <!-- Custom scripts section -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>