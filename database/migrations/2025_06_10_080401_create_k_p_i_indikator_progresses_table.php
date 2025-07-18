<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove duplicate table creation - kelola__k_p_i_s already created in earlier migration
        
        Schema::create('kpi_indikator_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('kelola__k_p_i_s')->onDelete('cascade'); // foreign key ke kelola__k_p_i_s.id
            $table->string('kode'); // contoh: 1.1, 1.2, dst
            $table->string('indikator');
            $table->float(column: 'bobot')->nullable(); // <-- Tambahkan kolom bobot di sini
            $table->float('progress')->nullable();
            $table->string('dokumen')->nullable();
            $table->date('periode')->nullable();      
            $table->string('lampiran')->nullable();   
            $table->float('realisasi')->nullable();   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_indikator_progress');
    }
};
