<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollsTable extends Migration
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
            'period_month' => [
                'type' => 'INT',
                'constraint' => 2,
            ],
            'period_year' => [
                'type' => 'INT',
                'constraint' => 4,
            ],
            'employee_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'work_days' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'present_days' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'absent_days' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'late_days' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'late_minutes' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'leave_days' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'sick_days' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'overtime_hours' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'base_salary' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'total_earnings' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'total_deductions' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'gross_salary' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'tax_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'net_salary' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'calculated', 'approved', 'paid'],
                'default' => 'draft',
            ],
            'calculated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey(['period_year', 'period_month']);
        $this->forge->addKey('employee_id');
        $this->forge->createTable('payrolls');
    }

    public function down()
    {
        $this->forge->dropTable('payrolls');
    }
}
