<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SalaryComponentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Earnings
            [
                'code' => 'BASIC',
                'name' => 'Gaji Pokok',
                'type' => 'earning',
                'calculation_type' => 'fixed',
                'default_value' => 0,
                'is_taxable' => 1,
                'is_fixed' => 1,
                'order_num' => 1,
            ],
            [
                'code' => 'TRANSPORT',
                'name' => 'Tunjangan Transportasi',
                'type' => 'earning',
                'calculation_type' => 'fixed',
                'default_value' => 500000,
                'is_taxable' => 1,
                'is_fixed' => 1,
                'order_num' => 2,
            ],
            [
                'code' => 'MEAL',
                'name' => 'Tunjangan Makan',
                'type' => 'earning',
                'calculation_type' => 'fixed',
                'default_value' => 500000,
                'is_taxable' => 1,
                'is_fixed' => 1,
                'order_num' => 3,
            ],
            [
                'code' => 'POSITION',
                'name' => 'Tunjangan Jabatan',
                'type' => 'earning',
                'calculation_type' => 'fixed',
                'default_value' => 0,
                'is_taxable' => 1,
                'is_fixed' => 1,
                'order_num' => 4,
            ],
            [
                'code' => 'FAMILY',
                'name' => 'Tunjangan Keluarga',
                'type' => 'earning',
                'calculation_type' => 'percentage',
                'default_value' => 5,
                'percentage_base' => 'base_salary',
                'is_taxable' => 1,
                'is_fixed' => 1,
                'order_num' => 5,
            ],
            [
                'code' => 'HEALTH',
                'name' => 'Tunjangan Kesehatan',
                'type' => 'earning',
                'calculation_type' => 'fixed',
                'default_value' => 300000,
                'is_taxable' => 0,
                'is_fixed' => 1,
                'order_num' => 6,
            ],
            [
                'code' => 'OVERTIME',
                'name' => 'Uang Lembur',
                'type' => 'earning',
                'calculation_type' => 'formula',
                'default_value' => 0,
                'formula' => '(base_salary / 173) * 1.5 * overtime_hours',
                'is_taxable' => 1,
                'is_fixed' => 0,
                'order_num' => 7,
            ],
            [
                'code' => 'BONUS',
                'name' => 'Bonus',
                'type' => 'earning',
                'calculation_type' => 'fixed',
                'default_value' => 0,
                'is_taxable' => 1,
                'is_fixed' => 0,
                'order_num' => 8,
            ],
            [
                'code' => 'ATTENDANCE',
                'name' => 'Uang Kehadiran',
                'type' => 'earning',
                'calculation_type' => 'fixed',
                'default_value' => 300000,
                'is_taxable' => 1,
                'is_fixed' => 0,
                'order_num' => 9,
            ],
            
            // Deductions
            [
                'code' => 'BPJS_KES',
                'name' => 'BPJS Kesehatan',
                'type' => 'deduction',
                'calculation_type' => 'percentage',
                'default_value' => 1,
                'percentage_base' => 'gross_salary',
                'is_taxable' => 0,
                'is_fixed' => 1,
                'order_num' => 20,
            ],
            [
                'code' => 'BPJS_JHT',
                'name' => 'BPJS Jaminan Hari Tua',
                'type' => 'deduction',
                'calculation_type' => 'percentage',
                'default_value' => 2,
                'percentage_base' => 'gross_salary',
                'is_taxable' => 0,
                'is_fixed' => 1,
                'order_num' => 21,
            ],
            [
                'code' => 'BPJS_JP',
                'name' => 'BPJS Jaminan Pensiun',
                'type' => 'deduction',
                'calculation_type' => 'percentage',
                'default_value' => 1,
                'percentage_base' => 'gross_salary',
                'is_taxable' => 0,
                'is_fixed' => 1,
                'order_num' => 22,
            ],
            [
                'code' => 'TAX',
                'name' => 'PPh 21',
                'type' => 'deduction',
                'calculation_type' => 'formula',
                'default_value' => 0,
                'formula' => 'pph21',
                'is_taxable' => 0,
                'is_fixed' => 0,
                'order_num' => 23,
            ],
            [
                'code' => 'LATE',
                'name' => 'Potongan Keterlambatan',
                'type' => 'deduction',
                'calculation_type' => 'formula',
                'default_value' => 0,
                'formula' => '(base_salary / 173 / 60) * late_minutes',
                'is_taxable' => 0,
                'is_fixed' => 0,
                'order_num' => 24,
            ],
            [
                'code' => 'ABSENT',
                'name' => 'Potongan Tidak Hadir',
                'type' => 'deduction',
                'calculation_type' => 'formula',
                'default_value' => 0,
                'formula' => '(base_salary / work_days) * absent_days',
                'is_taxable' => 0,
                'is_fixed' => 0,
                'order_num' => 25,
            ],
            [
                'code' => 'LOAN',
                'name' => 'Potongan Pinjaman',
                'type' => 'deduction',
                'calculation_type' => 'fixed',
                'default_value' => 0,
                'is_taxable' => 0,
                'is_fixed' => 0,
                'order_num' => 26,
            ],
            [
                'code' => 'OTHER_DED',
                'name' => 'Potongan Lain-lain',
                'type' => 'deduction',
                'calculation_type' => 'fixed',
                'default_value' => 0,
                'is_taxable' => 0,
                'is_fixed' => 0,
                'order_num' => 27,
            ],
        ];

        foreach ($data as $row) {
            $row['is_active'] = 1;
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('salary_components')->insert($row);
        }
    }
}
