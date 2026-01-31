<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\LeaveTypeModel;
use App\Models\EmployeeLeaveQuotaModel;
use App\Models\LeaveRequestModel;

class LeaveConfig extends BaseController
{
    protected $employeeModel;
    protected $leaveTypeModel;
    protected $quotaModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
        $this->leaveTypeModel = new LeaveTypeModel();
        $this->quotaModel = new EmployeeLeaveQuotaModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn() || $this->currentUser['group_id'] != 1) {
            return redirect()->to('/auth');
        }

        $departmentId = $this->request->getGet('department_id');
        $year = $this->request->getGet('year') ?? date('Y');

        $query = $this->employeeModel
            ->select('employees.id, employees.employee_code, employees.full_name, departments.name as department_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->where('employees.is_active', 1);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->findAll();
        $leaveTypes = $this->leaveTypeModel->where('is_active', 1)->findAll();

        // Attach quotas summary
        foreach ($employees as &$emp) {
            $emp['quotas'] = [];
            foreach ($leaveTypes as $type) {
                // Get configured quota or default
                $configured = $this->quotaModel->getQuota($emp['id'], $type['id'], $year);
                $quota = $configured ? $configured['quota'] : $type['quota'];
                
                $emp['quotas'][$type['id']] = $quota;
            }
        }

        $departmentModel = new \App\Models\DepartmentModel();
        $departments = $departmentModel->findAll();

        return view('leave_config/index', $this->viewData([
            'title' => 'Konfigurasi Cuti Karyawan',
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'departments' => $departments,
            'department_id' => $departmentId,
            'year' => $year
        ]));
    }

    public function edit($employeeId)
    {
        if (!$this->isLoggedIn() || $this->currentUser['group_id'] != 1) {
            return redirect()->to('/auth');
        }

        $year = $this->request->getGet('year') ?? date('Y');
        
        $employee = $this->employeeModel->find($employeeId);
        if (!$employee) {
            return redirect()->back()->with('error', 'Karyawan tidak ditemukan');
        }

        $leaveTypes = $this->leaveTypeModel->where('is_active', 1)->findAll();
        
        $quotas = [];
        foreach ($leaveTypes as $type) {
             $configured = $this->quotaModel->getQuota($employeeId, $type['id'], $year);
             $quotas[$type['id']] = $configured ? $configured['quota'] : $type['quota'];
        }

        return view('leave_config/edit', $this->viewData([
            'title' => 'Edit Kuota Cuti: ' . $employee['full_name'],
            'employee' => $employee,
            'leaveTypes' => $leaveTypes,
            'quotas' => $quotas,
            'year' => $year
        ]));
    }

    public function update($employeeId)
    {
        if (!$this->isLoggedIn() || $this->currentUser['group_id'] != 1) {
            return redirect()->to('/auth');
        }

        $year = $this->request->getPost('year');
        $quotas = $this->request->getPost('quotas'); // Array [leave_type_id => amount]

        foreach ($quotas as $typeId => $amount) {
            $this->quotaModel->setQuota($employeeId, $typeId, $year, $amount);
        }

        return redirect()->to('/leave-config?year=' . $year)->with('success', 'Kuota cuti berhasil diperbarui');
    }
}
