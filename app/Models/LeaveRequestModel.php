<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveRequestModel extends Model
{
    protected $table = 'leave_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days',
        'reason', 'attachment', 'status', 'approved_by', 'approved_at', 'rejection_reason'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'employee_id' => 'required|numeric',
        'leave_type_id' => 'required|numeric',
        'start_date' => 'required|valid_date',
        'end_date' => 'required|valid_date',
    ];

    public function getWithDetails($id = null)
    {
        $builder = $this->select('
            leave_requests.*,
            employees.full_name as employee_name,
            employees.employee_code,
            leave_types.name as leave_type_name,
            leave_types.code as leave_type_code,
            approver.username as approved_by_name
        ')
            ->join('employees', 'employees.id = leave_requests.employee_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->join('users as approver', 'approver.id = leave_requests.approved_by', 'left');
        
        if ($id !== null) {
            return $builder->where('leave_requests.id', $id)->first();
        }
        
        return $builder->orderBy('leave_requests.created_at', 'DESC')->findAll();
    }

    public function getByEmployee($employeeId, $year = null)
    {
        $builder = $this->select('leave_requests.*, leave_types.name as leave_type_name')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('leave_requests.employee_id', $employeeId);
        
        if ($year) {
            $builder->where('YEAR(leave_requests.start_date)', $year);
        }
        
        return $builder->orderBy('leave_requests.created_at', 'DESC')->findAll();
    }

    public function getPending()
    {
        return $this->getWithDetails()
            ->where('leave_requests.status', 'pending')
            ->orderBy('leave_requests.created_at', 'ASC')
            ->findAll();
    }

    public function getPendingByDepartment($departmentId)
    {
        return $this->select('
            leave_requests.*,
            employees.full_name as employee_name,
            employees.employee_code,
            leave_types.name as leave_type_name
        ')
            ->join('employees', 'employees.id = leave_requests.employee_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('employees.department_id', $departmentId)
            ->where('leave_requests.status', 'pending')
            ->orderBy('leave_requests.created_at', 'ASC')
            ->findAll();
    }

    public function getLeaveBalance($employeeId, $year = null)
    {
        $year = $year ?? date('Y');
        
        $db = \Config\Database::connect();
        $quotaModel = new \App\Models\EmployeeLeaveQuotaModel();
        
        $leaveTypes = $db->table('leave_types')
            ->where('is_active', 1)
            ->get()
            ->getResultArray();
        
        $balance = [];
        
        foreach ($leaveTypes as $type) {
            // Get base quota (default from type)
            $quota = $type['quota'];
            
            // Check for individual override
            $override = $quotaModel->getQuota($employeeId, $type['id'], $year);
            if ($override) {
                $quota = $override['quota'];
            }
            
            // If quota is 0 (and not overriden to positive), skip if we only want to show available leaves
            // But sometimes we want to show 0 quota leaves too if they are just used up.
            // Let's keep showing all active types.

            $used = $this->where('employee_id', $employeeId)
                ->where('leave_type_id', $type['id'])
                ->whereIn('status', ['approved', 'pending'])
                ->where('YEAR(start_date)', $year)
                ->selectSum('total_days')
                ->first();
            
            $usedDays = (int) ($used['total_days'] ?? 0);
            
            $balance[] = [
                'leave_type_id' => $type['id'],
                'leave_type_name' => $type['name'],
                'quota' => $quota,
                'used' => $usedDays,
                'remaining' => $quota - $usedDays,
            ];
        }
        
        return $balance;
    }

    public function approve($id, $userId)
    {
        return $this->update($id, [
            'status' => 'approved',
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

    public function calculateTotalDays($startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        return $interval->days + 1;
    }
}
