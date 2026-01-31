<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\DepartmentModel;
use App\Models\AttendanceModel;
use App\Models\PayrollModel;
use App\Models\PayrollDetailModel;

class Reports extends BaseController
{
    protected $employeeModel;
    protected $departmentModel;
    protected $attendanceModel;
    protected $payrollModel;
    protected $payrollDetailModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
        $this->departmentModel = new DepartmentModel();
        $this->attendanceModel = new AttendanceModel();
        $this->payrollModel = new PayrollModel();
        $this->payrollDetailModel = new PayrollDetailModel();
    }

    /**
     * Attendance Report
     */
    public function attendance()
    {
        $month = $this->request->getGet('month') ?? date('n');
        $year = $this->request->getGet('year') ?? date('Y');
        $departmentId = $this->request->getGet('department_id');

        // Get departments for filter
        $departments = $this->departmentModel->findAll();

        // Build employee query
        $employeeQuery = $this->employeeModel
            ->select('employees.*, departments.name as department_name, departments.code as department_code')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->where('employees.employment_status', 'active');

        if ($departmentId) {
            $employeeQuery->where('employees.department_id', $departmentId);
        }

        $employees = $employeeQuery->findAll();

        // Get attendance data for the month
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        $daysInMonth = (int)date('t', strtotime($startDate));

        $report = [];
        $totalPresent = 0;
        $totalLate = 0;
        $totalAbsent = 0;
        $totalLeave = 0;

        foreach ($employees as $emp) {
            $attendances = $this->attendanceModel
                ->where('employee_id', $emp['id'])
                ->where('date >=', $startDate)
                ->where('date <=', $endDate)
                ->findAll();

            // Index attendance by date
            $attendanceByDate = [];
            foreach ($attendances as $att) {
                $day = (int)date('j', strtotime($att['date']));
                $attendanceByDate[$day] = $att['status'];
            }

            $row = [
                'employee_id' => $emp['employee_id'],
                'employee_name' => $emp['full_name'],
                'department_code' => $emp['department_code'],
                'days' => [],
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'leave' => 0,
                'attendance_rate' => 0,
            ];

            $workDays = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = mktime(0, 0, 0, $month, $d, $year);
                $dayOfWeek = date('w', $date);
                $isWeekend = in_array($dayOfWeek, [0, 6]);

                if ($isWeekend) {
                    $row['days'][$d] = 'W';
                } elseif (isset($attendanceByDate[$d])) {
                    $status = $attendanceByDate[$d];
                    if ($status === 'present') {
                        $row['days'][$d] = 'H';
                        $row['present']++;
                        $totalPresent++;
                    } elseif ($status === 'late') {
                        $row['days'][$d] = 'T';
                        $row['late']++;
                        $row['present']++; // Late counts as present
                        $totalLate++;
                    } elseif ($status === 'leave') {
                        $row['days'][$d] = 'C';
                        $row['leave']++;
                        $totalLeave++;
                    } else {
                        $row['days'][$d] = 'A';
                        $row['absent']++;
                        $totalAbsent++;
                    }
                    $workDays++;
                } else {
                    // Check if date is in past
                    if ($date <= time()) {
                        $row['days'][$d] = 'A';
                        $row['absent']++;
                        $totalAbsent++;
                        $workDays++;
                    } else {
                        $row['days'][$d] = '';
                    }
                }
            }

            if ($workDays > 0) {
                $row['attendance_rate'] = ($row['present'] / $workDays) * 100;
            }

            $report[] = $row;
        }

        // Calculate summary
        $totalEmployees = count($employees);
        $avgAttendance = $totalEmployees > 0 && ($totalPresent + $totalAbsent + $totalLeave) > 0
            ? ($totalPresent / ($totalPresent + $totalAbsent + $totalLeave)) * 100
            : 0;

        $summary = [
            'total_employees' => $totalEmployees,
            'avg_attendance' => $avgAttendance,
            'total_late' => $totalLate,
            'total_overtime' => 0, // TODO: Calculate overtime hours
        ];

        // Export to Excel
        if ($this->request->getGet('export') === 'excel') {
            return $this->exportAttendanceExcel($report, $month, $year);
        }

        return view('reports/attendance', [
            'report' => $report,
            'summary' => $summary,
            'departments' => $departments,
            'month' => $month,
            'year' => $year,
            'department_id' => $departmentId,
        ]);
    }

    /**
     * Payroll Report
     */
    public function payroll()
    {
        $month = $this->request->getGet('month') ?? date('n');
        $year = $this->request->getGet('year') ?? date('Y');
        $departmentId = $this->request->getGet('department_id');

        $departments = $this->departmentModel->findAll();

        // Build query
        $query = $this->payrollModel
            ->select('payrolls.*, employees.full_name as employee_name, employees.employee_id, 
                      departments.name as department')
            ->join('employees', 'employees.id = payrolls.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->where('payrolls.period_month', $month)
            ->where('payrolls.period_year', $year);

        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }

        $payrolls = $query->findAll();

        $report = [];
        $totals = [
            'base_salary' => 0,
            'allowances' => 0,
            'overtime' => 0,
            'gross_salary' => 0,
            'bpjs' => 0,
            'pph21' => 0,
            'other_deductions' => 0,
            'net_salary' => 0,
        ];

        foreach ($payrolls as $payroll) {
            // Get payroll details
            $details = $this->payrollDetailModel
                ->select('payroll_details.*, salary_components.name, salary_components.type, salary_components.code')
                ->join('salary_components', 'salary_components.id = payroll_details.component_id')
                ->where('payroll_id', $payroll['id'])
                ->findAll();

            $allowances = 0;
            $overtime = 0;
            $bpjs = 0;
            $otherDeductions = 0;

            foreach ($details as $detail) {
                if ($detail['type'] === 'earning') {
                    if (strpos(strtolower($detail['code']), 'lembur') !== false 
                        || strpos(strtolower($detail['code']), 'overtime') !== false) {
                        $overtime += $detail['amount'];
                    } else {
                        $allowances += $detail['amount'];
                    }
                } else {
                    if (strpos(strtolower($detail['code']), 'bpjs') !== false) {
                        $bpjs += $detail['amount'];
                    } else {
                        $otherDeductions += $detail['amount'];
                    }
                }
            }

            $row = [
                'employee_id' => $payroll['employee_id'],
                'employee_name' => $payroll['employee_name'],
                'department' => $payroll['department'],
                'base_salary' => $payroll['base_salary'],
                'allowances' => $allowances,
                'overtime' => $overtime,
                'gross_salary' => $payroll['gross_salary'],
                'bpjs' => $bpjs,
                'pph21' => $payroll['pph21'],
                'other_deductions' => $otherDeductions,
                'net_salary' => $payroll['net_salary'],
                'status' => $payroll['status'],
            ];

            $report[] = $row;

            // Update totals
            $totals['base_salary'] += $payroll['base_salary'];
            $totals['allowances'] += $allowances;
            $totals['overtime'] += $overtime;
            $totals['gross_salary'] += $payroll['gross_salary'];
            $totals['bpjs'] += $bpjs;
            $totals['pph21'] += $payroll['pph21'];
            $totals['other_deductions'] += $otherDeductions;
            $totals['net_salary'] += $payroll['net_salary'];
        }

        // Summary
        $summary = [
            'total_employees' => count($payrolls),
            'total_gross' => $totals['gross_salary'],
            'total_deductions' => $totals['bpjs'] + $totals['pph21'] + $totals['other_deductions'],
            'total_net' => $totals['net_salary'],
        ];

        // Department totals for chart
        $departmentTotals = [];
        $deptGroups = [];
        foreach ($report as $row) {
            $dept = $row['department'] ?? 'Lainnya';
            if (!isset($deptGroups[$dept])) {
                $deptGroups[$dept] = 0;
            }
            $deptGroups[$dept] += $row['net_salary'];
        }
        foreach ($deptGroups as $name => $total) {
            $departmentTotals[] = ['name' => $name, 'total' => $total];
        }

        // Export to Excel
        if ($this->request->getGet('export') === 'excel') {
            return $this->exportPayrollExcel($report, $month, $year);
        }

        return view('reports/payroll', [
            'report' => $report,
            'totals' => $totals,
            'summary' => $summary,
            'departmentTotals' => $departmentTotals,
            'departments' => $departments,
            'month' => $month,
            'year' => $year,
            'department_id' => $departmentId,
        ]);
    }

    /**
     * Employees Report
     */
    public function employees()
    {
        $departmentId = $this->request->getGet('department_id');
        $status = $this->request->getGet('status');

        $departments = $this->departmentModel->findAll();

        $query = $this->employeeModel
            ->select('employees.*, departments.name as department_name, positions.name as position_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left');

        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }

        if ($status) {
            $query->where('employees.employment_status', $status);
        }

        $employees = $query->findAll();

        // Summary
        $summary = [
            'total' => count($employees),
            'active' => 0,
            'inactive' => 0,
            'by_department' => [],
            'by_gender' => ['male' => 0, 'female' => 0],
            'by_contract' => [],
        ];

        foreach ($employees as $emp) {
            if ($emp['employment_status'] === 'active') {
                $summary['active']++;
            } else {
                $summary['inactive']++;
            }

            $dept = $emp['department_name'] ?? 'Lainnya';
            if (!isset($summary['by_department'][$dept])) {
                $summary['by_department'][$dept] = 0;
            }
            $summary['by_department'][$dept]++;

            if ($emp['gender'] === 'male') {
                $summary['by_gender']['male']++;
            } else {
                $summary['by_gender']['female']++;
            }

            $contract = $emp['contract_type'] ?? 'unknown';
            if (!isset($summary['by_contract'][$contract])) {
                $summary['by_contract'][$contract] = 0;
            }
            $summary['by_contract'][$contract]++;
        }

        // Export to Excel
        if ($this->request->getGet('export') === 'excel') {
            return $this->exportEmployeesExcel($employees);
        }

        return view('reports/employees', [
            'employees' => $employees,
            'summary' => $summary,
            'departments' => $departments,
            'department_id' => $departmentId,
            'status' => $status,
        ]);
    }

    /**
     * Export attendance to Excel (CSV)
     */
    private function exportAttendanceExcel($report, $month, $year)
    {
        $filename = sprintf('attendance_report_%04d_%02d.csv', $year, $month);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Headers
        $headers = ['No', 'Employee ID', 'Name', 'Department', 'Present', 'Absent', 'Late', 'Leave', 'Rate (%)'];
        fputcsv($output, $headers);

        // Data
        foreach ($report as $i => $row) {
            fputcsv($output, [
                $i + 1,
                $row['employee_id'],
                $row['employee_name'],
                $row['department_code'],
                $row['present'],
                $row['absent'],
                $row['late'],
                $row['leave'],
                number_format($row['attendance_rate'], 1),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export payroll to Excel (CSV)
     */
    private function exportPayrollExcel($report, $month, $year)
    {
        $filename = sprintf('payroll_report_%04d_%02d.csv', $year, $month);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Headers
        $headers = ['No', 'Employee ID', 'Name', 'Department', 'Base Salary', 'Allowances', 'Overtime',
                    'Gross Salary', 'BPJS', 'PPh21', 'Other Deductions', 'Net Salary', 'Status'];
        fputcsv($output, $headers);

        // Data
        foreach ($report as $i => $row) {
            fputcsv($output, [
                $i + 1,
                $row['employee_id'],
                $row['employee_name'],
                $row['department'],
                $row['base_salary'],
                $row['allowances'],
                $row['overtime'],
                $row['gross_salary'],
                $row['bpjs'],
                $row['pph21'],
                $row['other_deductions'],
                $row['net_salary'],
                $row['status'],
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export employees to Excel (CSV)
     */
    private function exportEmployeesExcel($employees)
    {
        $filename = 'employees_report_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Headers
        $headers = ['No', 'Employee ID', 'Name', 'NIK', 'Gender', 'Email', 'Phone',
                    'Department', 'Position', 'Join Date', 'Contract Type', 'Status'];
        fputcsv($output, $headers);

        // Data
        foreach ($employees as $i => $emp) {
            fputcsv($output, [
                $i + 1,
                $emp['employee_id'],
                $emp['full_name'],
                $emp['nik'],
                $emp['gender'],
                $emp['email'],
                $emp['phone'],
                $emp['department_name'],
                $emp['position_name'],
                $emp['join_date'],
                $emp['contract_type'],
                $emp['employment_status'],
            ]);
        }

        fclose($output);
        exit;
    }
}
