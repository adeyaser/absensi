<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // IT Department
            ['code' => 'IT-MGR', 'name' => 'IT Manager', 'department_id' => 1, 'level' => 5, 'base_salary' => 15000000],
            ['code' => 'IT-SPV', 'name' => 'IT Supervisor', 'department_id' => 1, 'level' => 4, 'base_salary' => 10000000],
            ['code' => 'IT-SR', 'name' => 'Senior Developer', 'department_id' => 1, 'level' => 3, 'base_salary' => 8000000],
            ['code' => 'IT-DEV', 'name' => 'Developer', 'department_id' => 1, 'level' => 2, 'base_salary' => 6000000],
            ['code' => 'IT-JR', 'name' => 'Junior Developer', 'department_id' => 1, 'level' => 1, 'base_salary' => 4500000],
            
            // HR Department
            ['code' => 'HR-MGR', 'name' => 'HR Manager', 'department_id' => 2, 'level' => 5, 'base_salary' => 15000000],
            ['code' => 'HR-SPV', 'name' => 'HR Supervisor', 'department_id' => 2, 'level' => 4, 'base_salary' => 10000000],
            ['code' => 'HR-STF', 'name' => 'HR Staff', 'department_id' => 2, 'level' => 2, 'base_salary' => 5000000],
            
            // Finance Department
            ['code' => 'FIN-MGR', 'name' => 'Finance Manager', 'department_id' => 3, 'level' => 5, 'base_salary' => 15000000],
            ['code' => 'FIN-SPV', 'name' => 'Finance Supervisor', 'department_id' => 3, 'level' => 4, 'base_salary' => 10000000],
            ['code' => 'FIN-ACC', 'name' => 'Accountant', 'department_id' => 3, 'level' => 3, 'base_salary' => 7000000],
            ['code' => 'FIN-STF', 'name' => 'Finance Staff', 'department_id' => 3, 'level' => 2, 'base_salary' => 5000000],
            
            // Marketing Department
            ['code' => 'MKT-MGR', 'name' => 'Marketing Manager', 'department_id' => 4, 'level' => 5, 'base_salary' => 15000000],
            ['code' => 'MKT-STF', 'name' => 'Marketing Staff', 'department_id' => 4, 'level' => 2, 'base_salary' => 5000000],
            
            // Operations Department
            ['code' => 'OPS-MGR', 'name' => 'Operations Manager', 'department_id' => 5, 'level' => 5, 'base_salary' => 15000000],
            ['code' => 'OPS-STF', 'name' => 'Operations Staff', 'department_id' => 5, 'level' => 2, 'base_salary' => 5000000],
            
            // GA Department
            ['code' => 'GA-MGR', 'name' => 'GA Manager', 'department_id' => 6, 'level' => 5, 'base_salary' => 12000000],
            ['code' => 'GA-STF', 'name' => 'GA Staff', 'department_id' => 6, 'level' => 2, 'base_salary' => 4500000],
        ];

        foreach ($data as $row) {
            $row['is_active'] = 1;
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('positions')->insert($row);
        }
    }
}
