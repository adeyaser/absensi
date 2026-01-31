<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupModel extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['name', 'description', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
    ];

    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getWithUserCount()
    {
        return $this->select('groups.*, COUNT(users.id) as user_count')
            ->join('users', 'users.group_id = groups.id', 'left')
            ->groupBy('groups.id')
            ->findAll();
    }
}
