<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordHintToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'password_hint' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'password',
                'comment' => 'Hint password untuk membantu ingatan user'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'password_hint');
    }
}
