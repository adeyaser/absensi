<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollDetailModel extends Model
{
    protected $table = 'payroll_details';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'payroll_id', 'component_id', 'component_name', 'component_type', 'amount', 'is_taxable'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getByPayroll($payrollId)
    {
        return $this->where('payroll_id', $payrollId)
            ->orderBy('component_type', 'ASC')
            ->findAll();
    }

    public function getEarnings($payrollId)
    {
        return $this->where('payroll_id', $payrollId)
            ->where('component_type', 'earning')
            ->findAll();
    }

    public function getDeductions($payrollId)
    {
        return $this->where('payroll_id', $payrollId)
            ->where('component_type', 'deduction')
            ->findAll();
    }

    public function deleteByPayroll($payrollId)
    {
        return $this->where('payroll_id', $payrollId)->delete();
    }
}
