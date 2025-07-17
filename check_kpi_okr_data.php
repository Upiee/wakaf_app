<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\User;

// Periksa data KPI divisi
echo "=== DATA KPI DIVISI ===\n";
$kpiDivisi = KelolaKPI::where('assignment_type', 'divisi')
    ->whereNull('user_id')
    ->whereNotNull('divisi_id')
    ->where(function($q) {
        $q->where('tipe', 'kpi')
          ->orWhere('tipe', 'kpi divisi');
    })
    ->get();

foreach ($kpiDivisi as $kpi) {
    echo "ID: {$kpi->id} | Activity: {$kpi->activity} | Tipe: {$kpi->tipe} | Divisi: {$kpi->divisi_id}\n";
}

echo "\n=== DATA OKR DIVISI ===\n";
$okrDivisi = KelolaOKR::where('assignment_type', 'divisi')
    ->whereNull('user_id')
    ->whereNotNull('divisi_id')
    ->where(function($q) {
        $q->where('tipe', 'okr')
          ->orWhere('tipe', 'okr divisi');
    })
    ->get();

foreach ($okrDivisi as $okr) {
    echo "ID: {$okr->id} | Activity: {$okr->activity} | Tipe: {$okr->tipe} | Divisi: {$okr->divisi_id}\n";
}

// Periksa data per divisi
echo "\n=== DATA PER DIVISI ===\n";
$managers = User::where('role', 'manager')->get();
foreach ($managers as $manager) {
    echo "\n--- Manager: {$manager->name} (Divisi: {$manager->divisi_id}) ---\n";
    
    $kpiCount = KelolaKPI::where('divisi_id', $manager->divisi_id)
        ->where('assignment_type', 'divisi')
        ->whereNull('user_id')
        ->where(function($q) {
            $q->where('tipe', 'kpi')
              ->orWhere('tipe', 'kpi divisi');
        })
        ->count();
    
    $okrCount = KelolaOKR::where('divisi_id', $manager->divisi_id)
        ->where('assignment_type', 'divisi')
        ->whereNull('user_id')
        ->where(function($q) {
            $q->where('tipe', 'okr')
              ->orWhere('tipe', 'okr divisi');
        })
        ->count();
    
    echo "KPI Divisi: {$kpiCount} | OKR Divisi: {$okrCount}\n";
}

echo "\nSelesai!\n";
