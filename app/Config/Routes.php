<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index');
$routes->get('/dashboard', 'Dashboard::index');

// Auth routes
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');

// Products routes
$routes->get('/products', 'Products::index');
$routes->get('/products/create', 'Products::create');
$routes->post('/products/store', 'Products::store');
$routes->get('/products/edit/(:num)', 'Products::edit/$1');
$routes->post('/products/update/(:num)', 'Products::update/$1');
$routes->get('/products/delete/(:num)', 'Products::delete/$1');

// Categories routes
$routes->get('/categories', 'Categories::index');
$routes->post('/categories/store', 'Categories::store');
$routes->post('/categories/update/(:num)', 'Categories::update/$1');
$routes->get('/categories/delete/(:num)', 'Categories::delete/$1');

// Purchases routes
$routes->get('/purchases', 'Purchases::index');
$routes->get('/purchases/create', 'Purchases::create');
$routes->post('/purchases/store', 'Purchases::store');
$routes->get('/purchases/view/(:num)', 'Purchases::view/$1');

// Incoming items routes
$routes->get('/incoming', 'Incoming::index');
$routes->get('/incoming/create', 'Incoming::create');
$routes->post('/incoming/store', 'Incoming::store');
$routes->get('/incoming/get-purchase-items/(:num)', 'Incoming::getPurchaseItems/$1');

// Outgoing items routes
$routes->get('/outgoing', 'Outgoing::index');
$routes->get('/outgoing/create', 'Outgoing::create');
$routes->post('/outgoing/store', 'Outgoing::store');

// Reports routes
$routes->get('/reports/incoming', 'Reports::incoming');
$routes->get('/reports/outgoing', 'Reports::outgoing');
$routes->get('/reports/stock', 'Reports::stock');