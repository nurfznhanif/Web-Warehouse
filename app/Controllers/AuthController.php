<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if ($this->request->getMethod() === 'post') {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            
            $userModel = new UserModel();
            $user = $userModel->getUserByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                $sessionData = [
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'logged_in' => true
                ];
                session()->set($sessionData);
                
                return redirect()->to('/dashboard');
            } else {
                session()->setFlashdata('error', 'Email atau password salah');
                return redirect()->to('/login');
            }
        }
        
        return $this->renderView('auth/login');
    }
    
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}