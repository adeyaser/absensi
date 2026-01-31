<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveTypeModel extends Model
{
    protected $table = 'leave_types';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['code', 'name', 'quota', 'is_paid', 'is_deductible', 'description', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'code' => 'required|min_length[2]|max_length[20]|is_unique[leave_types.code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[100]',
    ];

    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }
}
