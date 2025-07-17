<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\Divisi;
use App\Models\User;

class CreateSampleData extends Command
{
    protected $signature = 'create:sample-data';
    protected $description = 'Create sample KPI and OKR data for testing';

    public function handle()
    {
        // Ambil divisi pertama atau buat jika tidak ada
        $divisi = Divisi::first();
        if (!$divisi) {
            $divisi = Divisi::create(['nama' => 'Operational']);
        }

        // Update manager pertama dengan divisi ini
        $manager = User::where('role', 'manager')->first();
        if ($manager) {
            $manager->update(['divisi_id' => $divisi->id]);
            $this->info("Manager {$manager->name} assigned to divisi {$divisi->nama}");
        }

        // Buat sample KPI
        $kpi1 = KelolaKPI::create([
            'id' => 'KPI-MGR-001',
            'activity' => 'Meningkatkan efisiensi operasional',
            'output' => 'Pengurangan waktu proses 30%',
            'bobot' => 25,
            'indikator_progress' => 'Waktu proses operasional berkurang',
            'progress' => 0,
            'timeline' => '2025-12-31',
            'tipe' => 'kpi',
            'divisi_id' => $divisi->id,
            'assignment_type' => 'divisi',
            'status' => 'active',
            'periode' => '2025',
        ]);

        $kpi2 = KelolaKPI::create([
            'id' => 'KPI-MGR-002',
            'activity' => 'Optimalisasi sistem inventory',
            'output' => 'Akurasi inventory 95%',
            'bobot' => 30,
            'indikator_progress' => 'Tingkat akurasi inventory meningkat',
            'progress' => 0,
            'timeline' => '2025-12-31',
            'tipe' => 'kpi',
            'divisi_id' => $divisi->id,
            'assignment_type' => 'divisi',
            'status' => 'active',
            'periode' => '2025',
        ]);

        // Buat sample OKR
        $okr1 = KelolaOKR::create([
            'activity' => 'Digitalisasi proses operasional',
            'output' => 'Semua proses tersistem digital',
            'bobot' => 35,
            'indikator_progress' => 'Jumlah proses digital meningkat',
            'progress' => 0,
            'timeline' => '2025-12-31',
            'tipe' => 'okr',
            'divisi_id' => $divisi->id,
            'assignment_type' => 'divisi',
            'status' => 'active',
            'periode' => '2025',
        ]);

        $okr2 = KelolaOKR::create([
            'activity' => 'Implementasi quality control',
            'output' => 'Zero defect pada output operasional',
            'bobot' => 40,
            'indikator_progress' => 'Tingkat defect berkurang',
            'progress' => 0,
            'timeline' => '2025-12-31',
            'tipe' => 'okr',
            'divisi_id' => $divisi->id,
            'assignment_type' => 'divisi',
            'status' => 'active',
            'periode' => '2025',
        ]);

        $this->info('Sample data created successfully:');
        $this->info("- KPI 1: {$kpi1->activity}");
        $this->info("- KPI 2: {$kpi2->activity}");
        $this->info("- OKR 1: {$okr1->activity}");
        $this->info("- OKR 2: {$okr2->activity}");
        $this->info("All assigned to divisi: {$divisi->nama}");

        return 0;
    }
}
