<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Dashboard
            ['id' => 1, 'parent_id' => 0, 'title' => 'Dashboard', 'icon' => 'fas fa-home', 'url' => '/dashboard', 'route' => 'dashboard', 'order_num' => 1],
            
            // Master Data
            ['id' => 2, 'parent_id' => 0, 'title' => 'Master Data', 'icon' => 'fas fa-database', 'url' => '#', 'route' => null, 'order_num' => 2],
            ['id' => 3, 'parent_id' => 2, 'title' => 'Departemen', 'icon' => 'fas fa-building', 'url' => '/master/departments', 'route' => 'master.departments', 'order_num' => 1],
            ['id' => 4, 'parent_id' => 2, 'title' => 'Jabatan', 'icon' => 'fas fa-user-tie', 'url' => '/master/positions', 'route' => 'master.positions', 'order_num' => 2],
            ['id' => 5, 'parent_id' => 2, 'title' => 'Jadwal Kerja', 'icon' => 'fas fa-clock', 'url' => '/master/schedules', 'route' => 'master.schedules', 'order_num' => 3],
            ['id' => 6, 'parent_id' => 2, 'title' => 'Lokasi Kantor', 'icon' => 'fas fa-map-marker-alt', 'url' => '/master/locations', 'route' => 'master.locations', 'order_num' => 4],
            ['id' => 7, 'parent_id' => 2, 'title' => 'Hari Libur', 'icon' => 'fas fa-calendar-alt', 'url' => '/master/holidays', 'route' => 'master.holidays', 'order_num' => 5],
            ['id' => 8, 'parent_id' => 2, 'title' => 'Jenis Cuti', 'icon' => 'fas fa-calendar-check', 'url' => '/master/leave-types', 'route' => 'master.leave-types', 'order_num' => 6],
            
            // Employee Management
            ['id' => 10, 'parent_id' => 0, 'title' => 'Kepegawaian', 'icon' => 'fas fa-users', 'url' => '#', 'route' => null, 'order_num' => 3],
            ['id' => 11, 'parent_id' => 10, 'title' => 'Data Pegawai', 'icon' => 'fas fa-user', 'url' => '/employees', 'route' => 'employees', 'order_num' => 1],
            ['id' => 12, 'parent_id' => 10, 'title' => 'Jadwal Pegawai', 'icon' => 'fas fa-calendar', 'url' => '/employees/schedules', 'route' => 'employees.schedules', 'order_num' => 2],
            
            // Attendance
            ['id' => 20, 'parent_id' => 0, 'title' => 'Absensi', 'icon' => 'fas fa-fingerprint', 'url' => '#', 'route' => null, 'order_num' => 4],
            ['id' => 21, 'parent_id' => 20, 'title' => 'Absen Masuk/Pulang', 'icon' => 'fas fa-sign-in-alt', 'url' => '/attendance/clock', 'route' => 'attendance.clock', 'order_num' => 1],
            ['id' => 22, 'parent_id' => 20, 'title' => 'Riwayat Absensi', 'icon' => 'fas fa-history', 'url' => '/attendance/history', 'route' => 'attendance.history', 'order_num' => 2],
            ['id' => 23, 'parent_id' => 20, 'title' => 'Rekap Absensi', 'icon' => 'fas fa-chart-bar', 'url' => '/attendance/recap', 'route' => 'attendance.recap', 'order_num' => 3],
            ['id' => 24, 'parent_id' => 20, 'title' => 'Pengajuan Lembur', 'icon' => 'fas fa-user-clock', 'url' => '/attendance/overtime', 'route' => 'attendance.overtime', 'order_num' => 4],
            
            // Leave
            ['id' => 30, 'parent_id' => 0, 'title' => 'Cuti & Izin', 'icon' => 'fas fa-calendar-minus', 'url' => '#', 'route' => null, 'order_num' => 5],
            ['id' => 31, 'parent_id' => 30, 'title' => 'Pengajuan Cuti', 'icon' => 'fas fa-paper-plane', 'url' => '/leave/request', 'route' => 'leave.request', 'order_num' => 1],
            ['id' => 32, 'parent_id' => 30, 'title' => 'Persetujuan Cuti', 'icon' => 'fas fa-check-circle', 'url' => '/leave/approval', 'route' => 'leave.approval', 'order_num' => 2],
            ['id' => 33, 'parent_id' => 30, 'title' => 'Riwayat Cuti', 'icon' => 'fas fa-history', 'url' => '/leave/history', 'route' => 'leave.history', 'order_num' => 3],
            
            // Payroll
            ['id' => 40, 'parent_id' => 0, 'title' => 'Penggajian', 'icon' => 'fas fa-money-bill-wave', 'url' => '#', 'route' => null, 'order_num' => 6],
            ['id' => 41, 'parent_id' => 40, 'title' => 'Komponen Gaji', 'icon' => 'fas fa-cogs', 'url' => '/payroll/components', 'route' => 'payroll.components', 'order_num' => 1],
            ['id' => 42, 'parent_id' => 40, 'title' => 'Gaji Pegawai', 'icon' => 'fas fa-file-invoice-dollar', 'url' => '/payroll/employee-salary', 'route' => 'payroll.employee-salary', 'order_num' => 2],
            ['id' => 43, 'parent_id' => 40, 'title' => 'Proses Penggajian', 'icon' => 'fas fa-calculator', 'url' => '/payroll/process', 'route' => 'payroll.process', 'order_num' => 3],
            ['id' => 44, 'parent_id' => 40, 'title' => 'Slip Gaji', 'icon' => 'fas fa-receipt', 'url' => '/payroll/slips', 'route' => 'payroll.slips', 'order_num' => 4],
            ['id' => 45, 'parent_id' => 40, 'title' => 'Laporan Gaji', 'icon' => 'fas fa-file-alt', 'url' => '/payroll/reports', 'route' => 'payroll.reports', 'order_num' => 5],
            
            // Reports
            ['id' => 50, 'parent_id' => 0, 'title' => 'Laporan', 'icon' => 'fas fa-chart-line', 'url' => '#', 'route' => null, 'order_num' => 7],
            ['id' => 51, 'parent_id' => 50, 'title' => 'Laporan Kehadiran', 'icon' => 'fas fa-clipboard-list', 'url' => '/reports/attendance', 'route' => 'reports.attendance', 'order_num' => 1],
            ['id' => 52, 'parent_id' => 50, 'title' => 'Laporan Cuti', 'icon' => 'fas fa-file-medical', 'url' => '/reports/leave', 'route' => 'reports.leave', 'order_num' => 2],
            ['id' => 53, 'parent_id' => 50, 'title' => 'Laporan Penggajian', 'icon' => 'fas fa-file-invoice', 'url' => '/reports/payroll', 'route' => 'reports.payroll', 'order_num' => 3],
            
            // Settings
            ['id' => 60, 'parent_id' => 0, 'title' => 'Pengaturan', 'icon' => 'fas fa-cog', 'url' => '#', 'route' => null, 'order_num' => 8],
            ['id' => 61, 'parent_id' => 60, 'title' => 'Pengaturan Umum', 'icon' => 'fas fa-sliders-h', 'url' => '/settings/general', 'route' => 'settings.general', 'order_num' => 1],
            ['id' => 62, 'parent_id' => 60, 'title' => 'Manajemen User', 'icon' => 'fas fa-users-cog', 'url' => '/settings/users', 'route' => 'settings.users', 'order_num' => 2],
            ['id' => 63, 'parent_id' => 60, 'title' => 'Manajemen Group', 'icon' => 'fas fa-user-shield', 'url' => '/settings/groups', 'route' => 'settings.groups', 'order_num' => 3],
            ['id' => 64, 'parent_id' => 60, 'title' => 'Manajemen Menu', 'icon' => 'fas fa-bars', 'url' => '/settings/menus', 'route' => 'settings.menus', 'order_num' => 4],
            ['id' => 65, 'parent_id' => 60, 'title' => 'Hak Akses', 'icon' => 'fas fa-key', 'url' => '/settings/permissions', 'route' => 'settings.permissions', 'order_num' => 5],
        ];

        foreach ($data as $row) {
            $row['is_active'] = 1;
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('menus')->ignore(true)->insert($row);
        }
    }
}
