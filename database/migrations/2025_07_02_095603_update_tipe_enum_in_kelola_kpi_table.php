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
        Schema::table('kelola__k_p_i_s', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
        
        Schema::table('kelola__k_p_i_s', function (Blueprint $table) {
            $table->enum('tipe', ['kpi', 'okr', 'kpi individu', 'kpi divisi', 'okr individu', 'okr divisi'])
                ->default('kpi')
                ->after('output');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelola__k_p_i_s', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
        
        Schema::table('kelola__k_p_i_s', function (Blueprint $table) {
            $table->enum('tipe', ['kpi', 'okr'])
                  ->default('kpi')
                  ->after('output');
        });
    }
};
