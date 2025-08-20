<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Basic validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama kategori harus diisi',
            'min_length' => 'Nama kategori minimal 2 karakter',
            'max_length' => 'Nama kategori maksimal 100 karakter'
        ]
    ];
}
