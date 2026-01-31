<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SupervisorSeeder extends Seeder
{
    public function run()
    {
        // Data supervisor berdasarkan struktur organisasi
        // Struktur: Manager departemen sebagai atasan staff di departemennya
        
        $db = \Config\Database::connect();
        
        // Cek apakah kolom supervisor_id ada
        if (!$db->fieldExists('supervisor_id', 'employees')) {
            echo "Kolom supervisor_id belum ada. Jalankan migration dulu.\n";
            return;
        }

        // Ambil semua manager (berdasarkan position yang mengandung kata Manager/Kepala/Head)
        $managers = $db->query("
            SELECT e.id, e.full_name, e.department_id, p.name as position_name
            FROM employees e
            JOIN positions p ON p.id = e.position_id
            WHERE p.name LIKE '%Manager%' 
               OR p.name LIKE '%Kepala%' 
               OR p.name LIKE '%Head%'
               OR p.name LIKE '%Supervisor%'
               OR p.name LIKE '%Lead%'
        ")->getResultArray();

        // Buat mapping department -> manager
        $deptManagers = [];
        foreach ($managers as $manager) {
            $deptManagers[$manager['department_id']] = $manager['id'];
        }

        // Jika tidak ada manager, buat default supervisor dari karyawan pertama di tiap departemen
        if (empty($deptManagers)) {
            $firstByDept = $db->query("
                SELECT MIN(id) as id, department_id
                FROM employees
                WHERE is_active = 1
                GROUP BY department_id
            ")->getResultArray();
            
            foreach ($firstByDept as $emp) {
                $deptManagers[$emp['department_id']] = $emp['id'];
            }
        }

        // Update semua karyawan dengan supervisor mereka (berdasarkan departemen)
        $employees = $db->query("SELECT id, department_id FROM employees WHERE is_active = 1")->getResultArray();
        
        $updated = 0;
        foreach ($employees as $emp) {
            // Jika ada manager di departemen yang sama
            if (isset($deptManagers[$emp['department_id']])) {
                $supervisorId = $deptManagers[$emp['department_id']];
                
                // Jangan set diri sendiri sebagai supervisor
                if ($supervisorId != $emp['id']) {
                    $db->query("UPDATE employees SET supervisor_id = ? WHERE id = ?", [$supervisorId, $emp['id']]);
                    $updated++;
                }
            }
        }

        echo "Berhasil mengupdate supervisor untuk {$updated} karyawan.\n";
        
        // Tampilkan struktur yang dihasilkan
        echo "\n=== STRUKTUR SUPERVISOR ===\n";
        $structure = $db->query("
            SELECT e.id, e.full_name, d.name as department, p.name as position, 
                   s.full_name as supervisor_name
            FROM employees e
            LEFT JOIN departments d ON d.id = e.department_id
            LEFT JOIN positions p ON p.id = e.position_id
            LEFT JOIN employees s ON s.id = e.supervisor_id
            WHERE e.is_active = 1
            ORDER BY e.department_id, e.id
        ")->getResultArray();
        
        foreach ($structure as $emp) {
            $supervisor = $emp['supervisor_name'] ?? '(Tidak ada atasan)';
            echo "- {$emp['full_name']} ({$emp['position']}) -> Atasan: {$supervisor}\n";
        }
    }
}
