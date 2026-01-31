<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeScheduleModel extends Model
{
    protected $table = 'employee_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['employee_id', 'schedule_id', 'effective_date', 'end_date'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getCurrentSchedule($employeeId, $date = null)
    {
        $date = $date ?? date('Y-m-d');
        
        return $this->select('employee_schedules.*, work_schedules.*')
            ->join('work_schedules', 'work_schedules.id = employee_schedules.schedule_id')
            ->where('employee_schedules.employee_id', $employeeId)
            ->where('employee_schedules.effective_date <=', $date)
            ->groupStart()
            ->where('employee_schedules.end_date >=', $date)
            ->orWhere('employee_schedules.end_date IS NULL')
            ->groupEnd()
            ->first();
    }

    public function getEmployeeSchedules($employeeId)
    {
        return $this->select('employee_schedules.*, work_schedules.name as schedule_name, work_schedules.code as schedule_code')
            ->join('work_schedules', 'work_schedules.id = employee_schedules.schedule_id')
            ->where('employee_schedules.employee_id', $employeeId)
            ->orderBy('employee_schedules.effective_date', 'DESC')
            ->findAll();
    }

    public function assignSchedule($employeeId, $scheduleId, $effectiveDate, $endDate = null)
    {
        // End current schedule if exists
        $currentSchedule = $this->getCurrentSchedule($employeeId);
        if ($currentSchedule) {
            $this->update($currentSchedule['id'], [
                'end_date' => date('Y-m-d', strtotime($effectiveDate . ' -1 day'))
            ]);
        }

        // Insert new schedule
        return $this->insert([
            'employee_id' => $employeeId,
            'schedule_id' => $scheduleId,
            'effective_date' => $effectiveDate,
            'end_date' => $endDate,
        ]);
    }
}
