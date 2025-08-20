<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = \Config\Services::session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            return redirect()->to('/auth/login');
        }

        // Check role-based access if specified
        if ($arguments && count($arguments) > 0) {
            $requiredRole = $arguments[0];
            $userRole = $session->get('role');

            if ($userRole !== $requiredRole && $userRole !== 'admin') {
                session()->setFlashdata('error', 'Anda tidak memiliki akses ke halaman ini!');
                return redirect()->to('/dashboard');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
