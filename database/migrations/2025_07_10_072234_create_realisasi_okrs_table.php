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
        Schema::create('realisasi_okrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('divisi_id');
            $table->unsignedBigInteger('okr_id'); // Menggunakan bigint karena id OKR adalah bigint
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('nilai', 5, 2); // Nilai realisasi dalam persen
            $table->string('periode', 20); // Q1-2025, Q2-2025, dst
            $table->text('keterangan')->nullable();
            $table->boolean('is_cutoff')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('divisi_id')->references('id')->on('divisis')->onDelete('cascade');
            $table->foreign('okr_id')->references('id')->on('kelola__o_k_r_s')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['divisi_id', 'periode']);
            $table->index(['okr_id', 'periode']);
            $table->unique(['okr_id', 'periode', 'divisi_id'], 'unique_okr_realisasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_okrs');
    }
};
