<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOvertimeApprovals extends Migration
{
    public function up()
    {
        $fields = [
            'approved_by_supervisor' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'status',
            ],
            'approved_at_supervisor' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by_supervisor',
            ],
            'approved_by_finance' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'approved_at_supervisor',
            ],
            'approved_at_finance' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by_finance',
            ],
        ];
        
        $this->forge->addColumn('overtime_requests', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('overtime_requests', ['approved_by_supervisor', 'approved_at_supervisor', 'approved_by_finance', 'approved_at_finance']);
    }
}
