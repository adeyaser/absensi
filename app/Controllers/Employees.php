<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\DepartmentModel;
use App\Models\PositionModel;
use App\Models\UserModel;
use App\Models\EmployeeSalaryModel;
use App\Models\EmployeeScheduleModel;

class Employees extends BaseController
{
    protected $employeeModel;
    protected $departmentModel;
    protected $positionModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
        $this->departmentModel = new DepartmentModel();
        $this->positionModel = new PositionModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employees = $this->employeeModel->getWithDetails();
        $departments = $this->departmentModel->getActive();

        return view('employees/index', $this->viewData([
            'title' => 'Data Pegawai',
            'employees' => $employees,
            'departments' => $departments,
        ]));
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $departments = $this->departmentModel->getActive();
        $positions = $this->positionModel->getWithDepartment();
        $employees = $this->employeeModel->getWithDetails();

        return view('employees/form', $this->viewData([
            'title' => 'Tambah Pegawai',
            'employee' => null,
            'departments' => $departments,
            'positions' => $positions,
            'employees' => $employees,
        ]));
    }

    public function store()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $rules = [
            'full_name' => 'required|min_length[3]',
            'department_id' => 'required|numeric',
            'position_id' => 'required|numeric',
            'join_date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'employee_code' => $this->employeeModel->generateEmployeeCode(),
            'nik' => $this->request->getPost('nik'),
            'full_name' => $this->request->getPost('full_name'),
            'gender' => $this->request->getPost('gender') ?? 'L',
            'birth_place' => $this->request->getPost('birth_place'),
            'birth_date' => $this->request->getPost('birth_date'),
            'religion' => $this->request->getPost('religion'),
            'marital_status' => $this->request->getPost('marital_status') ?? 'single',
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'department_id' => $this->request->getPost('department_id'),
            'position_id' => $this->request->getPost('position_id'),
            'employment_status' => $this->request->getPost('employment_status') ?? 'permanent',
            'join_date' => $this->request->getPost('join_date'),
            'bank_name' => $this->request->getPost('bank_name'),
            'bank_account' => $this->request->getPost('bank_account'),
            'bank_holder' => $this->request->getPost('bank_holder'),
            'npwp' => $this->request->getPost('npwp'),
            'bpjs_kesehatan' => $this->request->getPost('bpjs_kesehatan'),
            'bpjs_ketenagakerjaan' => $this->request->getPost('bpjs_ketenagakerjaan'),
        ];

        // Handle photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $data['employee_code'] . '_' . $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads/employees', $newName);
            $data['photo'] = 'employees/' . $newName;
        }

        $employeeId = $this->employeeModel->insert($data);

        if (!$employeeId) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data pegawai');
        }

        // Create user account if requested
        if ($this->request->getPost('create_user')) {
            $userModel = new UserModel();
            $username = strtolower(str_replace(' ', '.', $data['full_name']));
            $userModel->insert([
                'username' => $username,
                'email' => $data['email'] ?? $username . '@absesi.com',
                'password' => 'password123',
                'group_id' => 5, // Employee group
                'employee_id' => $employeeId,
            ]);
        }

        // Set initial salary if provided
        $baseSalary = $this->request->getPost('base_salary');
        if ($baseSalary) {
            $salaryModel = new EmployeeSalaryModel();
            $salaryModel->insert([
                'employee_id' => $employeeId,
                'base_salary' => $baseSalary,
                'effective_date' => $data['join_date'],
            ]);
        }

        return redirect()->to('/employees')->with('success', 'Pegawai berhasil ditambahkan');
    }

    public function show($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employee = $this->employeeModel->getWithDetails($id);
        
        if (!$employee) {
            return redirect()->to('/employees')->with('error', 'Pegawai tidak ditemukan');
        }

        $salaryModel = new EmployeeSalaryModel();
        $scheduleModel = new EmployeeScheduleModel();
        $userModel = new UserModel();

        $salary = $salaryModel->getCurrentSalary($id);
        $schedules = $scheduleModel->getEmployeeSchedules($id);
        $user = $userModel->where('employee_id', $id)->first();

        return view('employees/show', $this->viewData([
            'title' => 'Detail Pegawai',
            'employee' => $employee,
            'salary' => $salary,
            'schedules' => $schedules,
            'user' => $user,
        ]));
    }

    public function edit($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employee = $this->employeeModel->find($id);
        
        if (!$employee) {
            return redirect()->to('/employees')->with('error', 'Pegawai tidak ditemukan');
        }

        $departments = $this->departmentModel->getActive();
        $positions = $this->positionModel->getWithDepartment();
        $employees = $this->employeeModel->getWithDetails();

        return view('employees/form', $this->viewData([
            'title' => 'Edit Pegawai',
            'employee' => $employee,
            'departments' => $departments,
            'positions' => $positions,
            'employees' => $employees,
        ]));
    }

    public function update($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employee = $this->employeeModel->find($id);
        
        if (!$employee) {
            return redirect()->to('/employees')->with('error', 'Pegawai tidak ditemukan');
        }

        $rules = [
            'full_name' => 'required|min_length[3]',
            'department_id' => 'required|numeric',
            'position_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nik' => $this->request->getPost('nik'),
            'full_name' => $this->request->getPost('full_name'),
            'gender' => $this->request->getPost('gender'),
            'birth_place' => $this->request->getPost('birth_place'),
            'birth_date' => $this->request->getPost('birth_date'),
            'religion' => $this->request->getPost('religion'),
            'marital_status' => $this->request->getPost('marital_status'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'department_id' => $this->request->getPost('department_id'),
            'position_id' => $this->request->getPost('position_id'),
            'employment_status' => $this->request->getPost('employment_status'),
            'resign_date' => $this->request->getPost('resign_date'),
            'bank_name' => $this->request->getPost('bank_name'),
            'bank_account' => $this->request->getPost('bank_account'),
            'bank_holder' => $this->request->getPost('bank_holder'),
            'npwp' => $this->request->getPost('npwp'),
            'bpjs_kesehatan' => $this->request->getPost('bpjs_kesehatan'),
            'bpjs_ketenagakerjaan' => $this->request->getPost('bpjs_ketenagakerjaan'),
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ];

        // Handle photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $employee['employee_code'] . '_' . $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads/employees', $newName);
            $data['photo'] = 'employees/' . $newName;
        }

        $this->employeeModel->update($id, $data);

        return redirect()->to('/employees/' . $id)->with('success', 'Data pegawai berhasil diupdate');
    }

    public function delete($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employee = $this->employeeModel->find($id);
        
        if (!$employee) {
            return redirect()->to('/employees')->with('error', 'Pegawai tidak ditemukan');
        }

        $this->employeeModel->delete($id);

        return redirect()->to('/employees')->with('success', 'Pegawai berhasil dihapus');
    }

    public function getPositions($departmentId)
    {
        $positions = $this->positionModel->getByDepartment($departmentId);
        return $this->successResponse($positions);
    }

    public function search()
    {
        $keyword = $this->request->getGet('q');
        $employees = $this->employeeModel->search($keyword);
        return $this->successResponse($employees);
    }

    public function export()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employees = $this->employeeModel->getWithDetails();

        // Generate CSV
        $filename = 'employees_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, ['Kode', 'NIK', 'Nama', 'Jenis Kelamin', 'Departemen', 'Jabatan', 'Status', 'Tanggal Masuk', 'Telepon', 'Email']);
        
        foreach ($employees as $emp) {
            fputcsv($output, [
                $emp['employee_code'],
                $emp['nik'],
                $emp['full_name'],
                $emp['gender'] == 'L' ? 'Laki-laki' : 'Perempuan',
                $emp['department_name'],
                $emp['position_name'],
                $emp['employment_status'],
                $emp['join_date'],
                $emp['phone'],
                $emp['email'],
            ]);
        }
        
        fclose($output);
        exit;
    }
}
