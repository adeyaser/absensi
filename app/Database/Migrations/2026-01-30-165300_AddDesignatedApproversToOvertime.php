<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDesignatedApproversToOvertime extends Migration
{
    public function up()
    {
        $this->forge->addColumn('overtime_requests', [
            'designated_supervisor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'status',
                'comment' => 'User ID yang ditunjuk sebagai approver atasan'
            ],
            'designated_finance_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'designated_supervisor_id',
                'comment' => 'User ID yang ditunjuk sebagai approver keuangan'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('overtime_requests', ['designated_supervisor_id', 'designated_finance_id']);
    }
}
