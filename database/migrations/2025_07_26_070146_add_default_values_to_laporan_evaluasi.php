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
            // Add default values for numeric columns
            $table->integer('total_kpi')->default(0)->change();
            $table->integer('total_okr')->default(0)->change();
            $table->decimal('pencapaian_kpi', 5, 2)->default(0.00)->change();
            $table->decimal('pencapaian_okr', 5, 2)->default(0.00)->change();
            $table->decimal('rata_rata_score', 5, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_evaluasi', function (Blueprint $table) {
            // Remove default values
            $table->integer('total_kpi')->default(null)->change();
            $table->integer('total_okr')->default(null)->change();
            $table->decimal('pencapaian_kpi', 5, 2)->default(null)->change();
            $table->decimal('pencapaian_okr', 5, 2)->default(null)->change();
            $table->decimal('rata_rata_score', 5, 2)->default(null)->change();
        });
    }
};
