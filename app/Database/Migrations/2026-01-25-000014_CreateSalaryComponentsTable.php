<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalaryComponentsTable extends Migration
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
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'unique' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['earning', 'deduction'],
                'default' => 'earning',
            ],
            'calculation_type' => [
                'type' => 'ENUM',
                'constraint' => ['fixed', 'percentage', 'formula'],
                'default' => 'fixed',
            ],
            'default_value' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'percentage_base' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'base_salary, gross_salary, etc',
            ],
            'formula' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_taxable' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'is_fixed' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'order_num' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->createTable('salary_components');
    }

    public function down()
    {
        $this->forge->dropTable('salary_components');
    }
}
