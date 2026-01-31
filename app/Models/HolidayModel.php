<?php

namespace App\Models;

use CodeIgniter\Model;

class HolidayModel extends Model
{
    protected $table = 'holidays';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['date', 'name', 'description', 'is_national'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'date' => 'required|valid_date',
        'name' => 'required|min_length[2]|max_length[100]',
    ];

    public function getByYear($year)
    {
        return $this->where('YEAR(date)', $year)
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    public function getByMonth($month, $year)
    {
        return $this->where('MONTH(date)', $month)
            ->where('YEAR(date)', $year)
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    public function isHoliday($date)
    {
        return $this->where('date', $date)->first() !== null;
    }

    public function getHolidayDates($startDate, $endDate)
    {
        $holidays = $this->where('date >=', $startDate)
            ->where('date <=', $endDate)
            ->findAll();
        
        return array_column($holidays, 'date');
    }
}
