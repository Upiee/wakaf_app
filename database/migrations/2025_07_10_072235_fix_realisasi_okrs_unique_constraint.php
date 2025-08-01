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
        Schema::table('realisasi_okrs', function (Blueprint $table) {
            // Drop existing unique constraint
            $table->dropUnique('unique_okr_realisasi');
            
            // Add new unique constraint that includes user_id
            $table->unique(['okr_id', 'periode', 'divisi_id', 'user_id'], 'unique_okr_realisasi_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_okrs', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('unique_okr_realisasi_user');
            
            // Restore original unique constraint
            $table->unique(['okr_id', 'periode', 'divisi_id'], 'unique_okr_realisasi');
        });
    }
};
