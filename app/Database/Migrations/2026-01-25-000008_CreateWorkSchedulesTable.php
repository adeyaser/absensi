<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkSchedulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'unique' => true,
            ],
            'clock_in' => [
                'type' => 'TIME',
            ],
            'clock_out' => [
                'type' => 'TIME',
            ],
            'break_start' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'break_end' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'late_tolerance' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 15,
                'comment' => 'in minutes',
            ],
            'early_leave_tolerance' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'in minutes',
            ],
            'work_days' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => '1,2,3,4,5',
                'comment' => '0=Sunday, 1=Monday, etc',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('work_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('work_schedules');
    }
}
