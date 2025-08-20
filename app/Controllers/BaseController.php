<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseController extends Controller
{
    protected $session;
    protected $helpers = ['form', 'url'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->session = \Config\Services::session();
        
        if (!session()->has('logged_in') && 
            !($this instanceof AuthController) &&
            !($this instanceof Home)) {
            return redirect()->to('/login');
        }
    }

    protected function isAdmin()
    {
        return session()->get('role') === 'admin';
    }

    protected function renderView($view, $data = [])
    {
        $data['session'] = $this->session;
        $data['isAdmin'] = $this->isAdmin();
        
        echo view('templates/header', $data);
        echo view($view, $data);
        echo view('templates/footer', $data);
    }
}