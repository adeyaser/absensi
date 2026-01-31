<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Get all menus
        $menus = $this->db->table('menus')->get()->getResultArray();
        
        // Super Admin (group_id = 1) - Full access
        foreach ($menus as $menu) {
            $this->db->table('permissions')->ignore(true)->insert([
                'group_id' => 1,
                'menu_id' => $menu['id'],
                'can_view' => 1,
                'can_create' => 1,
                'can_edit' => 1,
                'can_delete' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // HRD (group_id = 2)
        $hrdMenus = [1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 20, 21, 22, 23, 24, 30, 31, 32, 33, 50, 51, 52];
        foreach ($hrdMenus as $menuId) {
            $this->db->table('permissions')->ignore(true)->insert([
                'group_id' => 2,
                'menu_id' => $menuId,
                'can_view' => 1,
                'can_create' => 1,
                'can_edit' => 1,
                'can_delete' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Finance (group_id = 3)
        $financeMenus = [1, 40, 41, 42, 43, 44, 45, 50, 53];
        foreach ($financeMenus as $menuId) {
            $this->db->table('permissions')->ignore(true)->insert([
                'group_id' => 3,
                'menu_id' => $menuId,
                'can_view' => 1,
                'can_create' => 1,
                'can_edit' => 1,
                'can_delete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Manager (group_id = 4)
        $managerMenus = [1, 20, 22, 23, 24, 30, 32, 33, 50, 51, 52];
        foreach ($managerMenus as $menuId) {
            $this->db->table('permissions')->ignore(true)->insert([
                'group_id' => 4,
                'menu_id' => $menuId,
                'can_view' => 1,
                'can_create' => 0,
                'can_edit' => 1,
                'can_delete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Employee (group_id = 5)
        // Added menu 24 (Overtime Request) to employee permissions
        $employeeMenus = [1, 20, 21, 22, 24, 30, 31, 33, 44];
        foreach ($employeeMenus as $menuId) {
            $this->db->table('permissions')->ignore(true)->insert([
                'group_id' => 5,
                'menu_id' => $menuId,
                'can_view' => 1,
                // Employee can create clock-in (21), leave request (31), and overtime request (24)
                'can_create' => in_array($menuId, [21, 31, 24]) ? 1 : 0,
                'can_edit' => 0,
                'can_delete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
