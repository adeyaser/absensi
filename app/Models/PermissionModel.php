<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['group_id', 'menu_id', 'can_view', 'can_create', 'can_edit', 'can_delete'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getByGroup($groupId)
    {
        return $this->select('permissions.*, menus.title as menu_title, menus.route')
            ->join('menus', 'menus.id = permissions.menu_id')
            ->where('permissions.group_id', $groupId)
            ->findAll();
    }

    public function getPermission($groupId, $menuId)
    {
        return $this->where('group_id', $groupId)
            ->where('menu_id', $menuId)
            ->first();
    }

    public function checkPermission($groupId, $route, $action = 'can_view')
    {
        $permission = $this->select('permissions.*')
            ->join('menus', 'menus.id = permissions.menu_id')
            ->where('permissions.group_id', $groupId)
            ->where('menus.route', $route)
            ->first();

        if (!$permission) {
            return false;
        }

        return (bool) $permission[$action];
    }

    public function updatePermissions($groupId, $permissions)
    {
        // Delete existing permissions for this group
        $this->where('group_id', $groupId)->delete();

        // Insert new permissions
        foreach ($permissions as $menuId => $perms) {
            $this->insert([
                'group_id' => $groupId,
                'menu_id' => $menuId,
                'can_view' => isset($perms['can_view']) ? 1 : 0,
                'can_create' => isset($perms['can_create']) ? 1 : 0,
                'can_edit' => isset($perms['can_edit']) ? 1 : 0,
                'can_delete' => isset($perms['can_delete']) ? 1 : 0,
            ]);
        }

        return true;
    }
}
