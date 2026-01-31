<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollModel extends Model
{
    protected $table = 'payrolls';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'period_month', 'period_year', 'employee_id', 'work_days', 'present_days',
        'absent_days', 'late_days', 'late_minutes', 'leave_days', 'sick_days',
        'overtime_hours', 'base_salary', 'total_earnings', 'total_deductions',
        'gross_salary', 'tax_amount', 'net_salary', 'status', 'calculated_at',
        'approved_by', 'approved_at', 'paid_at', 'notes'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getWithEmployee($id = null)
    {
        $builder = $this->select('payrolls.*, employees.employee_code, employees.full_name, departments.name as department_name')
            ->join('employees', 'employees.id = payrolls.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left');
        
        if ($id !== null) {
            return $builder->where('payrolls.id', $id)->first();
        }
        
        return $builder->findAll();
    }

    public function getByPeriod($month, $year, $status = '')
    {
        $builder = $this->select('payrolls.*, employees.employee_code, employees.full_name, departments.name as department_name')
            ->join('employees', 'employees.id = payrolls.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->where('payrolls.period_month', $month)
            ->where('payrolls.period_year', $year);

        if (!empty($status)) {
            $builder->where('payrolls.status', $status);
        }

        return $builder->findAll();
    }

    public function getEmployeePayroll($employeeId, $month, $year)
    {
        return $this->where('employee_id', $employeeId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();
    }

    public function getEmployeePayrollHistory($employeeId, $limit = 12)
    {
        return $this->where('employee_id', $employeeId)
            ->orderBy('period_year', 'DESC')
            ->orderBy('period_month', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function approve($id, $userId)
    {
        return $this->update($id, [
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markAsPaid($id)
    {
        return $this->update($id, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function bulkApprove($ids, $userId)
    {
        return $this->whereIn('id', $ids)->set([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
        ])->update();
    }

    public function bulkMarkAsPaid($ids)
    {
        return $this->whereIn('id', $ids)->set([
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ])->update();
    }

    public function getPeriodSummary($month, $year)
    {
        return $this->select('
            COUNT(*) as total_employees,
            SUM(base_salary) as total_base_salary,
            SUM(total_earnings) as total_earnings,
            SUM(total_deductions) as total_deductions,
            SUM(gross_salary) as total_gross,
            SUM(net_salary) as total_net,
            SUM(tax_amount) as total_tax
        ')
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();
    }
}
