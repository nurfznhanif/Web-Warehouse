<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Auth::login');

// Authentication routes
$routes->group('auth', function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::processLogin');
    $routes->get('logout', 'Auth::logout');
    $routes->get('register', 'Auth::register', ['filter' => 'auth:admin']);
    $routes->post('register', 'Auth::processRegister', ['filter' => 'auth:admin']);
    $routes->get('profile', 'Auth::profile', ['filter' => 'auth']);
    $routes->post('profile', 'Auth::updateProfile', ['filter' => 'auth']);
});

// Protected routes - require authentication
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/getTransactionChart', 'Dashboard::getTransactionChart');
    $routes->get('dashboard/getStockAlert', 'Dashboard::getStockAlert');

    // Categories management
    $routes->group('categories', function ($routes) {
        $routes->get('/', 'Categories::index');
        $routes->get('create', 'Categories::create');
        $routes->post('create', 'Categories::store');
        $routes->get('edit/(:num)', 'Categories::edit/$1');
        $routes->post('edit/(:num)', 'Categories::update/$1');
        $routes->get('delete/(:num)', 'Categories::delete/$1');
        $routes->post('delete/(:num)', 'Categories::delete/$1');
        $routes->get('view/(:num)', 'Categories::view/$1');
    });

    // Products management
    $routes->group('products', function ($routes) {
        $routes->get('/', 'Products::index');
        $routes->get('create', 'Products::create');
        $routes->post('create', 'Products::store');
        $routes->get('edit/(:num)', 'Products::edit/$1');
        $routes->post('edit/(:num)', 'Products::update/$1');
        $routes->get('delete/(:num)', 'Products::delete/$1');
        $routes->post('delete/(:num)', 'Products::delete/$1');
        $routes->get('view/(:num)', 'Products::view/$1');
        $routes->get('low-stock', 'Products::lowStock');
        $routes->post('check-stock', 'Products::checkStock');
        $routes->get('search', 'Products::search');
    });

    // Vendors management
    $routes->group('vendors', function ($routes) {
        $routes->get('/', 'Vendors::index');
        $routes->get('create', 'Vendors::create');
        $routes->post('create', 'Vendors::store');
        $routes->get('edit/(:num)', 'Vendors::edit/$1');
        $routes->post('edit/(:num)', 'Vendors::update/$1');
        $routes->get('delete/(:num)', 'Vendors::delete/$1');
        $routes->post('delete/(:num)', 'Vendors::delete/$1');
        $routes->get('view/(:num)', 'Vendors::view/$1');
        $routes->get('search', 'Vendors::search');
    });

    // Purchases management
    $routes->group('purchases', function ($routes) {
        $routes->get('/', 'Purchases::index');
        $routes->get('create', 'Purchases::create');
        $routes->post('create', 'Purchases::store');
        $routes->get('edit/(:num)', 'Purchases::edit/$1');
        $routes->post('edit/(:num)', 'Purchases::update/$1');
        $routes->get('delete/(:num)', 'Purchases::delete/$1');
        $routes->post('delete/(:num)', 'Purchases::delete/$1');
        $routes->get('view/(:num)', 'Purchases::view/$1');
        $routes->post('add-item', 'Purchases::addItem');
        $routes->post('remove-item', 'Purchases::removeItem');
        $routes->post('update-status/(:num)', 'Purchases::updateStatus/$1');
    });

    // Incoming items management
    $routes->group('incoming-items', function ($routes) {
        $routes->get('/', 'IncomingItems::index');
        $routes->get('create', 'IncomingItems::create');
        $routes->post('create', 'IncomingItems::store');
        $routes->get('edit/(:num)', 'IncomingItems::edit/$1');
        $routes->post('edit/(:num)', 'IncomingItems::update/$1');
        $routes->get('delete/(:num)', 'IncomingItems::delete/$1');
        $routes->post('delete/(:num)', 'IncomingItems::delete/$1');
        $routes->get('view/(:num)', 'IncomingItems::view/$1');
        $routes->get('get-purchase-items/(:num)', 'IncomingItems::getPurchaseItems/$1');
        $routes->post('bulk-receive', 'IncomingItems::bulkReceive');
    });

    // Outgoing items management
    $routes->group('outgoing-items', function ($routes) {
        $routes->get('/', 'OutgoingItems::index');
        $routes->get('create', 'OutgoingItems::create');
        $routes->post('create', 'OutgoingItems::store');
        $routes->get('edit/(:num)', 'OutgoingItems::edit/$1');
        $routes->post('edit/(:num)', 'OutgoingItems::update/$1');
        $routes->get('delete/(:num)', 'OutgoingItems::delete/$1');
        $routes->post('delete/(:num)', 'OutgoingItems::delete/$1');
        $routes->get('view/(:num)', 'OutgoingItems::view/$1');
    });

    // Reports
    $routes->group('reports', function ($routes) {
        $routes->get('/', 'Reports::index');
        $routes->get('incoming', 'Reports::incoming');
        $routes->post('incoming', 'Reports::incoming');
        $routes->get('outgoing', 'Reports::outgoing');
        $routes->post('outgoing', 'Reports::outgoing');
        $routes->get('stock', 'Reports::stock');
        $routes->post('stock', 'Reports::stock');
        $routes->get('export/incoming', 'Reports::exportIncoming');
        $routes->get('export/outgoing', 'Reports::exportOutgoing');
        $routes->get('export/stock', 'Reports::exportStock');
        $routes->get('summary', 'Reports::summary');
    });

    // API endpoints for AJAX requests
    $routes->group('api', function ($routes) {
        $routes->get('products/search', 'Products::search');
        $routes->get('products/by-category/(:num)', 'Products::getByCategory/$1');
        $routes->post('products/check-stock', 'Products::checkStock');
        $routes->get('vendors/search', 'Vendors::search');
        $routes->get('purchases/items/(:num)', 'Purchases::getItems/$1');
        $routes->get('categories/products/(:num)', 'Categories::getProducts/$1');
    });
});

// Admin only routes
$routes->group('admin', ['filter' => 'auth:admin'], function ($routes) {
    $routes->get('users', 'Admin::users');
    $routes->get('users/create', 'Admin::createUser');
    $routes->post('users/create', 'Admin::storeUser');
    $routes->get('users/edit/(:num)', 'Admin::editUser/$1');
    $routes->post('users/edit/(:num)', 'Admin::updateUser/$1');
    $routes->get('users/delete/(:num)', 'Admin::deleteUser/$1');
    $routes->post('users/delete/(:num)', 'Admin::deleteUser/$1');
    $routes->get('system-settings', 'Admin::systemSettings');
    $routes->post('system-settings', 'Admin::updateSystemSettings');
    $routes->get('backup', 'Admin::backup');
    $routes->post('backup/create', 'Admin::createBackup');
    $routes->get('logs', 'Admin::logs');
});

// Error routes
$routes->set404Override(function () {
    return view('errors/html/error_404');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
