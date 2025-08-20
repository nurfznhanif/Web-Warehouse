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
        'role'
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
            'in_list' => 'Role harus admin atau operator'
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

    public function getUserWithRole($id)
    {
        return $this->where('id', $id)->first();
    }

    public function findByUsername($username)
    {
        return $this->where('username', $username)
            ->orWhere('email', $username)
            ->first();
    }

    public function getAllUsers($limit = null, $offset = null, $search = null)
    {
        $builder = $this->builder();

        if ($search) {
            $builder->groupStart()
                ->like('username', $search)
                ->orLike('email', $search)
                ->orLike('full_name', $search)
                ->groupEnd();
        }

        $builder->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function countUsers($search = null)
    {
        $builder = $this->builder();

        if ($search) {
            $builder->groupStart()
                ->like('username', $search)
                ->orLike('email', $search)
                ->orLike('full_name', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getUsersByRole($role)
    {
        return $this->where('role', $role)
            ->orderBy('full_name', 'ASC')
            ->findAll();
    }

    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function changePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    public function isEmailUnique($email, $excludeId = null)
    {
        $builder = $this->where('email', $email);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() === 0;
    }

    public function isUsernameUnique($username, $excludeId = null)
    {
        $builder = $this->where('username', $username);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() === 0;
    }

    public function getUserStatistics()
    {
        $stats = [];

        // Total users
        $stats['total_users'] = $this->countAll();

        // Users by role
        $stats['admin_count'] = $this->where('role', 'admin')->countAllResults();
        $stats['operator_count'] = $this->where('role', 'operator')->countAllResults();

        // Recent users (last 30 days)
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $stats['recent_users'] = $this->where('created_at >=', $thirtyDaysAgo)->countAllResults();

        // Active users (who have logged in recently)
        $stats['active_users'] = $this->where('last_login >=', $thirtyDaysAgo)->countAllResults();

        return $stats;
    }

    public function getRecentUsers($limit = 5)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function searchUsers($keyword)
    {
        return $this->like('username', $keyword)
            ->orLike('email', $keyword)
            ->orLike('full_name', $keyword)
            ->orderBy('full_name', 'ASC')
            ->findAll();
    }

    public function bulkUpdateRole($userIds, $newRole)
    {
        $this->db->transStart();

        foreach ($userIds as $userId) {
            $this->update($userId, ['role' => $newRole]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function deactivateUser($userId)
    {
        return $this->update($userId, ['status' => 'inactive']);
    }

    public function activateUser($userId)
    {
        return $this->update($userId, ['status' => 'active']);
    }
}
