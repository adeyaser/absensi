<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['code' => 'IT', 'name' => 'Information Technology', 'description' => 'IT Department', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'HR', 'name' => 'Human Resources', 'description' => 'HR Department', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'FIN', 'name' => 'Finance & Accounting', 'description' => 'Finance Department', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'MKT', 'name' => 'Marketing', 'description' => 'Marketing Department', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'OPS', 'name' => 'Operations', 'description' => 'Operations Department', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['code' => 'GA', 'name' => 'General Affairs', 'description' => 'General Affairs Department', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];

        $this->db->table('departments')->insertBatch($data);
    }
}
