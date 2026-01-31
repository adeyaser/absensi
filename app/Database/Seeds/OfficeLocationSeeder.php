<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OfficeLocationSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'address' => 'Jl. Sudirman No. 1, Jakarta Pusat',
                'latitude' => -6.20876,
                'longitude' => 106.84559,
                'radius' => 100,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Kantor Cabang Bandung',
                'address' => 'Jl. Braga No. 10, Bandung',
                'latitude' => -6.91747,
                'longitude' => 107.61912,
                'radius' => 100,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Kantor Cabang Surabaya',
                'address' => 'Jl. Tunjungan No. 5, Surabaya',
                'latitude' => -7.25764,
                'longitude' => 112.75209,
                'radius' => 100,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('office_locations')->insertBatch($data);
    }
}
