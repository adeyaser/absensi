<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('GroupSeeder');
        $this->call('MenuSeeder');
        $this->call('PermissionSeeder');
        $this->call('UserSeeder');
        $this->call('DepartmentSeeder');
        $this->call('PositionSeeder');
        $this->call('WorkScheduleSeeder');
        $this->call('OfficeLocationSeeder');
        $this->call('LeaveTypeSeeder');
        $this->call('SalaryComponentSeeder');
        $this->call('SettingsSeeder');
    }
}
