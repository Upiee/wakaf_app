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
        Schema::create('tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tindak_lanjut')->unique(); // TL-202501-001
            $table->foreignId('laporan_evaluasi_id')->constrained('laporan_evaluasi')->onDelete('cascade'); // Referensi ke laporan
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Karyawan yang mendapat tindak lanjut
            $table->enum('jenis_tindakan', [
                'pelatihan', 
                'promosi', 
                'rotasi', 
                'peringatan', 
                'development_plan',
                'coaching',
                'counseling',
                'special_assignment',
                'reward'
            ]); // Sesuai skenario step 5
            $table->text('deskripsi_tindakan'); // Detail tindakan yang akan dilakukan
            $table->text('target_perbaikan')->nullable(); // Target yang ingin dicapai
            $table->date('timeline_mulai'); // Sesuai skenario step 6 - timeline
            $table->date('timeline_selesai'); // Sesuai skenario step 6 - timeline
            $table->foreignId('pic_responsible')->constrained('users')->onDelete('cascade'); // Sesuai skenario step 6 - PIC
            $table->enum('status_pelaksanaan', [
                'planned', 
                'in_progress', 
                'completed', 
                'cancelled', 
                'postponed'
            ])->default('planned');
            $table->integer('progress_percentage')->default(0); // Progress 0-100%
            $table->text('catatan_progress')->nullable(); // Update progress berkala
            $table->text('hasil_evaluasi')->nullable(); // Hasil setelah selesai
            $table->foreignId('dibuat_oleh')->constrained('users')->onDelete('cascade'); // HR yang membuat
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null'); // Manager yang menyetujui
            $table->timestamp('tanggal_persetujuan')->nullable(); // Waktu approval
            $table->timestamps();
            
            // Indexes untuk performance
            $table->index(['status_pelaksanaan', 'timeline_selesai']);
            $table->index(['user_id', 'jenis_tindakan']);
            $table->index(['pic_responsible', 'status_pelaksanaan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
    }
};
