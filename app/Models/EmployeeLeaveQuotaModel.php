<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeLeaveQuotaModel extends Model
{
    protected $table = 'employee_leave_quotas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false; // Usually quotas are hard deleted or preserved history, no deleted_at required unless specified
    // But migration has no deleted_at, so useSoftDeletes = false.
    
    protected $allowedFields = [
        'employee_id',
        'leave_type_id',
        'year',
        'quota',
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getQuota($employeeId, $leaveTypeId, $year)
    {
        return $this->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();
    }
    
    public function setQuota($employeeId, $leaveTypeId, $year, $quota)
    {
        $existing = $this->getQuota($employeeId, $leaveTypeId, $year);
        
        if ($existing) {
            return $this->update($existing['id'], ['quota' => $quota]);
        } else {
            return $this->insert([
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
                'quota' => $quota,
            ]);
        }
    }
}
