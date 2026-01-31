<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@absesi.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'group_id' => 1,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'hrd',
                'email' => 'hrd@absesi.com',
                'password' => password_hash('hrd123', PASSWORD_DEFAULT),
                'group_id' => 2,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'finance',
                'email' => 'finance@absesi.com',
                'password' => password_hash('finance123', PASSWORD_DEFAULT),
                'group_id' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
