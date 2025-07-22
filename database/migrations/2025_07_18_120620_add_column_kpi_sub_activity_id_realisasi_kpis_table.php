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
            $table->unsignedBigInteger('kpi_sub_activity_id')->nullable()->after('kpi_id');
            $table->foreign('kpi_sub_activity_id')->references('id')->on('kpi_sub_activities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_kpis', function (Blueprint $table) {
            $table->dropForeign(['kpi_sub_activity_id']);
            $table->dropColumn('kpi_sub_activity_id');
        });
    }
};
