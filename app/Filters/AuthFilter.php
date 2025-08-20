<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Jika belum login dan bukan halaman auth, redirect ke login
        if (!session()->has('logged_in')) {
            $uri = service('uri');
            $currentController = $uri->getSegment(1);
            
            $authControllers = ['login', 'auth'];
            
            if (!in_array($currentController, $authControllers)) {
                return redirect()->to('/login');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}