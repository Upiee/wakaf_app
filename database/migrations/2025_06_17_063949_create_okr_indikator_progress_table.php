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
        Schema::create('okr_indikator_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('okr_id');
            $table->foreign('okr_id')->references('id')->on('kelola__o_k_r_s')->onDelete('cascade');
            $table->string('kode');
            $table->string('indikator');
            $table->float('bobot')->nullable();
            $table->float('progress')->nullable();
            $table->date('periode')->nullable();
            $table->string('lampiran')->nullable();
            $table->float('realisasi')->nullable();
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('okr_indikator_progress');
    }
};
