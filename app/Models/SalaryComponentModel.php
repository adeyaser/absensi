<?php

namespace App\Models;

use CodeIgniter\Model;

class SalaryComponentModel extends Model
{
    protected $table = 'salary_components';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'code', 'name', 'type', 'calculation_type', 'default_value',
        'percentage_base', 'formula', 'is_taxable', 'is_fixed', 'order_num', 'description', 'is_active'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'code' => 'required|min_length[2]|max_length[20]|is_unique[salary_components.code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[100]',
        'type' => 'required|in_list[earning,deduction]',
    ];

    public function getActive()
    {
        return $this->where('is_active', 1)
            ->orderBy('order_num', 'ASC')
            ->findAll();
    }

    public function getEarnings()
    {
        return $this->where('is_active', 1)
            ->where('type', 'earning')
            ->orderBy('order_num', 'ASC')
            ->findAll();
    }

    public function getDeductions()
    {
        return $this->where('is_active', 1)
            ->where('type', 'deduction')
            ->orderBy('order_num', 'ASC')
            ->findAll();
    }

    public function getFixed()
    {
        return $this->where('is_active', 1)
            ->where('is_fixed', 1)
            ->orderBy('order_num', 'ASC')
            ->findAll();
    }
}
