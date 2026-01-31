<?php

namespace App\Controllers;

use App\Models\PayrollModel;
use App\Models\PayrollDetailModel;
use App\Models\EmployeeModel;
use App\Models\EmployeeSalaryModel;
use App\Models\SalaryComponentModel;
use App\Models\DepartmentModel;
use App\Models\EmployeeSalaryComponentModel;
use App\Libraries\PayrollCalculator;

class Payroll extends BaseController
{
    protected $payrollModel;
    protected $payrollDetailModel;
    protected $employeeModel;

    public function __construct()
    {
        $this->payrollModel = new PayrollModel();
        $this->payrollDetailModel = new PayrollDetailModel();
        $this->employeeModel = new EmployeeModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');
        $status = $this->request->getGet('status') ?? '';

        $payrolls = $this->payrollModel->getByPeriod($month, $year, $status);
        $summary = $this->payrollModel->getPeriodSummary($month, $year);

        return view('payroll/index', $this->viewData([
            'title' => 'Proses Penggajian',
            'payrolls' => $payrolls,
            'summary' => $summary,
            'month' => $month,
            'year' => $year,
            'status' => $status,
        ]));
    }

    public function process()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');

        $employees = $this->employeeModel->getActive();
        
        $departmentModel = new DepartmentModel();
        $departments = $departmentModel->findAll();

        return view('payroll/process', $this->viewData([
            'title' => 'Proses Penggajian',
            'employees' => $employees,
            'departments' => $departments,
            'month' => $month,
            'year' => $year,
        ]));
    }

    public function calculate()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $month = $this->request->getPost('month');
        $year = $this->request->getPost('year');
        $departmentId = $this->request->getPost('department_id');
        $employeeIds = $this->request->getPost('employee_ids');

        if (!$month || !$year) {
            return $this->errorResponse('Bulan dan tahun wajib diisi');
        }

        if (!$employeeIds || !is_array($employeeIds)) {
            // Calculate for all employees or by department
            if ($departmentId) {
                $employees = $this->employeeModel->getByDepartment($departmentId);
            } else {
                $employees = $this->employeeModel->getActive();
            }
            $employeeIds = array_column($employees, 'id');
        }

        $calculator = new PayrollCalculator();
        $results = [];
        $errors = [];

        foreach ($employeeIds as $employeeId) {
            try {
                // Check if payroll already exists
                $existing = $this->payrollModel->getEmployeePayroll($employeeId, $month, $year);
                
                if ($existing && $existing['status'] !== 'draft') {
                    $errors[] = "Payroll untuk pegawai ID {$employeeId} sudah diproses";
                    continue;
                }

                // Calculate payroll
                $payrollData = $calculator->calculate($employeeId, $month, $year);
                
                $payroll = [
                    'period_month' => $payrollData['period_month'],
                    'period_year' => $payrollData['period_year'],
                    'employee_id' => $payrollData['employee_id'],
                    'work_days' => $payrollData['work_days'],
                    'present_days' => $payrollData['present_days'],
                    'absent_days' => $payrollData['absent_days'],
                    'late_days' => $payrollData['late_days'],
                    'late_minutes' => $payrollData['late_minutes'],
                    'leave_days' => $payrollData['leave_days'],
                    'sick_days' => $payrollData['sick_days'],
                    'overtime_hours' => $payrollData['overtime_hours'],
                    'base_salary' => $payrollData['base_salary'],
                    'total_earnings' => $payrollData['total_earnings'],
                    'total_deductions' => $payrollData['total_deductions'],
                    'gross_salary' => $payrollData['gross_salary'],
                    'tax_amount' => $payrollData['tax_amount'],
                    'net_salary' => $payrollData['net_salary'],
                    'status' => 'calculated',
                    'calculated_at' => date('Y-m-d H:i:s'),
                ];

                if ($existing) {
                    $this->payrollModel->update($existing['id'], $payroll);
                    $payrollId = $existing['id'];
                    
                    // Delete existing details
                    $this->payrollDetailModel->deleteByPayroll($payrollId);
                } else {
                    $payrollId = $this->payrollModel->insert($payroll);
                }

                // Insert details
                foreach ($payrollData['earnings'] as $earning) {
                    $earning['payroll_id'] = $payrollId;
                    $this->payrollDetailModel->insert($earning);
                }

                foreach ($payrollData['deductions'] as $deduction) {
                    $deduction['payroll_id'] = $payrollId;
                    $this->payrollDetailModel->insert($deduction);
                }

                $results[] = $payrollId;
            } catch (\Exception $e) {
                $errors[] = "Error processing employee ID {$employeeId}: " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $processedCount = count($results);
        if ($processedCount === 0) {
             return redirect()->back()->with('error', 'Tidak ada data penggajian yang diproses.');
        }

        $message = "Penggajian berhasil diproses untuk $processedCount pegawai.";

        // Jika hanya 1 pegawai, redirect langsung ke slip gaji agar bisa dicetak
        if ($processedCount === 1 && !empty($results[0])) {
            return redirect()->to('/payroll/slip/' . $results[0])->with('success', $message);
        }

        return redirect()->to('/payroll')->with('success', $message);
    }

    public function show($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $payroll = $this->payrollModel->getWithEmployee($id);
        
        if (!$payroll) {
            return redirect()->to('/payroll')->with('error', 'Data tidak ditemukan');
        }

        $details = $this->payrollDetailModel->getByPayroll($id);
        $earnings = array_filter($details, fn($d) => $d['component_type'] === 'earning');
        $deductions = array_filter($details, fn($d) => $d['component_type'] === 'deduction');

        return view('payroll/show', $this->viewData([
            'title' => 'Detail Penggajian',
            'payroll' => $payroll,
            'earnings' => $earnings,
            'deductions' => $deductions,
        ]));
    }

    public function slip($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $payroll = $this->payrollModel->getWithEmployee($id);
        
        if (!$payroll) {
            return redirect()->to('/payroll')->with('error', 'Data tidak ditemukan');
        }

        // Check permission - employees can only view their own slip
        if ($this->currentUser['group_id'] == 5) {
            if ($payroll['employee_id'] != $this->getEmployeeId()) {
                return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
            }
        }

        $details = $this->payrollDetailModel->getByPayroll($id);
        $earnings = array_filter($details, fn($d) => $d['component_type'] === 'earning');
        $deductions = array_filter($details, fn($d) => $d['component_type'] === 'deduction');

        $settingModel = new \App\Models\SettingModel();
        $settings = $settingModel->getAllAsArray();

        return view('payroll/slip', $this->viewData([
            'title' => 'Slip Gaji',
            'payroll' => $payroll,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'settings' => $settings,
        ]));
    }

    public function approve()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $ids = $this->request->getPost('ids');
        
        if (!$ids || !is_array($ids)) {
            return $this->errorResponse('Pilih data yang akan disetujui');
        }

        $this->payrollModel->bulkApprove($ids, $this->session->get('user_id'));

        return $this->successResponse(null, 'Penggajian berhasil disetujui');
    }

    public function pay()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $ids = $this->request->getPost('ids');
        
        if (!$ids || !is_array($ids)) {
            return $this->errorResponse('Pilih data yang akan dibayar');
        }

        $this->payrollModel->bulkMarkAsPaid($ids);

        return $this->successResponse(null, 'Status pembayaran berhasil diupdate');
    }

    public function mySlips()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employeeId = $this->getEmployeeId();
        
        // Only check employee_id for non-admin users (group_id != 1 and != 2)
        if (!$employeeId && !in_array($this->currentUser['group_id'], [1, 2])) {
            return redirect()->to('/dashboard')->with('error', 'Akun tidak terhubung dengan data pegawai');
        }

        $payrolls = $this->payrollModel->getEmployeePayrollHistory($employeeId);

        return view('payroll/my_slips', $this->viewData([
            'title' => 'Slip Gaji Saya',
            'payrolls' => $payrolls,
        ]));
    }

    public function components()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $componentModel = new SalaryComponentModel();
        $components = $componentModel->findAll();

        return view('payroll/components', $this->viewData([
            'title' => 'Komponen Gaji',
            'components' => $components,
        ]));
    }

    public function saveComponent()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $componentModel = new SalaryComponentModel();
        
        $data = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'calculation_type' => $this->request->getPost('calculation_type'),
            'default_value' => $this->request->getPost('default_value') ?? 0,
            'percentage_base' => $this->request->getPost('percentage_base'),
            'formula' => $this->request->getPost('formula'),
            'is_taxable' => $this->request->getPost('is_taxable') ? 1 : 0,
            'is_fixed' => $this->request->getPost('is_fixed') ? 1 : 0,
            'order_num' => $this->request->getPost('order_num') ?? 0,
            'description' => $this->request->getPost('description'),
            'is_active' => 1,
        ];

        $id = $this->request->getPost('id');

        if ($id) {
            $componentModel->update($id, $data);
            $message = 'Komponen berhasil diupdate';
        } else {
            $componentModel->insert($data);
            $message = 'Komponen berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function employeeSalary()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $salaryModel = new EmployeeSalaryModel();
        $compModel = new SalaryComponentModel();
        $empSalaryCompModel = new EmployeeSalaryComponentModel();
        
        $employees = $this->employeeModel->select('employees.*, employee_salary.base_salary, departments.name as department_name, positions.name as position_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->join('employee_salary', 'employee_salary.employee_id = employees.id AND employee_salary.end_date IS NULL', 'left')
            ->where('employees.is_active', 1)
            ->findAll();

        foreach ($employees as &$emp) {
            $emp['salary_components'] = $empSalaryCompModel->getEmployeeComponents($emp['id']);
            
            // Also fetch position components if employee doesn't have custom ones
            if ($emp['position_id']) {
                $posCompModel = new \App\Models\PositionSalaryComponentModel();
                $emp['position_components'] = $posCompModel->getByPosition($emp['position_id']);
                
                // If base salary not set in employee_salary, use position's base salary
                if (!$emp['base_salary']) {
                    $posModel = new \App\Models\PositionModel();
                    $pos = $posModel->find($emp['position_id']);
                    $emp['base_salary'] = $pos['base_salary'] ?? 0;
                    $emp['is_inherited_salary'] = true;
                }
            }
        }

        return view('payroll/employee_salary', $this->viewData([
            'title' => 'Gaji Pegawai',
            'employees' => $employees,
            'earningComponents' => $compModel->getEarnings(),
            'deductionComponents' => $compModel->getDeductions(),
        ]));
    }

    public function editEmployeeSalary($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $empSalaryCompModel = new EmployeeSalaryComponentModel();
        $compModel = new SalaryComponentModel();
        
        $emp = $this->employeeModel->select('employees.*, employee_salary.base_salary, departments.name as department_name, positions.name as position_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->join('employee_salary', 'employee_salary.employee_id = employees.id AND employee_salary.end_date IS NULL', 'left')
            ->where('employees.id', $id)
            ->first();

        if (!$emp) {
            return redirect()->to('/payroll/employee-salary')->with('error', 'Pegawai tidak ditemukan');
        }

        $emp['salary_components'] = $empSalaryCompModel->getEmployeeComponents($id);
        
        if ($emp['position_id']) {
            $posCompModel = new \App\Models\PositionSalaryComponentModel();
            $emp['position_components'] = $posCompModel->getByPosition($emp['position_id']);
            
            if (!$emp['base_salary']) {
                $posModel = new \App\Models\PositionModel();
                $pos = $posModel->find($emp['position_id']);
                $emp['base_salary'] = $pos['base_salary'] ?? 0;
                $emp['is_inherited_salary'] = true;
            }
        }

        return view('payroll/edit_employee_salary', $this->viewData([
            'title' => 'Atur Gaji Pegawai',
            'emp' => $emp,
            'earningComponents' => $compModel->getEarnings(),
            'deductionComponents' => $compModel->getDeductions(),
        ]));
    }

    public function updateEmployeeSalary()
    {
        if (!$this->isLoggedIn()) {
            if ($this->request->isAJAX()) {
                return $this->errorResponse('Unauthorized', 401);
            }
            return redirect()->to('/auth');
        }

        $employeeId = $this->request->getPost('employee_id');
        $baseSalary = $this->request->getPost('base_salary');
        $effectiveDate = $this->request->getPost('effective_date') ?? date('Y-m-d');
        $components = $this->request->getPost('components') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update Bank Info in employees table
            $this->employeeModel->update($employeeId, [
                'bank_name' => $this->request->getPost('bank_name'),
                'bank_account' => $this->request->getPost('bank_account'),
                'bank_holder' => $this->request->getPost('bank_holder'),
            ]);

            // Update Base Salary (only if provided and not empty)
            if ($baseSalary !== null && $baseSalary !== '') {
                $employeeSalaryModel = new EmployeeSalaryModel();
                $employeeSalaryModel->updateSalary($employeeId, $baseSalary, $effectiveDate);
            }

            // Update components ONLY if they are explicitly sent in the request
            if ($this->request->getPost('components') !== null) {
                $empSalaryCompModel = new EmployeeSalaryComponentModel();
                
                // End all current components for this employee
                $db->table('employee_salary_components')
                    ->where('employee_id', $employeeId)
                    ->where('end_date IS NULL')
                    ->update(['end_date' => date('Y-m-d', strtotime($effectiveDate . ' -1 day'))]);

                foreach ($components as $compId => $data) {
                    if (isset($data['enabled']) && $data['enabled'] == 1) {
                        $empSalaryCompModel->insert([
                            'employee_id' => $employeeId,
                            'component_id' => $compId,
                            'value' => $data['amount'] ?? 0,
                            'effective_date' => $effectiveDate,
                            'end_date' => null
                        ]);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                if ($this->request->isAJAX()) {
                    return $this->errorResponse('Gagal menyimpan pengaturan gaji.');
                }
                return redirect()->back()->with('error', 'Gagal menyimpan pengaturan gaji.');
            }

            if ($this->request->isAJAX()) {
                return $this->successResponse(null, 'Gaji berhasil diupdate');
            }
            return redirect()->to('/payroll/employee-salary')->with('success', 'Gaji berhasil diupdate');
            
        } catch (\Exception $e) {
            $db->transRollback();
            if ($this->request->isAJAX()) {
                return $this->errorResponse('Terjadi kesalahan: ' . $e->getMessage());
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');

        $payrolls = $this->payrollModel->getByPeriod($month, $year);

        // Generate CSV
        $filename = 'payroll_' . $year . $month . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, ['Kode', 'Nama', 'Departemen', 'Gaji Pokok', 'Total Tunjangan', 'Total Potongan', 'Gaji Kotor', 'Pajak', 'Gaji Bersih', 'Status']);
        
        foreach ($payrolls as $payroll) {
            fputcsv($output, [
                $payroll['employee_code'],
                $payroll['full_name'],
                $payroll['department_name'],
                $payroll['base_salary'],
                $payroll['total_earnings'],
                $payroll['total_deductions'],
                $payroll['gross_salary'],
                $payroll['tax_amount'],
                $payroll['net_salary'],
                $payroll['status'],
            ]);
        }
        
        fclose($output);
        exit;
    }
}
