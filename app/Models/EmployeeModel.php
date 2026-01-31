<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'employee_code', 'nik', 'full_name', 'gender', 'birth_place', 'birth_date',
        'religion', 'marital_status', 'address', 'phone', 'email', 'photo',
        'department_id', 'position_id', 'supervisor_id', 'employment_status', 'join_date', 'resign_date',
        'bank_name', 'bank_account', 'bank_holder', 'npwp', 'bpjs_kesehatan',
        'bpjs_ketenagakerjaan', 'is_active'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'employee_code' => 'required|min_length[3]|max_length[20]|is_unique[employees.employee_code,id,{id}]',
        'full_name' => 'required|min_length[3]|max_length[150]',
        'department_id' => 'required|numeric',
        'position_id' => 'required|numeric',
        'join_date' => 'required|valid_date',
    ];

    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getWithDetails($id = null)
    {
        $builder = $this->select('employees.*, departments.name as department_name, positions.name as position_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left');
        
        if ($id !== null) {
            return $builder->where('employees.id', $id)->first();
        }
        
        return $builder->findAll();
    }

    public function getByDepartment($departmentId)
    {
        return $this->where('department_id', $departmentId)
            ->where('is_active', 1)
            ->findAll();
    }

    public function getByPosition($positionId)
    {
        return $this->where('position_id', $positionId)
            ->where('is_active', 1)
            ->findAll();
    }

    public function generateEmployeeCode()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "EMP{$year}{$month}";
        
        $lastEmployee = $this->like('employee_code', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();
        
        if ($lastEmployee) {
            $lastNumber = (int) substr($lastEmployee['employee_code'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getWithUser($employeeId)
    {
        return $this->select('employees.*, users.id as user_id, users.username, users.email as user_email')
            ->join('users', 'users.employee_id = employees.id', 'left')
            ->where('employees.id', $employeeId)
            ->first();
    }

    public function search($keyword)
    {
        return $this->select('employees.*, departments.name as department_name, positions.name as position_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->groupStart()
            ->like('employees.employee_code', $keyword)
            ->orLike('employees.full_name', $keyword)
            ->orLike('employees.nik', $keyword)
            ->groupEnd()
            ->findAll();
    }
}
