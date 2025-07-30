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
        Schema::table('laporan_evaluasi', function (Blueprint $table) {
            // Update enum to only include individual and divisi
            $table->enum('tipe_laporan', ['individual', 'divisi'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_evaluasi', function (Blueprint $table) {
            // Restore original enum
            $table->enum('tipe_laporan', ['individual', 'divisi', 'perusahaan'])->change();
        });
    }
};
