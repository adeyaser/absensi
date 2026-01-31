<?php

namespace App\Libraries;

use App\Models\SettingModel;

class PayrollCalculator
{
    protected $settingModel;
    protected $settings;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
        $this->settings = $this->settingModel->getAllAsArray();
    }

    /**
     * Calculate payroll for an employee
     */
    public function calculate($employeeId, $month, $year)
    {
        $employeeModel = new \App\Models\EmployeeModel();
        $employeeSalaryModel = new \App\Models\EmployeeSalaryModel();
        $employeeSalaryComponentModel = new \App\Models\EmployeeSalaryComponentModel();
        $attendanceModel = new \App\Models\AttendanceModel();
        $overtimeModel = new \App\Models\OvertimeRequestModel();
        $salaryComponentModel = new \App\Models\SalaryComponentModel();
        $posCompModel = new \App\Models\PositionSalaryComponentModel();

        // Get employee details (including position_id)
        $employee = $employeeModel->find($employeeId);
        $positionId = $employee['position_id'] ?? null;

        // Get base salary (priority: employee_salary table, fallback: positions table)
        $salary = $employeeSalaryModel->getCurrentSalary($employeeId);
        $baseSalary = 0;
        if ($salary && $salary['base_salary'] > 0) {
            $baseSalary = (float) $salary['base_salary'];
        } elseif ($positionId) {
            $posModel = new \App\Models\PositionModel();
            $position = $posModel->find($positionId);
            $baseSalary = $position ? (float) $position['base_salary'] : 0;
        }

        // Get attendance recap
        $attendanceRecap = $attendanceModel->getMonthlyRecap($employeeId, $month, $year);
        
        // Get overtime hours
        $overtimeHours = $overtimeModel->getTotalOvertimeHours($employeeId, $month, $year);

        // Get component values
        // Priority: 1. Employee-specific, 2. Position-specific, 3. Default (fixed)
        $componentValues = [];
        
        // 1. Load Position-specific components first
        if ($positionId) {
            $posComponents = $posCompModel->getByPosition($positionId);
            foreach ($posComponents as $pc) {
                $componentValues[$pc['component_id']] = (float) $pc['amount'];
            }
        }

        // 2. Load Employee-specific components (overrides position)
        $customComponents = $employeeSalaryComponentModel->getEmployeeComponents($employeeId);
        foreach ($customComponents as $comp) {
            $componentValues[$comp['component_id']] = (float) $comp['value'];
        }

        // Get all active salary components
        $components = $salaryComponentModel->getActive();

        // Prepare calculation variables
        $workDays = (int) ($this->settings['work_days_per_month'] ?? 22);
        $variables = [
            'base_salary' => $baseSalary,
            'work_days' => $workDays,
            'present_days' => $attendanceRecap['present'],
            'absent_days' => $attendanceRecap['absent'],
            'late_days' => $attendanceRecap['late'],
            'late_minutes' => $attendanceRecap['total_late_minutes'],
            'overtime_hours' => $overtimeHours,
            'leave_days' => $attendanceRecap['leave'],
            'sick_days' => $attendanceRecap['sick'],
        ];

        // Calculate earnings
        $earnings = [];
        $totalEarnings = 0;
        $grossSalary = $baseSalary;

        foreach ($components as $component) {
            if ($component['type'] !== 'earning') continue;
            
            $amount = $this->calculateComponent($component, $variables, $componentValues);
            
            if ($amount > 0) {
                $earnings[] = [
                    'component_id' => $component['id'],
                    'component_name' => $component['name'],
                    'component_type' => 'earning',
                    'amount' => $amount,
                    'is_taxable' => $component['is_taxable'],
                ];
                $totalEarnings += $amount;
                
                if ($component['code'] !== 'BASIC') {
                    $grossSalary += $amount;
                }
            }
        }

        // Hitung Tunjangan Lembur (Overtime)
        if ($overtimeHours > 0) {
            // Rumus standar Depnaker: 1/173 * Upah Sebulan
            $overtimeAmount = ($baseSalary / 173) * $overtimeHours;
            $overtimeAmount = floor($overtimeAmount); // Pembulatan ke bawah

            $earnings[] = [
                'component_id' => 0,
                'component_name' => "Tunjangan Lembur ($overtimeHours jam)",
                'component_type' => 'earning',
                'amount' => $overtimeAmount,
                'is_taxable' => 1,
            ];
            
            $totalEarnings += $overtimeAmount;
            $grossSalary += $overtimeAmount;
        }

        $variables['gross_salary'] = $grossSalary;

        // Calculate deductions
        $deductions = [];
        $totalDeductions = 0;

        foreach ($components as $component) {
            if ($component['type'] !== 'deduction') continue;
            if ($component['code'] === 'TAX') continue; // Calculate tax separately
            
            $amount = $this->calculateComponent($component, $variables, $componentValues);
            
            if ($amount > 0) {
                $deductions[] = [
                    'component_id' => $component['id'],
                    'component_name' => $component['name'],
                    'component_type' => 'deduction',
                    'amount' => $amount,
                    'is_taxable' => 0,
                ];
                $totalDeductions += $amount;
            }
        }

        // Calculate PPh 21 (simplified)
        $taxableIncome = $this->calculateTaxableIncome($earnings);
        $taxAmount = $this->calculatePPh21($taxableIncome);

        if ($taxAmount > 0) {
            $deductions[] = [
                'component_id' => 0,
                'component_name' => 'PPh 21',
                'component_type' => 'deduction',
                'amount' => $taxAmount,
                'is_taxable' => 0,
            ];
            $totalDeductions += $taxAmount;
        }

        // Calculate net salary
        $netSalary = $grossSalary - $totalDeductions;

        return [
            'employee_id' => $employeeId,
            'period_month' => $month,
            'period_year' => $year,
            'work_days' => $workDays,
            'present_days' => $attendanceRecap['present'],
            'absent_days' => $attendanceRecap['absent'],
            'late_days' => $attendanceRecap['late'],
            'late_minutes' => $attendanceRecap['total_late_minutes'],
            'leave_days' => $attendanceRecap['leave'],
            'sick_days' => $attendanceRecap['sick'],
            'overtime_hours' => $overtimeHours,
            'base_salary' => $baseSalary,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'gross_salary' => $grossSalary,
            'tax_amount' => $taxAmount,
            'net_salary' => $netSalary,
            'earnings' => $earnings,
            'deductions' => $deductions,
        ];
    }

    /**
     * Calculate component amount based on its type
     */
    protected function calculateComponent($component, $variables, $customValues = [])
    {
        // Check if there's a custom value for this employee
        if (isset($customValues[$component['id']])) {
            return $customValues[$component['id']];
        }

        switch ($component['calculation_type']) {
            case 'fixed':
                return (float) $component['default_value'];

            case 'percentage':
                $base = $variables[$component['percentage_base']] ?? 0;
                return ($base * $component['default_value']) / 100;

            case 'formula':
                return $this->evaluateFormula($component['formula'], $variables);

            default:
                return 0;
        }
    }

    /**
     * Evaluate formula with variables
     */
    protected function evaluateFormula($formula, $variables)
    {
        if (empty($formula)) return 0;

        // Replace variables in formula
        foreach ($variables as $key => $value) {
            $formula = str_replace($key, $value, $formula);
        }

        // Simple formula evaluation (for basic math operations)
        try {
            // Only allow safe characters
            if (preg_match('/^[0-9+\-*\/\(\)\.\s]+$/', $formula)) {
                return eval("return $formula;");
            }
        } catch (\Exception $e) {
            return 0;
        }

        return 0;
    }

    /**
     * Calculate taxable income from earnings
     */
    protected function calculateTaxableIncome($earnings)
    {
        $taxableIncome = 0;
        
        foreach ($earnings as $earning) {
            if ($earning['is_taxable']) {
                $taxableIncome += $earning['amount'];
            }
        }

        return $taxableIncome;
    }

    /**
     * Calculate PPh 21 (simplified Indonesian income tax)
     */
    protected function calculatePPh21($monthlyTaxableIncome)
    {
        // Annual taxable income
        $annualIncome = $monthlyTaxableIncome * 12;
        
        // PTKP (Tax-free income) for single person - TK/0
        $ptkp = 54000000;
        
        // Calculate PKP (Taxable income)
        $pkp = max(0, $annualIncome - $ptkp);
        
        if ($pkp <= 0) return 0;

        // Progressive tax rates (as of 2023)
        $tax = 0;
        
        if ($pkp > 0) {
            if ($pkp <= 60000000) {
                $tax = $pkp * 0.05;
            } elseif ($pkp <= 250000000) {
                $tax = 3000000 + (($pkp - 60000000) * 0.15);
            } elseif ($pkp <= 500000000) {
                $tax = 31500000 + (($pkp - 250000000) * 0.25);
            } elseif ($pkp <= 5000000000) {
                $tax = 94000000 + (($pkp - 500000000) * 0.30);
            } else {
                $tax = 1444000000 + (($pkp - 5000000000) * 0.35);
            }
        }

        // Return monthly tax
        return round($tax / 12);
    }
}
