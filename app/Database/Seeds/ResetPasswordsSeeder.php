<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResetPasswordsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        $password = '123456';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update all users except 'admin'
        $db->table('users')
           ->where('username !=', 'admin')
           ->update([
               'password' => $hashedPassword,
               'password_hint' => 'Default: 123456'
           ]);

        echo "Berhasil mereset password semua user (kecuali admin) menjadi: 123456\n";
    }
}
