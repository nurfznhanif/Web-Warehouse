<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Redirect root to dashboard
$routes->get('/', 'Dashboard::index', ['filter' => 'auth']);

// Auth routes
$routes->group('auth', function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::login');
    $routes->get('logout', 'Auth::logout');
    $routes->get('register', 'Auth::register', ['filter' => 'auth:admin']);
    $routes->post('register', 'Auth::register', ['filter' => 'auth:admin']);
    $routes->get('profile', 'Auth::profile', ['filter' => 'auth']);
    $routes->post('profile', 'Auth::profile', ['filter' => 'auth']);
});

// Dashboard
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('dashboard/getTransactionChart', 'Dashboard::getTransactionChart', ['filter' => 'auth']);
$routes->get('dashboard/getStockAlert', 'Dashboard::getStockAlert', ['filter' => 'auth']);

// Categories routes
$routes->group('categories', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Categories::index');
    $routes->get('create', 'Categories::create');
    $routes->post('store', 'Categories::store');
    $routes->get('edit/(:num)', 'Categories::edit/$1');
    $routes->post('update/(:num)', 'Categories::update/$1');
    $routes->get('delete/(:num)', 'Categories::delete/$1');
});

// Products routes
$routes->group('products', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Products::index');
    $routes->get('create', 'Products::create');
    $routes->post('store', 'Products::store');
    $routes->get('edit/(:num)', 'Products::edit/$1');
    $routes->post('update/(:num)', 'Products::update/$1');
    $routes->get('delete/(:num)', 'Products::delete/$1');
    $routes->get('low-stock', 'Products::lowStock');
    $routes->get('search', 'Products::search');
});

// Vendors routes
$routes->group('vendors', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Vendors::index');
    $routes->get('create', 'Vendors::create');
    $routes->post('store', 'Vendors::store');
    $routes->get('edit/(:num)', 'Vendors::edit/$1');
    $routes->post('update/(:num)', 'Vendors::update/$1');
    $routes->get('delete/(:num)', 'Vendors::delete/$1');
    $routes->get('detail/(:num)', 'Vendors::detail/$1');
});

// Purchases routes
$routes->group('purchases', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Purchases::index');
    $routes->get('create', 'Purchases::create');
    $routes->post('store', 'Purchases::store');
    $routes->get('detail/(:num)', 'Purchases::detail/$1');
    $routes->get('edit/(:num)', 'Purchases::edit/$1');
    $routes->post('update/(:num)', 'Purchases::update/$1');
    $routes->get('delete/(:num)', 'Purchases::delete/$1');
    $routes->post('add-item', 'Purchases::addItem');
    $routes->post('update-item/(:num)', 'Purchases::updateItem/$1');
    $routes->get('delete-item/(:num)', 'Purchases::deleteItem/$1');
    $routes->get('get-product-price/(:num)', 'Purchases::getProductPrice/$1');
});

// Incoming Items routes
$routes->group('incoming-items', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'IncomingItems::index');
    $routes->get('create', 'IncomingItems::create');
    $routes->post('store', 'IncomingItems::store');
    $routes->get('edit/(:num)', 'IncomingItems::edit/$1');
    $routes->post('update/(:num)', 'IncomingItems::update/$1');
    $routes->get('delete/(:num)', 'IncomingItems::delete/$1');
    $routes->get('get-purchase-items/(:num)', 'IncomingItems::getPurchaseItems/$1');
});

// Outgoing Items routes
$routes->group('outgoing-items', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'OutgoingItems::index');
    $routes->get('create', 'OutgoingItems::create');
    $routes->post('store', 'OutgoingItems::store');
    $routes->get('edit/(:num)', 'OutgoingItems::edit/$1');
    $routes->post('update/(:num)', 'OutgoingItems::update/$1');
    $routes->get('delete/(:num)', 'OutgoingItems::delete/$1');
});

// Reports routes
$routes->group('reports', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Reports::index');
    $routes->get('incoming', 'Reports::incoming');
    $routes->get('outgoing', 'Reports::outgoing');
    $routes->get('stock', 'Reports::stock');
    $routes->get('export-incoming', 'Reports::exportIncoming');
    $routes->get('export-outgoing', 'Reports::exportOutgoing');
    $routes->get('export-stock', 'Reports::exportStock');
});

// API routes for AJAX calls
$routes->group('api', ['filter' => 'auth'], function ($routes) {
    $routes->get('products/search', 'Api::searchProducts');
    $routes->get('vendors/search', 'Api::searchVendors');
    $routes->get('categories/list', 'Api::getCategories');
});

// Backward compatibility routes (redirects)
$routes->addRedirect('login', 'auth/login');
$routes->addRedirect('logout', 'auth/logout');
$routes->addRedirect('incoming', 'incoming-items');
$routes->addRedirect('outgoing', 'outgoing-items');
