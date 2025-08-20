<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'username',
        'email', 
        'password',
        'full_name',
        'role',
        'status',
        'last_login'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'full_name' => 'required|min_length[3]|max_length[100]',
        'role' => 'required|in_list[admin,operator]'
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Username harus diisi',
            'min_length' => 'Username minimal 3 karakter',
            'max_length' => 'Username maksimal 50 karakter',
            'is_unique' => 'Username sudah digunakan'
        ],
        'email' => [
            'required' => 'Email harus diisi',
            'valid_email' => 'Format email tidak valid',
            'is_unique' => 'Email sudah digunakan'
        ],
        'password' => [
            'required' => 'Password harus diisi',
            'min_length' => 'Password minimal 6 karakter'
        ],
        'full_name' => [
            'required' => 'Nama lengkap harus diisi',
            'min_length' => 'Nama lengkap minimal 3 karakter',
            'max_length' => 'Nama lengkap maksimal 100 karakter'
        ],
        'role' => [
            'required' => 'Role harus dipilih',
            'in_list' => 'Role tidak valid'
        ]
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function findByUsername($username)
    {
        return $this->where('username', $username)
                   ->orWhere('email', $username)
                   ->where('status', 'active')
                   ->first();
    }

    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function getUsersWithStats($limit = null, $offset = null, $search = null)
    {
        $builder = $this->select('users.*');

        if ($search) {
            $builder->groupStart()
                ->like('username', $search)
                ->orLike('full_name', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        $builder->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    public function getUserStatistics()
    {
        $stats = [];
        
        // Total users
        $stats['total_users'] = $this->countAll();
        
        // Active users
        $stats['active_users'] = $this->where('status', 'active')->countAllResults(false);
        
        // Users by role
        $stats['admin_users'] = $this->where('role', 'admin')->countAllResults(false);
        $stats['operator_users'] = $this->where('role', 'operator')->countAllResults(false);
        
        // Recent logins (last 30 days)
        $stats['recent_logins'] = $this->where('last_login >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                                      ->countAllResults(false);

        return $stats;
    }

    public function getActiveUsers()
    {
        return $this->where('status', 'active')
                   ->orderBy('full_name', 'ASC')
                   ->findAll();
    }

    public function changePassword($userId, $newPassword)
    {
        return $this->update($userId, ['password' => $newPassword]);
    }

    public function toggleStatus($userId)
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        return $this->update($userId, ['status' => $newStatus]);
    }

    public function isUsernameUnique($username, $excludeId = null)
    {
        $builder = $this->where('username', $username);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() === 0;
    }

    public function isEmailUnique($email, $excludeId = null)
    {
        $builder = $this->where('email', $email);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() === 0;
    }

    public function getUserActivityLog($userId, $limit = 10)
    {
        // This would require an activity log table
        // For now, we'll return login history
        return $this->select('last_login')
                   ->where('id', $userId)
                   ->first();
    }

    public function resetPassword($userId, $tempPassword = null)
    {
        if (!$tempPassword) {
            $tempPassword = bin2hex(random_bytes(4)); // Generate 8 character temp password
        }
        
        return $this->update($userId, ['password' => $tempPassword]);
    }
}