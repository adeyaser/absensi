<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'username', 'email', 'password', 'password_hint', 'group_id', 'employee_id',
        'avatar', 'face_encoding', 'is_active', 'last_login'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'permit_empty|min_length[6]',
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['data']['password']);
        }
        return $data;
    }

    public function getWithGroup($id = null)
    {
        $builder = $this->select('users.*, groups.name as group_name')
            ->join('groups', 'groups.id = users.group_id', 'left');
        
        if ($id !== null) {
            return $builder->where('users.id', $id)->first();
        }
        
        return $builder->findAll();
    }

    public function getWithEmployee($id = null)
    {
        $builder = $this->select('users.*, employees.full_name, employees.employee_code, groups.name as group_name')
            ->join('groups', 'groups.id = users.group_id', 'left')
            ->join('employees', 'employees.id = users.employee_id', 'left');
        
        if ($id !== null) {
            return $builder->where('users.id', $id)->first();
        }
        
        return $builder->findAll();
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }
}
