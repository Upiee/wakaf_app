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
        Schema::create('realisasi_divisi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('divisi_id');
            $table->unsignedBigInteger('kpi_id')->nullable();
            $table->unsignedBigInteger('okr_id')->nullable();
            $table->float('nilai')->nullable();
            $table->string('periode')->nullable(); // Ubah dari date ke string untuk Q1, Q2, dll
            $table->boolean('is_cutoff')->default(false);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('divisi_id')->references('id')->on('divisis')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('kelola__k_p_i_s')->onDelete('set null');
            $table->foreign('okr_id')->references('id')->on('kelola__o_k_r_s')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_divisi');
    }
};
