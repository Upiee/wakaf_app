<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Akun Admin
        
        // Akun Manajer
        User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('123'), // password: 123
            'role' => 'manager',
        ]);

        // Akun Karyawan
        User::factory()->create([
            'name' => 'Karyawan',
            'email' => 'karyawan@gmail.com',
            'password' => bcrypt('123'), // password: 123
            'role' => 'employee',
        ]);


        $data = null;
    }
}
