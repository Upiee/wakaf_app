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
            // Change rata_rata_score from decimal(3,2) to decimal(5,2) to support 0.00-100.00
            $table->decimal('rata_rata_score', 5, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_evaluasi', function (Blueprint $table) {
            // Revert back to decimal(3,2)
            $table->decimal('rata_rata_score', 3, 2)->change();
        });
    }
};
