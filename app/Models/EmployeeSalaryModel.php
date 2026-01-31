<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeSalaryModel extends Model
{
    protected $table = 'employee_salary';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['employee_id', 'base_salary', 'effective_date', 'end_date', 'notes'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getCurrentSalary($employeeId, $date = null)
    {
        $date = $date ?? date('Y-m-d');
        
        return $this->where('employee_id', $employeeId)
            ->where('effective_date <=', $date)
            ->groupStart()
            ->where('end_date >=', $date)
            ->orWhere('end_date IS NULL')
            ->groupEnd()
            ->orderBy('effective_date', 'DESC')
            ->first();
    }

    public function getSalaryHistory($employeeId)
    {
        return $this->where('employee_id', $employeeId)
            ->orderBy('effective_date', 'DESC')
            ->findAll();
    }

    public function updateSalary($employeeId, $baseSalary, $effectiveDate, $notes = null)
    {
        // End current salary
        $currentSalary = $this->getCurrentSalary($employeeId);
        if ($currentSalary) {
            $this->update($currentSalary['id'], [
                'end_date' => date('Y-m-d', strtotime($effectiveDate . ' -1 day'))
            ]);
        }

        // Insert new salary
        return $this->insert([
            'employee_id' => $employeeId,
            'base_salary' => $baseSalary,
            'effective_date' => $effectiveDate,
            'notes' => $notes,
        ]);
    }
}
