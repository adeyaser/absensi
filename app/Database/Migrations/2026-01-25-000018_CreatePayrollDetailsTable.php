<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollDetailsTable extends Migration
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
            'payroll_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'component_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'component_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'component_type' => [
                'type' => 'ENUM',
                'constraint' => ['earning', 'deduction'],
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'is_taxable' => [
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
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('payroll_id');
        $this->forge->createTable('payroll_details');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_details');
    }
}
