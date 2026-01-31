<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'code' => 'REG',
                'name' => 'Regular Office Hours',
                'clock_in' => '08:00:00',
                'clock_out' => '17:00:00',
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'late_tolerance' => 15,
                'early_leave_tolerance' => 0,
                'work_days' => '1,2,3,4,5',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'SHIFT1',
                'name' => 'Shift 1 (Morning)',
                'clock_in' => '06:00:00',
                'clock_out' => '14:00:00',
                'break_start' => '10:00:00',
                'break_end' => '10:30:00',
                'late_tolerance' => 10,
                'early_leave_tolerance' => 0,
                'work_days' => '1,2,3,4,5,6',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'SHIFT2',
                'name' => 'Shift 2 (Afternoon)',
                'clock_in' => '14:00:00',
                'clock_out' => '22:00:00',
                'break_start' => '18:00:00',
                'break_end' => '18:30:00',
                'late_tolerance' => 10,
                'early_leave_tolerance' => 0,
                'work_days' => '1,2,3,4,5,6',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'SHIFT3',
                'name' => 'Shift 3 (Night)',
                'clock_in' => '22:00:00',
                'clock_out' => '06:00:00',
                'break_start' => '02:00:00',
                'break_end' => '02:30:00',
                'late_tolerance' => 10,
                'early_leave_tolerance' => 0,
                'work_days' => '1,2,3,4,5,6',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'FLEX',
                'name' => 'Flexible Hours',
                'clock_in' => '07:00:00',
                'clock_out' => '16:00:00',
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'late_tolerance' => 60,
                'early_leave_tolerance' => 30,
                'work_days' => '1,2,3,4,5',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('work_schedules')->insertBatch($data);
    }
}
