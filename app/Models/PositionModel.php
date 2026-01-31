<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table = 'positions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['code', 'name', 'department_id', 'level', 'base_salary', 'description', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'code' => 'required|min_length[2]|max_length[20]|is_unique[positions.code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[100]',
        'department_id' => 'required|numeric',
    ];

    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getByDepartment($departmentId)
    {
        return $this->where('department_id', $departmentId)
            ->where('is_active', 1)
            ->orderBy('level', 'DESC')
            ->findAll();
    }

    public function getWithDepartment($id = null)
    {
        $builder = $this->select('positions.*, departments.name as department_name')
            ->join('departments', 'departments.id = positions.department_id', 'left');
        
        if ($id !== null) {
            return $builder->where('positions.id', $id)->first();
        }
        
        return $builder->findAll();
    }
}
