<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendances';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'employee_id', 'date', 'clock_in', 'clock_out',
        'clock_in_latitude', 'clock_in_longitude', 'clock_out_latitude', 'clock_out_longitude',
        'clock_in_photo', 'clock_out_photo', 'clock_in_address', 'clock_out_address',
        'office_location_id', 'status', 'late_minutes', 'early_leave_minutes',
        'overtime_minutes', 'work_hours', 'notes', 'is_valid_location', 'is_valid_face'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getTodayAttendance($employeeId)
    {
        return $this->where('employee_id', $employeeId)
            ->where('date', date('Y-m-d'))
            ->first();
    }

    public function getByDateRange($employeeId, $startDate, $endDate)
    {
        return $this->where('employee_id', $employeeId)
            ->where('date >=', $startDate)
            ->where('date <=', $endDate)
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    public function getByMonth($employeeId, $month, $year)
    {
        return $this->where('employee_id', $employeeId)
            ->where('MONTH(date)', $month)
            ->where('YEAR(date)', $year)
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    public function getMonthlyRecap($employeeId, $month, $year)
    {
        $attendances = $this->getByMonth($employeeId, $month, $year);
        
        $recap = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'leave' => 0,
            'sick' => 0,
            'permit' => 0,
            'total_work_hours' => 0,
            'total_late_minutes' => 0,
            'total_overtime_minutes' => 0,
        ];
        
        foreach ($attendances as $att) {
            switch ($att['status']) {
                case 'present':
                    $recap['present']++;
                    break;
                case 'late':
                    $recap['late']++;
                    $recap['present']++;
                    break;
                case 'absent':
                    $recap['absent']++;
                    break;
                case 'leave':
                    $recap['leave']++;
                    break;
                case 'sick':
                    $recap['sick']++;
                    break;
                case 'permit':
                    $recap['permit']++;
                    break;
            }
            
            $recap['total_work_hours'] += (float) $att['work_hours'];
            $recap['total_late_minutes'] += (int) $att['late_minutes'];
            $recap['total_overtime_minutes'] += (int) $att['overtime_minutes'];
        }
        
        return $recap;
    }

    public function getAllEmployeeRecap($month, $year)
    {
        return $this->select('
            employees.id as employee_id,
            employees.employee_code,
            employees.full_name,
            departments.name as department_name,
            SUM(CASE WHEN attendances.status IN ("present", "late") THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN attendances.status = "late" THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN attendances.status = "absent" THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN attendances.status = "leave" THEN 1 ELSE 0 END) as leave_days,
            SUM(CASE WHEN attendances.status = "sick" THEN 1 ELSE 0 END) as sick_days,
            SUM(attendances.late_minutes) as total_late_minutes,
            SUM(attendances.overtime_minutes) as total_overtime_minutes,
            SUM(attendances.work_hours) as total_work_hours
        ')
            ->join('employees', 'employees.id = attendances.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->where('MONTH(attendances.date)', $month)
            ->where('YEAR(attendances.date)', $year)
            ->groupBy('attendances.employee_id')
            ->findAll();
    }

    public function getDailyReport($date)
    {
        return $this->select('attendances.*, employees.employee_code, employees.full_name, departments.name as department_name')
            ->join('employees', 'employees.id = attendances.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->where('attendances.date', $date)
            ->orderBy('employees.full_name', 'ASC')
            ->findAll();
    }

    public function clockIn($data)
    {
        $existing = $this->getTodayAttendance($data['employee_id']);
        
        if ($existing) {
            return ['error' => 'Already clocked in today'];
        }
        
        $data['date'] = date('Y-m-d');
        $data['clock_in'] = date('Y-m-d H:i:s');
        
        $insertId = $this->insert($data);
        return $insertId ? $this->find($insertId) : ['error' => 'Failed to clock in'];
    }

    public function clockOut($employeeId, $data)
    {
        $attendance = $this->getTodayAttendance($employeeId);
        
        if (!$attendance) {
            return ['error' => 'No clock in record found for today'];
        }
        
        if ($attendance['clock_out']) {
            return ['error' => 'Already clocked out today'];
        }
        
        $data['clock_out'] = date('Y-m-d H:i:s');
        
        // Calculate work hours
        $clockIn = new \DateTime($attendance['clock_in']);
        $clockOut = new \DateTime($data['clock_out']);
        $interval = $clockIn->diff($clockOut);
        $data['work_hours'] = round($interval->h + ($interval->i / 60), 2);
        
        $this->update($attendance['id'], $data);
        return $this->find($attendance['id']);
    }
}
