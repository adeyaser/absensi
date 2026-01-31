<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSupervisorToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'supervisor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'position_id',
                'comment' => 'ID atasan langsung'
            ],
        ]);

        // Add foreign key (optional, can be commented out if causing issues)
        // $this->forge->addForeignKey('supervisor_id', 'employees', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'supervisor_id');
    }
}
