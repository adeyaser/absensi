<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeSalaryComponentModel extends Model
{
    protected $table = 'employee_salary_components';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['employee_id', 'component_id', 'value', 'effective_date', 'end_date'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getEmployeeComponents($employeeId, $date = null)
    {
        $date = $date ?? date('Y-m-d');
        
        return $this->select('employee_salary_components.*, salary_components.code, salary_components.name, salary_components.type, salary_components.calculation_type, salary_components.is_taxable')
            ->join('salary_components', 'salary_components.id = employee_salary_components.component_id')
            ->where('employee_salary_components.employee_id', $employeeId)
            ->where('employee_salary_components.effective_date <=', $date)
            ->groupStart()
            ->where('employee_salary_components.end_date >=', $date)
            ->orWhere('employee_salary_components.end_date IS NULL')
            ->groupEnd()
            ->findAll();
    }

    public function updateComponent($employeeId, $componentId, $value, $effectiveDate)
    {
        // End current component value
        $current = $this->where('employee_id', $employeeId)
            ->where('component_id', $componentId)
            ->where('end_date IS NULL')
            ->first();
        
        if ($current) {
            $this->update($current['id'], [
                'end_date' => date('Y-m-d', strtotime($effectiveDate . ' -1 day'))
            ]);
        }

        // Insert new value
        return $this->insert([
            'employee_id' => $employeeId,
            'component_id' => $componentId,
            'value' => $value,
            'effective_date' => $effectiveDate,
        ]);
    }

    public function bulkUpdateComponents($employeeId, $components, $effectiveDate)
    {
        foreach ($components as $componentId => $value) {
            $this->updateComponent($employeeId, $componentId, $value, $effectiveDate);
        }
        return true;
    }
}
