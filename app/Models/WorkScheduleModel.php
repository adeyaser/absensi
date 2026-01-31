<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkScheduleModel extends Model
{
    protected $table = 'work_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'code', 'name', 'clock_in', 'clock_out', 'break_start', 'break_end',
        'late_tolerance', 'early_leave_tolerance', 'work_hours', 'work_days', 'is_active'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'code' => 'required|min_length[2]|max_length[20]|is_unique[work_schedules.code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[100]',
        'clock_in' => 'required',
        'clock_out' => 'required',
    ];

    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getWorkDaysArray($schedule)
    {
        return explode(',', $schedule['work_days']);
    }

    public function isWorkDay($schedule, $dayOfWeek)
    {
        $workDays = $this->getWorkDaysArray($schedule);
        return in_array($dayOfWeek, $workDays);
    }
}
