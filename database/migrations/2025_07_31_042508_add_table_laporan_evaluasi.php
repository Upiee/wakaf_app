<?php

use App\Models\Divisi;
use App\Models\User;
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
            $table->id();
            $table->string('kode_laporan');
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Divisi::class, 'divisi_id')->nullable()->constrained()->nullOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->enum('tipe_laporan', ['individual', 'divisi'])->default('individual');
            $table->json('data_laporan')->nullable();
            $table->json('kpi_references')->nullable();
            $table->json('okr_references')->nullable();
            $table->integer('total_kpi')->default(0);
            $table->integer('total_okr')->default(0);
            $table->decimal('pencapaian_kpi', 5, 2)->default(0.00);
            $table->decimal('pencapaian_okr', 5, 2)->default(0.00);
            $table->decimal('rata_rata_score', 5, 2)->default(0.00);
            $table->text('catatan_evaluasi')->nullable();
            $table->foreignIdFor(User::class, 'dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_evaluasi', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['divisi_id']);
            $table->dropForeign(['dibuat_oleh']);
            $table->dropColumn([
                'id',
                'kode_laporan',
                'user_id',
                'divisi_id',
                'periode_mulai',
                'periode_selesai',
                'tipe_laporan',
                'data_laporan',
                'kpi_references',
                'okr_references',
                'total_kpi',
                'total_okr',
                'pencapaian_kpi',
                'pencapaian_okr',
                'rata_rata_score',
                'catatan_evaluasi',
                'dibuat_oleh'
            ]);
        });
    }
};
