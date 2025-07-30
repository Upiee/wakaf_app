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
            // Add columns yang belum ada
            if (!Schema::hasColumn('laporan_evaluasi', 'kode_laporan')) {
                $table->string('kode_laporan')->unique()->after('id');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->after('kode_laporan');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'divisi_id')) {
                $table->foreignId('divisi_id')->nullable()->constrained('divisis')->onDelete('cascade')->after('user_id');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'periode_mulai')) {
                $table->date('periode_mulai')->after('divisi_id');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'periode_selesai')) {
                $table->date('periode_selesai')->after('periode_mulai');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'tipe_laporan')) {
                $table->enum('tipe_laporan', ['individual', 'divisi', 'perusahaan'])->after('periode_selesai');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'data_laporan')) {
                $table->json('data_laporan')->nullable()->after('tipe_laporan');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'kpi_references')) {
                $table->json('kpi_references')->nullable()->after('data_laporan');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'okr_references')) {
                $table->json('okr_references')->nullable()->after('kpi_references');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'total_kpi')) {
                $table->integer('total_kpi')->default(0)->after('okr_references');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'total_okr')) {
                $table->integer('total_okr')->default(0)->after('total_kpi');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'pencapaian_kpi')) {
                $table->decimal('pencapaian_kpi', 5, 2)->default(0)->after('total_okr');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'pencapaian_okr')) {
                $table->decimal('pencapaian_okr', 5, 2)->default(0)->after('pencapaian_kpi');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'rata_rata_score')) {
                $table->decimal('rata_rata_score', 3, 2)->default(0)->after('pencapaian_okr');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'status_kinerja')) {
                $table->string('status_kinerja')->nullable()->after('rata_rata_score');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'rekomendasi')) {
                $table->text('rekomendasi')->nullable()->after('status_kinerja');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'catatan_evaluasi')) {
                $table->text('catatan_evaluasi')->nullable()->after('rekomendasi');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'dibuat_oleh')) {
                $table->foreignId('dibuat_oleh')->constrained('users')->onDelete('cascade')->after('catatan_evaluasi');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'disetujui_oleh')) {
                $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null')->after('dibuat_oleh');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'tanggal_persetujuan')) {
                $table->timestamp('tanggal_persetujuan')->nullable()->after('disetujui_oleh');
            }
            if (!Schema::hasColumn('laporan_evaluasi', 'status_laporan')) {
                $table->enum('status_laporan', ['draft', 'pending_approval', 'approved', 'published'])->default('draft')->after('tanggal_persetujuan');
            }
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_evaluasi', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['periode_mulai', 'periode_selesai']);
            $table->dropIndex(['tipe_laporan', 'status_laporan']);
            $table->dropIndex(['divisi_id', 'periode_mulai']);
            
            // Drop columns
            $table->dropColumn([
                'kode_laporan', 'user_id', 'divisi_id', 'periode_mulai', 'periode_selesai',
                'tipe_laporan', 'data_laporan', 'kpi_references', 'okr_references',
                'total_kpi', 'total_okr', 'pencapaian_kpi', 'pencapaian_okr',
                'rata_rata_score', 'status_kinerja', 'rekomendasi', 'catatan_evaluasi',
                'dibuat_oleh', 'disetujui_oleh', 'tanggal_persetujuan', 'status_laporan'
            ]);
        });
    }
};
