<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Divisi;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class KodifikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama jika ada (hanya untuk development)
        // User::truncate();
        // Divisi::truncate();

        // Data Divisi dengan Kode Terstruktur
        $divisis = [
            ['kode' => 'DIV-001', 'nama' => 'IT & Technology'],
            ['kode' => 'DIV-002', 'nama' => 'Human Resources'],  
            ['kode' => 'DIV-003', 'nama' => 'Finance & Accounting'],
            ['kode' => 'DIV-004', 'nama' => 'Marketing & Sales'],
            ['kode' => 'DIV-005', 'nama' => 'Operations'],
            ['kode' => 'DIV-006', 'nama' => 'Quality Assurance'],
            ['kode' => 'DIV-007', 'nama' => 'Legal & Compliance'],
            ['kode' => 'DIV-008', 'nama' => 'Research & Development'],
            ['kode' => 'DIV-009', 'nama' => 'Customer Service'],
            ['kode' => 'DIV-010', 'nama' => 'Procurement'],
        ];

        // Create Divisi
        foreach ($divisis as $divisi) {
            Divisi::updateOrCreate(
                ['kode' => $divisi['kode']], 
                $divisi
            );
        }

        // Data Users dengan Kode Terstruktur
        $users = [
            // IT & Technology (DIV-001)
            [
                'kode' => 'EMP00001',
                'name' => 'Ahmad Rizky',
                'email' => 'ahmad.rizky@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'divisi_id' => Divisi::where('kode', 'DIV-001')->first()->id,
                'is_active' => true,
            ],
            [
                'kode' => 'EMP00002', 
                'name' => 'Sari Indah',
                'email' => 'sari.indah@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'divisi_id' => Divisi::where('kode', 'DIV-001')->first()->id,
                'is_active' => true,
            ],
            [
                'kode' => 'EMP00003',
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@wakafapp.com', 
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'divisi_id' => Divisi::where('kode', 'DIV-001')->first()->id,
                'is_active' => true,
            ],
            
            // Human Resources (DIV-002)
            [
                'kode' => 'EMP00004',
                'name' => 'Maya Sari',
                'email' => 'maya.sari@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'hr',
                'divisi_id' => Divisi::where('kode', 'DIV-002')->first()->id,
                'is_active' => true,
            ],
            [
                'kode' => 'EMP00005',
                'name' => 'Dedi Kurniawan', 
                'email' => 'dedi.kurniawan@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'divisi_id' => Divisi::where('kode', 'DIV-002')->first()->id,
                'is_active' => true,
            ],
            
            // Finance & Accounting (DIV-003)
            [
                'kode' => 'EMP00006',
                'name' => 'Rina Wulandari',
                'email' => 'rina.wulandari@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'divisi_id' => Divisi::where('kode', 'DIV-003')->first()->id,
                'is_active' => true,
            ],
            [
                'kode' => 'EMP00007',
                'name' => 'Agus Prasetyo',
                'email' => 'agus.prasetyo@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'divisi_id' => Divisi::where('kode', 'DIV-003')->first()->id,
                'is_active' => true,
            ],
            
            // Marketing & Sales (DIV-004)
            [
                'kode' => 'EMP00008',
                'name' => 'Linda Kartika',
                'email' => 'linda.kartika@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'divisi_id' => Divisi::where('kode', 'DIV-004')->first()->id,
                'is_active' => true,
            ],
            [
                'kode' => 'EMP00009',
                'name' => 'Rendi Firmansyah',
                'email' => 'rendi.firmansyah@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'divisi_id' => Divisi::where('kode', 'DIV-004')->first()->id,
                'is_active' => true,
            ],
            
            // Operations (DIV-005)
            [
                'kode' => 'EMP00010',
                'name' => 'Tuti Handayani',
                'email' => 'tuti.handayani@wakafapp.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'divisi_id' => Divisi::where('kode', 'DIV-005')->first()->id,
                'is_active' => true,
            ],
            
            // Admin Super User
            [
                'kode' => 'ADM00001',
                'name' => 'Super Admin',
                'email' => 'admin@wakafapp.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'divisi_id' => Divisi::where('kode', 'DIV-002')->first()->id, // HR
                'is_active' => true,
            ],
        ];

        // Create Users
        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], 
                $user
            );
        }

        $this->command->info('🎯 Kodifikasi Seeder completed successfully!');
        $this->command->info('📊 Created/Updated ' . count($divisis) . ' divisi with structured codes');
        $this->command->info('👥 Created/Updated ' . count($users) . ' users with employee codes');
        $this->command->info('');
        $this->command->info('📋 Sample Codes Generated:');
        $this->command->info('   Divisi: DIV-001, DIV-002, DIV-003, etc.');
        $this->command->info('   Users: EMP00001, EMP00002, EMP00003, etc.');
        $this->command->info('   Admin: ADM00001');
    }
}
