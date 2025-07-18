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
        Schema::create('employee_indikator_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kelola_kpi_okr_id'); // updated to bigint to match KPI ID
            $table->foreign('kelola_kpi_okr_id')->references('id')->on('kelola__k_p_i_s')->onDelete('cascade');
            $table->string('kode')->nullable();
            $table->string('indikator');
            $table->float('bobot')->nullable();
            $table->float('progress')->nullable();
            $table->string('periode')->nullable();
            $table->string('lampiran')->nullable();
            $table->string('realisasi')->nullable();
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_indikator_progress');
    }
};
