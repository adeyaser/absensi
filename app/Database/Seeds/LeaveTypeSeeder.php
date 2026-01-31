<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'code' => 'ANNUAL',
                'name' => 'Cuti Tahunan',
                'quota' => 12,
                'is_paid' => 1,
                'is_deductible' => 0,
                'description' => 'Cuti tahunan untuk pegawai tetap',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'SICK',
                'name' => 'Cuti Sakit',
                'quota' => 0,
                'is_paid' => 1,
                'is_deductible' => 0,
                'description' => 'Cuti karena sakit dengan surat dokter',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'MATERNITY',
                'name' => 'Cuti Melahirkan',
                'quota' => 90,
                'is_paid' => 1,
                'is_deductible' => 0,
                'description' => 'Cuti melahirkan untuk pegawai wanita',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'PATERNITY',
                'name' => 'Cuti Ayah',
                'quota' => 3,
                'is_paid' => 1,
                'is_deductible' => 0,
                'description' => 'Cuti untuk pegawai pria yang istrinya melahirkan',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'MARRIAGE',
                'name' => 'Cuti Nikah',
                'quota' => 3,
                'is_paid' => 1,
                'is_deductible' => 0,
                'description' => 'Cuti pernikahan',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'BEREAVEMENT',
                'name' => 'Cuti Duka',
                'quota' => 3,
                'is_paid' => 1,
                'is_deductible' => 0,
                'description' => 'Cuti karena keluarga meninggal',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'UNPAID',
                'name' => 'Cuti Tidak Dibayar',
                'quota' => 0,
                'is_paid' => 0,
                'is_deductible' => 1,
                'description' => 'Cuti tanpa gaji',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'PERMIT',
                'name' => 'Izin',
                'quota' => 0,
                'is_paid' => 1,
                'is_deductible' => 0,
                'description' => 'Izin keperluan pribadi',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('leave_types')->insertBatch($data);
    }
}
