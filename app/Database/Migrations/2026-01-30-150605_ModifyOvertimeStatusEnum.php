<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyOvertimeStatusEnum extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'pending_finance', 'approved', 'rejected', 'cancelled'],
                'default' => 'pending',
            ],
        ];
        $this->forge->modifyColumn('overtime_requests', $fields);
    }

    public function down()
    {
        $fields = [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'cancelled'],
                'default' => 'pending',
            ],
        ];
        $this->forge->modifyColumn('overtime_requests', $fields);
    }
}
