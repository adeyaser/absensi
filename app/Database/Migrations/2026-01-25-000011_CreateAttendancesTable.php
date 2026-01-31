<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendancesTable extends Migration
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
            'employee_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'clock_in' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'clock_out' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'clock_in_latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
            ],
            'clock_in_longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
            ],
            'clock_out_latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
            ],
            'clock_out_longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
            ],
            'clock_in_photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'clock_out_photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'clock_in_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'clock_out_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'office_location_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['present', 'late', 'early_leave', 'absent', 'leave', 'sick', 'permit', 'holiday', 'off'],
                'default' => 'present',
            ],
            'late_minutes' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'early_leave_minutes' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'overtime_minutes' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'work_hours' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_valid_location' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'is_valid_face' => [
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
        $this->forge->addKey('employee_id');
        $this->forge->addKey('date');
        $this->forge->addKey(['employee_id', 'date']);
        $this->forge->createTable('attendances');
    }

    public function down()
    {
        $this->forge->dropTable('attendances');
    }
}
