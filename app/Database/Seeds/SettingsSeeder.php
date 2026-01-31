<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Company Settings
            ['key' => 'company_name', 'value' => 'PT. Absesi Indonesia', 'type' => 'text', 'group' => 'company', 'label' => 'Nama Perusahaan'],
            ['key' => 'company_address', 'value' => 'Jl. Sudirman No. 1, Jakarta Pusat', 'type' => 'textarea', 'group' => 'company', 'label' => 'Alamat Perusahaan'],
            ['key' => 'company_phone', 'value' => '021-12345678', 'type' => 'text', 'group' => 'company', 'label' => 'Telepon'],
            ['key' => 'company_email', 'value' => 'info@absesi.com', 'type' => 'email', 'group' => 'company', 'label' => 'Email'],
            ['key' => 'company_logo', 'value' => null, 'type' => 'image', 'group' => 'company', 'label' => 'Logo Perusahaan'],
            
            // Attendance Settings
            ['key' => 'attendance_require_photo', 'value' => '1', 'type' => 'boolean', 'group' => 'attendance', 'label' => 'Wajib Foto Saat Absen'],
            ['key' => 'attendance_require_location', 'value' => '1', 'type' => 'boolean', 'group' => 'attendance', 'label' => 'Wajib Lokasi GPS'],
            ['key' => 'attendance_require_face', 'value' => '1', 'type' => 'boolean', 'group' => 'attendance', 'label' => 'Validasi Wajah'],
            ['key' => 'attendance_location_strict', 'value' => '1', 'type' => 'boolean', 'group' => 'attendance', 'label' => 'Wajib di Lokasi Kantor'],
            ['key' => 'attendance_early_clock_in', 'value' => '60', 'type' => 'number', 'group' => 'attendance', 'label' => 'Absen Masuk Lebih Awal (menit)'],
            
            // Payroll Settings
            ['key' => 'payroll_period_start', 'value' => '1', 'type' => 'number', 'group' => 'payroll', 'label' => 'Tanggal Mulai Periode'],
            ['key' => 'payroll_period_end', 'value' => '31', 'type' => 'number', 'group' => 'payroll', 'label' => 'Tanggal Akhir Periode'],
            ['key' => 'payroll_pay_date', 'value' => '25', 'type' => 'number', 'group' => 'payroll', 'label' => 'Tanggal Gajian'],
            ['key' => 'overtime_rate_weekday', 'value' => '1.5', 'type' => 'number', 'group' => 'payroll', 'label' => 'Rate Lembur Hari Kerja'],
            ['key' => 'overtime_rate_weekend', 'value' => '2', 'type' => 'number', 'group' => 'payroll', 'label' => 'Rate Lembur Hari Libur'],
            ['key' => 'work_hours_per_day', 'value' => '8', 'type' => 'number', 'group' => 'payroll', 'label' => 'Jam Kerja Per Hari'],
            ['key' => 'work_days_per_month', 'value' => '22', 'type' => 'number', 'group' => 'payroll', 'label' => 'Hari Kerja Per Bulan'],
            
            // BPJS Settings
            ['key' => 'bpjs_kesehatan_company', 'value' => '4', 'type' => 'number', 'group' => 'bpjs', 'label' => 'BPJS Kesehatan Perusahaan (%)'],
            ['key' => 'bpjs_kesehatan_employee', 'value' => '1', 'type' => 'number', 'group' => 'bpjs', 'label' => 'BPJS Kesehatan Karyawan (%)'],
            ['key' => 'bpjs_jht_company', 'value' => '3.7', 'type' => 'number', 'group' => 'bpjs', 'label' => 'BPJS JHT Perusahaan (%)'],
            ['key' => 'bpjs_jht_employee', 'value' => '2', 'type' => 'number', 'group' => 'bpjs', 'label' => 'BPJS JHT Karyawan (%)'],
            ['key' => 'bpjs_jp_company', 'value' => '2', 'type' => 'number', 'group' => 'bpjs', 'label' => 'BPJS JP Perusahaan (%)'],
            ['key' => 'bpjs_jp_employee', 'value' => '1', 'type' => 'number', 'group' => 'bpjs', 'label' => 'BPJS JP Karyawan (%)'],
            ['key' => 'bpjs_jkk', 'value' => '0.24', 'type' => 'number', 'group' => 'bpjs', 'label' => 'BPJS JKK (%)'],
            ['key' => 'bpjs_jkm', 'value' => '0.3', 'type' => 'number', 'group' => 'bpjs', 'label' => 'BPJS JKM (%)'],
        ];

        foreach ($data as $row) {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('settings')->insert($row);
        }
    }
}
