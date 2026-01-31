<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['code', 'name', 'description', 'head_employee_id', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'code' => 'required|min_length[2]|max_length[20]|is_unique[departments.code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[100]',
    ];

    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getWithHead()
    {
        return $this->select('departments.*, employees.full_name as head_name')
            ->join('employees', 'employees.id = departments.head_employee_id', 'left')
            ->findAll();
    }

    public function getWithEmployeeCount()
    {
        return $this->select('departments.*, COUNT(employees.id) as employee_count')
            ->join('employees', 'employees.department_id = departments.id', 'left')
            ->groupBy('departments.id')
            ->findAll();
    }
}
