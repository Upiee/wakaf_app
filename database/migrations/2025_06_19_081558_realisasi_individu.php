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
        Schema::create('realisasi_individu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('kpi_id', 255)->nullable();
            $table->string('okr_id', 255)->nullable();
            $table->float('nilai')->nullable();
            $table->date('periode')->nullable();
            $table->boolean('is_cutoff')->default(false);
            $table->timestamps();

            // Jika ingin foreign key, pastikan kolom id di tabel parent juga VARCHAR(255)
            // $table->foreign('kpi_id')->references('id')->on('kelola__k_p_i_s')->onDelete('set null');
            // $table->foreign('okr_id')->references('id')->on('kelola__o_k_r_s')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_individu');
    }
};
