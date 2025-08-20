<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends Controller
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
    }

    public function login()
    {
        // Check if user is already logged in
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Login - Warehouse Management System'
        ];

        if ($this->request->getMethod() === 'POST') {
            return $this->processLogin();
        }

        return view('auth/login', $data);
    }

    public function processLogin()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return view('auth/login', [
                'title' => 'Login - Warehouse Management System',
                'validation' => $this->validator,
                'old' => $this->request->getPost()
            ]);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Find user by username or email
        $user = $this->userModel->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user || !password_verify($password, $user['password'])) {
            session()->setFlashdata('error', 'Username atau password salah!');
            return view('auth/login', [
                'title' => 'Login - Warehouse Management System',
                'old' => $this->request->getPost()
            ]);
        }

        // Set session data
        $sessionData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
            'logged_in' => true
        ];

        $this->session->set($sessionData);
        session()->setFlashdata('success', 'Selamat datang, ' . $user['full_name'] . '!');

        return redirect()->to('/dashboard');
    }

    public function register()
    {
        // Only allow admin to register new users
        if (!$this->session->get('logged_in') || $this->session->get('role') !== 'admin') {
            return redirect()->to('/auth/login')->with('error', 'Access denied!');
        }

        $data = [
            'title' => 'Register User - Warehouse Management System'
        ];

        if ($this->request->getMethod() === 'POST') {
            return $this->processRegister();
        }

        return view('auth/register', $data);
    }

    public function processRegister()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'full_name' => 'required|min_length[3]|max_length[100]',
            'role' => 'required|in_list[admin,operator]'
        ];

        if (!$this->validate($rules)) {
            return view('auth/register', [
                'title' => 'Register User - Warehouse Management System',
                'validation' => $this->validator,
                'old' => $this->request->getPost()
            ]);
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'full_name' => $this->request->getPost('full_name'),
            'role' => $this->request->getPost('role')
        ];

        if ($this->userModel->save($userData)) {
            session()->setFlashdata('success', 'User berhasil didaftarkan!');
            return redirect()->to('/dashboard');
        } else {
            session()->setFlashdata('error', 'Gagal mendaftarkan user!');
            return view('auth/register', [
                'title' => 'Register User - Warehouse Management System',
                'old' => $this->request->getPost()
            ]);
        }
    }

    public function logout()
    {
        $this->session->destroy();
        session()->setFlashdata('success', 'Berhasil logout!');
        return redirect()->to('/auth/login');
    }

    public function profile()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Profile - Warehouse Management System',
            'user' => $user
        ];

        if ($this->request->getMethod() === 'POST') {
            return $this->updateProfile();
        }

        return view('auth/profile', $data);
    }

    public function updateProfile()
    {
        $userId = $this->session->get('user_id');

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]"
        ];

        // If password is provided, validate it
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
            $rules['confirm_password'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            $user = $this->userModel->find($userId);
            return view('auth/profile', [
                'title' => 'Profile - Warehouse Management System',
                'validation' => $this->validator,
                'user' => $user,
                'old' => $this->request->getPost()
            ]);
        }

        $updateData = [
            'full_name' => $this->request->getPost('full_name'),
            'email' => $this->request->getPost('email')
        ];

        // Update password if provided
        if ($this->request->getPost('password')) {
            $updateData['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($userId, $updateData)) {
            // Update session data
            $this->session->set('full_name', $updateData['full_name']);
            session()->setFlashdata('success', 'Profile berhasil diupdate!');
        } else {
            session()->setFlashdata('error', 'Gagal mengupdate profile!');
        }

        return redirect()->to('/auth/profile');
    }
}
