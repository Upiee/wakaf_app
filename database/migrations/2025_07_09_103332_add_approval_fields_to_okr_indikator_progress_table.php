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
        Schema::table('okr_indikator_progress', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('dokumen');
            $table->string('status_approval')->default('pending')->after('keterangan');
            $table->text('manager_notes')->nullable()->after('status_approval');
            $table->timestamp('tanggal_realisasi')->nullable()->after('manager_notes');
            $table->unsignedBigInteger('user_id')->nullable()->after('tanggal_realisasi');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('okr_indikator_progress', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['keterangan', 'status_approval', 'manager_notes', 'tanggal_realisasi', 'user_id']);
        });
    }
};
