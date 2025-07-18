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
        Schema::table('kelola__o_k_r_s', function (Blueprint $table) {
            // Add fields to match KelolaKPI structure
            $table->double('bobot')->default(0)->after('output');
            $table->text('indikator_progress')->nullable()->after('bobot');
            $table->double('progress')->nullable()->default(0)->after('indikator_progress');
            $table->text('dokumen')->nullable()->after('progress');
            $table->string('periode', 255)->nullable()->after('dokumen');
            $table->string('timeline', 255)->nullable()->after('periode');
            $table->double('timeline_realisasi')->default(100)->after('timeline');
            $table->double('realisasi')->nullable()->default(0)->after('timeline_realisasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelola__o_k_r_s', function (Blueprint $table) {
            $table->dropColumn([
                'bobot',
                'indikator_progress', 
                'progress',
                'dokumen',
                'periode',
                'timeline',
                'timeline_realisasi',
                'realisasi'
            ]);
        });
    }
};
