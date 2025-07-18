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
        // Add bobot field back to kpi_sub_activities
        Schema::table('kpi_sub_activities', function (Blueprint $table) {
            $table->decimal('bobot', 5, 2)->default(0)->after('output');
        });

        // Add bobot field back to okr_sub_activities
        Schema::table('okr_sub_activities', function (Blueprint $table) {
            $table->decimal('bobot', 5, 2)->default(0)->after('output');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove bobot field from kpi_sub_activities
        Schema::table('kpi_sub_activities', function (Blueprint $table) {
            $table->dropColumn('bobot');
        });

        // Remove bobot field from okr_sub_activities
        Schema::table('okr_sub_activities', function (Blueprint $table) {
            $table->dropColumn('bobot');
        });
    }
};
