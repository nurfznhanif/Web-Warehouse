<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home & Dashboard
$routes->get('/', 'Dashboard::index');
$routes->get('/dashboard', 'Dashboard::index');

// Auth routes
$routes->get('/auth/login', 'Auth::login');
$routes->post('/auth/login', 'Auth::processLogin');
$routes->get('/auth/register', 'Auth::register');
$routes->post('/auth/register', 'Auth::processRegister');
$routes->get('/auth/logout', 'Auth::logout');
$routes->get('/auth/profile', 'Auth::profile');
$routes->post('/auth/profile', 'Auth::updateProfile');

// Legacy auth routes
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::processLogin');
$routes->get('/logout', 'Auth::logout');

// Categories routes - Simplified
$routes->get('/categories', 'Categories::index');
$routes->get('/categories/create', 'Categories::create');
$routes->post('/categories/store', 'Categories::store');
$routes->get('/categories/edit/(:num)', 'Categories::edit/$1');
$routes->post('/categories/update/(:num)', 'Categories::update/$1');
$routes->get('/categories/delete/(:num)', 'Categories::delete/$1');

// Products routes - dengan filter auth
$routes->group('products', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Products::index');
    $routes->get('index', 'Products::index');
    $routes->get('create', 'Products::create');
    $routes->post('store', 'Products::store');
    $routes->get('view/(:num)', 'Products::view/$1');
    $routes->get('edit/(:num)', 'Products::edit/$1');
    $routes->post('update/(:num)', 'Products::update/$1');
    $routes->get('delete/(:num)', 'Products::delete/$1', ['filter' => 'auth:admin']);
    $routes->get('low-stock', 'Products::lowStock');
});

// API routes untuk validasi
$routes->group('api', function ($routes) {
    $routes->post('products/check-code', 'Products::checkCode');
    $routes->post('categories/store', 'Categories::store');
});

// Vendors routes
$routes->get('/vendors', 'Vendors::index');
$routes->get('/vendors/create', 'Vendors::create');
$routes->post('/vendors/store', 'Vendors::store');
$routes->get('/vendors/edit/(:num)', 'Vendors::edit/$1');
$routes->post('/vendors/update/(:num)', 'Vendors::update/$1');
$routes->get('/vendors/delete/(:num)', 'Vendors::delete/$1');

// Purchases routes

// Purchases routes - Lengkap
$routes->group('purchases', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Purchases::index');
    $routes->get('index', 'Purchases::index');
    $routes->get('create', 'Purchases::create');
    $routes->post('store', 'Purchases::store');
    $routes->get('view/(:num)', 'Purchases::view/$1');
    $routes->get('edit/(:num)', 'Purchases::edit/$1');
    $routes->post('update/(:num)', 'Purchases::update/$1');
    $routes->get('delete/(:num)', 'Purchases::delete/$1');
    $routes->get('update-status/(:num)/(:alpha)', 'Purchases::updateStatus/$1/$2');
    $routes->get('duplicate/(:num)', 'Purchases::duplicate/$1');
});

// Incoming items routes (complete)
$routes->group('incoming-items', ['filter' => 'auth'], function($routes) {
    // Main CRUD routes
    $routes->get('/', 'IncomingItems::index');
    $routes->get('index', 'IncomingItems::index');
    $routes->get('create', 'IncomingItems::create');
    $routes->post('store', 'IncomingItems::store');
    $routes->get('edit/(:num)', 'IncomingItems::edit/$1');
    $routes->post('update/(:num)', 'IncomingItems::update/$1');
    $routes->get('delete/(:num)', 'IncomingItems::delete/$1', ['filter' => 'auth:admin']);
    
    // AJAX endpoints
    $routes->get('get-purchase-items/(:num)', 'IncomingItems::getPurchaseItems/$1');
    $routes->get('get-product-info/(:num)', 'IncomingItems::getProductInfo/$1');
    $routes->post('validate-quantity', 'IncomingItems::validateQuantity');
    $routes->post('bulk-receive', 'IncomingItems::bulkReceive');
    $routes->get('get-summary', 'IncomingItems::getSummary');
    
    // Special features
    $routes->get('receive-from-purchase/(:num)', 'IncomingItems::receiveFromPurchase/$1');
    $routes->get('history/(:num)', 'IncomingItems::history/$1');
    $routes->get('print-receipt/(:num)', 'IncomingItems::printReceipt/$1');
    $routes->get('export', 'IncomingItems::export');
});

// Legacy routes untuk backward compatibility
$routes->group('incoming', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'IncomingItems::index');
    $routes->get('create', 'IncomingItems::create');
    $routes->post('store', 'IncomingItems::store');
    $routes->get('get-purchase-items/(:num)', 'IncomingItems::getPurchaseItems/$1');
});

// Quick access routes (tanpa prefix)
$routes->get('/receive-purchase/(:num)', 'IncomingItems::receiveFromPurchase/$1', ['filter' => 'auth']);
$routes->get('/incoming-history/(:num)', 'IncomingItems::history/$1', ['filter' => 'auth']);


// Outgoing items routes
$routes->get('/outgoing-items', 'OutgoingItems::index');
$routes->get('/outgoing-items/create', 'OutgoingItems::create');
$routes->post('/outgoing-items/store', 'OutgoingItems::store');

// Legacy outgoing routes
$routes->get('/outgoing', 'OutgoingItems::index');
$routes->get('/outgoing/create', 'OutgoingItems::create');
$routes->post('/outgoing/store', 'OutgoingItems::store');

// Reports routes
$routes->get('/reports/incoming', 'Reports::incoming');
$routes->get('/reports/outgoing', 'Reports::outgoing');
$routes->get('/reports/stock', 'Reports::stock');
