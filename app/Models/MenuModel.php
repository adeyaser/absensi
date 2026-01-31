<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table = 'menus';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['parent_id', 'title', 'icon', 'url', 'route', 'order_num', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'title' => 'required|min_length[2]|max_length[100]',
    ];

    public function getMenuTree($groupId = null)
    {
        if ($groupId) {
            // Get menus with permissions for specific group
            $menus = $this->select('menus.*, permissions.can_view, permissions.can_create, permissions.can_edit, permissions.can_delete')
                ->join('permissions', 'permissions.menu_id = menus.id AND permissions.group_id = ' . $groupId, 'left')
                ->where('menus.is_active', 1)
                ->where('permissions.can_view', 1)
                ->orderBy('menus.order_num', 'ASC')
                ->findAll();
        } else {
            $menus = $this->where('is_active', 1)
                ->orderBy('order_num', 'ASC')
                ->findAll();
        }

        return $this->buildTree($menus);
    }

    public function getAllMenus()
    {
        return $this->orderBy('parent_id', 'ASC')
            ->orderBy('order_num', 'ASC')
            ->findAll();
    }

    public function getParentMenus()
    {
        return $this->where('parent_id', 0)
            ->where('is_active', 1)
            ->orderBy('order_num', 'ASC')
            ->findAll();
    }

    private function buildTree(array $menus, $parentId = 0)
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $children = $this->buildTree($menus, $menu['id']);
                if ($children) {
                    $menu['children'] = $children;
                }
                $tree[] = $menu;
            }
        }
        return $tree;
    }

    public function getMenuByRoute($route)
    {
        return $this->where('route', $route)->first();
    }
}
