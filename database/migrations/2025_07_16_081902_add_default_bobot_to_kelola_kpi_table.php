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
            $table->float('bobot')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelola__k_p_i_s', function (Blueprint $table) {
            $table->float('bobot')->change();
        });
    }
};
