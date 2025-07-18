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
            $table->boolean('is_editable')->default(true)->after('output');
            // Menambahkan kolom is_editable dengan default true
            // Kolom ini akan menentukan apakah OKR dapat diedit atau tidak
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelola__o_k_r_s', function (Blueprint $table) {
            $table->dropColumn('is_editable');
            // Menghapus kolom is_editable jika migrasi dibatalkan
        });
    }
};
