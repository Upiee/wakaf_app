<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Export route untuk laporan evaluasi
Route::get('/laporan/export/{id}', function ($id) {
    $laporan = \App\Models\LaporanEvaluasi::with(['user', 'divisi'])->findOrFail($id);
    
    $html = view('exports.laporan-evaluasi', [
        'laporan' => $laporan,
        'kpiData' => $laporan->kpi_references ?? [],
        'okrData' => $laporan->okr_references ?? [],
    ])->render();
    
    $filename = "Laporan_Evaluasi_" . str_replace(['/', '\\', ' '], '_', $laporan->getAttribute('kode_laporan')) . "_" . date('Ymd_His') . ".xls";
    
    return response($html, 200, [
        'Content-Type' => 'application/vnd.ms-excel',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    ]);
})->name('laporan.export');
