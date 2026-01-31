<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PasswordHintSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $users = $db->table('users')->get()->getResultArray();

        foreach ($users as $user) {
            $hint = 'Default: 123456';
            
            // Customize hint based on username if needed
            if ($user['username'] === 'admin') {
                $hint = 'Admin default password';
            } elseif ($user['username'] === 'hrd') {
                $hint = 'HRD access password';
            }

            $db->table('users')
               ->where('id', $user['id'])
               ->update(['password_hint' => $hint]);
        }

        echo "Berhasil mengupdate hint password untuk " . count($users) . " user.\n";
    }
}
