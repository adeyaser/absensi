<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionSalaryComponentModel extends Model
{
    protected $table = 'position_salary_components';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['position_id', 'component_id', 'amount'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getByPosition($positionId)
    {
        return $this->select('position_salary_components.*, salary_components.name, salary_components.type, salary_components.calculation_type')
            ->join('salary_components', 'salary_components.id = position_salary_components.component_id')
            ->where('position_id', $positionId)
            ->findAll();
    }

    public function deleteByPosition($positionId)
    {
        return $this->where('position_id', $positionId)->delete();
    }
}
