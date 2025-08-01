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
            if (!Schema::hasColumn('kelola__o_k_r_s', 'output')) {
                $table->text('output')->nullable()->after('activity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelola__o_k_r_s', function (Blueprint $table) {
            if (Schema::hasColumn('kelola__o_k_r_s', 'output')) {
                $table->dropColumn('output');
            }
        });
    }
};