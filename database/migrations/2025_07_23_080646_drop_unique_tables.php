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
        Schema::table('realisasi_kpis', function (Blueprint $table) {
            // Drop existing unique constraint
            $table->dropUnique('unique_kpi_realisasi_user');
            
            // Add new unique constraint that includes user_id
            // $table->unique(['kpi_id', 'periode', 'divisi_id', 'user_id'], 'unique_kpi_realisasi_user');
        });
        Schema::table('realisasi_okrs', function (Blueprint $table) {
            // Drop existing unique constraint
            $table->dropUnique('unique_okr_realisasi_user'); 

            // Add new unique constraint that includes user_id
            // $table->unique(['okr_id', 'periode', 'divisi_id', 'user_id'], 'unique_okr_realisasi_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
