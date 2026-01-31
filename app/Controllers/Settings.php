<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GroupModel;
use App\Models\MenuModel;
use App\Models\PermissionModel;
use App\Models\SettingModel;
use App\Models\EmployeeModel;

class Settings extends BaseController
{
    public function general()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $settingModel = new SettingModel();
        $settings = $settingModel->getAllAsArray();
        $settingGroups = [
            'company' => $settingModel->getByGroup('company'),
            'attendance' => $settingModel->getByGroup('attendance'),
            'payroll' => $settingModel->getByGroup('payroll'),
            'bpjs' => $settingModel->getByGroup('bpjs'),
        ];

        return view('settings/index', $this->viewData([
            'title' => 'Pengaturan Umum',
            'settings' => $settings,
            'settingGroups' => $settingGroups,
        ]));
    }

    public function saveGeneral()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $settingModel = new SettingModel();
        $data = $this->request->getPost();

        // Handle logo upload
        $logo = $this->request->getFile('company_logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newName = 'logo_' . time() . '.' . $logo->getExtension();
            $logo->move(WRITEPATH . 'uploads', $newName);
            $data['company_logo'] = $newName;
        }

        $settingModel->updateSettings($data);

        return $this->successResponse(null, 'Pengaturan berhasil disimpan');
    }

    public function users()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $userModel = new UserModel();
        $groupModel = new GroupModel();

        $users = $userModel->getWithEmployee();
        $groups = $groupModel->getActive();

        // Load active employees for "Kaitkan dengan Pegawai" dropdown
        $employeeModel = new EmployeeModel();
        $employees = $employeeModel->getActive();

        return view('settings/users', $this->viewData([
            'title' => 'Manajemen User',
            'users' => $users,
            'groups' => $groups,
            'employees' => $employees,
        ]));
    }

    public function saveUser()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $userModel = new UserModel();
        $id = $this->request->getPost('id');

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'group_id' => $this->request->getPost('group_id'),
            'employee_id' => $this->request->getPost('employee_id') ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'password_hint' => $this->request->getPost('password_hint'),
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        if ($id) {
            $userModel->update($id, $data);
            $message = 'User berhasil diupdate';
        } else {
            if (!$this->request->getPost('password')) {
                return $this->errorResponse('Password wajib diisi untuk user baru');
            }
            $userModel->insert($data);
            $message = 'User berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function deleteUser($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $userModel = new UserModel();
        
        // Prevent deleting own account
        if ($id == $this->session->get('user_id')) {
            return $this->errorResponse('Tidak dapat menghapus akun sendiri');
        }

        $userModel->delete($id);

        return $this->successResponse(null, 'User berhasil dihapus');
    }

    public function groups()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $groupModel = new GroupModel();
        $groups = $groupModel->getWithUserCount();

        return view('settings/groups', $this->viewData([
            'title' => 'Manajemen Group',
            'groups' => $groups,
        ]));
    }

    public function saveGroup()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $groupModel = new GroupModel();
        $id = $this->request->getPost('id');

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $groupModel->update($id, $data);
            $message = 'Group berhasil diupdate';
        } else {
            $groupModel->insert($data);
            $message = 'Group berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function menus()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $menuModel = new MenuModel();
        $menus = $menuModel->getAllMenus();
        $parentMenus = $menuModel->getParentMenus();

        return view('settings/menus', $this->viewData([
            'title' => 'Manajemen Menu',
            'menus' => $menus,
            'parentMenus' => $parentMenus,
        ]));
    }

    public function saveMenu()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $menuModel = new MenuModel();
        $id = $this->request->getPost('id');

        $data = [
            'parent_id' => $this->request->getPost('parent_id') ?? 0,
            'title' => $this->request->getPost('title'),
            'icon' => $this->request->getPost('icon'),
            'url' => $this->request->getPost('url'),
            'route' => $this->request->getPost('route'),
            'order_num' => $this->request->getPost('order_num') ?? 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $menuModel->update($id, $data);
            $message = 'Menu berhasil diupdate';
        } else {
            $menuModel->insert($data);
            $message = 'Menu berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function permissions($groupId = null)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $groupModel = new GroupModel();
        $menuModel = new MenuModel();
        $permissionModel = new PermissionModel();

        $groups = $groupModel->getActive();
        $menus = $menuModel->getAllMenus();
        
        $selectedGroup = $groupId ?? $this->request->getGet('group_id') ?? ($groups[0]['id'] ?? null);
        $permissions = [];
        
        if ($selectedGroup) {
            $perms = $permissionModel->getByGroup($selectedGroup);
            foreach ($perms as $perm) {
                $permissions[$perm['menu_id']] = $perm;
            }
        }

        return view('settings/permissions', $this->viewData([
            'title' => 'Hak Akses',
            'groups' => $groups,
            'menus' => $menus,
            'selectedGroup' => $selectedGroup,
            'permissions' => $permissions,
        ]));
    }

    public function savePermissions()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $groupId = $this->request->getPost('group_id');
        $permissions = $this->request->getPost('permissions') ?? [];

        $permissionModel = new PermissionModel();
        $permissionModel->updatePermissions($groupId, $permissions);

        return $this->successResponse(null, 'Hak akses berhasil disimpan');
    }

    public function getEmployees()
    {
        $employeeModel = new EmployeeModel();
        $employees = $employeeModel->select('id, employee_code, full_name')
            ->where('is_active', 1)
            ->findAll();
        
        return $this->successResponse($employees);
    }
}
