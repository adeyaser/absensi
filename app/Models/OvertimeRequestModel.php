<?php

namespace App\Models;

use CodeIgniter\Model;

class OvertimeRequestModel extends Model
{
    protected $table = 'overtime_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'employee_id', 'date', 'start_time', 'end_time', 'duration_hours',
        'reason', 'status', 'designated_supervisor_id', 'designated_finance_id',
        'approved_by', 'approved_at', 'rejection_reason',
        'approved_by_supervisor', 'approved_at_supervisor',
        'approved_by_finance', 'approved_at_finance'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'employee_id' => 'required|numeric',
        'date' => 'required|valid_date',
        'start_time' => 'required',
        'end_time' => 'required',
    ];

    public function getWithEmployee($id = null)
    {
        $builder = $this->select('overtime_requests.*, 
                employees.full_name as employee_name, 
                employees.employee_code,
                employees.supervisor_id,
                departments.name as department_name,
                emp_supervisor.full_name as employee_supervisor_name,
                designated_sup.username as designated_supervisor_name,
                designated_fin.username as designated_finance_name,
                supervisor.username as supervisor_name,
                finance.username as finance_name')
            ->join('employees', 'employees.id = overtime_requests.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('employees as emp_supervisor', 'emp_supervisor.id = employees.supervisor_id', 'left')
            ->join('users as designated_sup', 'designated_sup.id = overtime_requests.designated_supervisor_id', 'left')
            ->join('users as designated_fin', 'designated_fin.id = overtime_requests.designated_finance_id', 'left')
            ->join('users as supervisor', 'supervisor.id = overtime_requests.approved_by_supervisor', 'left')
            ->join('users as finance', 'finance.id = overtime_requests.approved_by_finance', 'left');
        
        if ($id !== null) {
            return $builder->where('overtime_requests.id', $id)->first();
        }
        
        return $builder->orderBy('overtime_requests.created_at', 'DESC')->findAll();
    }

    public function getByEmployee($employeeId, $month = null, $year = null)
    {
        $builder = $this->where('employee_id', $employeeId);
        
        if ($month && $year) {
            $builder->where('MONTH(date)', $month)->where('YEAR(date)', $year);
        }
        
        return $builder->orderBy('date', 'DESC')->findAll();
    }

    public function getPending()
    {
        return $this->getWithEmployee()
            ->where('overtime_requests.status', 'pending')
            ->findAll();
    }

    public function getApprovedByMonth($employeeId, $month, $year)
    {
        return $this->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('MONTH(date)', $month)
            ->where('YEAR(date)', $year)
            ->findAll();
    }

    public function getTotalOvertimeHours($employeeId, $month, $year)
    {
        $result = $this->selectSum('duration_hours')
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('MONTH(date)', $month)
            ->where('YEAR(date)', $year)
            ->first();
        
        return (float) ($result['duration_hours'] ?? 0);
    }

    public function approve($id, $userId)
    {
        // Legacy method, might be used for direct approval or admin override
        return $this->approveFinance($id, $userId);
    }

    public function approveSupervisor($id, $userId)
    {
        return $this->update($id, [
            'status' => 'pending_finance',
            'approved_by_supervisor' => $userId,
            'approved_at_supervisor' => date('Y-m-d H:i:s'),
        ]);
    }

    public function approveFinance($id, $userId)
    {
        return $this->update($id, [
            'status' => 'approved',
            'approved_by_finance' => $userId,
            'approved_at_finance' => date('Y-m-d H:i:s'),
            // Maintain compatibility with legacy fields
            'approved_by' => $userId, 
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function reject($id, $userId, $reason)
    {
        return $this->update($id, [
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason,
        ]);
    }
}
