<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWorkHoursToWorkSchedules extends Migration
{
    public function up()
    {
        $this->forge->addColumn('work_schedules', [
            'work_hours' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 8.00,
                'after' => 'early_leave_tolerance'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('work_schedules', 'work_hours');
    }
}
