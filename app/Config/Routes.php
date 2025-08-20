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

// Debug routes (remove in production)
$routes->get('/categories/test-db', 'Categories::testDb');
$routes->post('/categories/store-test', 'Categories::storeTest');
$routes->get('/categories/test-redirect', 'Categories::testRedirect');

// Products routes
$routes->get('/products', 'Products::index');
$routes->get('/products/create', 'Products::create');
$routes->post('/products/store', 'Products::store');
$routes->get('/products/edit/(:num)', 'Products::edit/$1');
$routes->post('/products/update/(:num)', 'Products::update/$1');
$routes->get('/products/delete/(:num)', 'Products::delete/$1');

// Vendors routes
$routes->get('/vendors', 'Vendors::index');
$routes->get('/vendors/create', 'Vendors::create');
$routes->post('/vendors/store', 'Vendors::store');
$routes->get('/vendors/edit/(:num)', 'Vendors::edit/$1');
$routes->post('/vendors/update/(:num)', 'Vendors::update/$1');
$routes->get('/vendors/delete/(:num)', 'Vendors::delete/$1');

// Purchases routes
$routes->get('/purchases', 'Purchases::index');
$routes->get('/purchases/create', 'Purchases::create');
$routes->post('/purchases/store', 'Purchases::store');
$routes->get('/purchases/view/(:num)', 'Purchases::view/$1');

// Incoming items routes
$routes->get('/incoming-items', 'IncomingItems::index');
$routes->get('/incoming-items/create', 'IncomingItems::create');
$routes->post('/incoming-items/store', 'IncomingItems::store');
$routes->get('/incoming-items/get-purchase-items/(:num)', 'IncomingItems::getPurchaseItems/$1');

// Legacy incoming routes
$routes->get('/incoming', 'IncomingItems::index');
$routes->get('/incoming/create', 'IncomingItems::create');
$routes->post('/incoming/store', 'IncomingItems::store');
$routes->get('/incoming/get-purchase-items/(:num)', 'IncomingItems::getPurchaseItems/$1');

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