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
        Schema::create('performance_appraisals', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 50); // contoh: "Juli 2025"
            $table->date('mulai_penilaian');
            $table->date('selesai_penilaian');
            $table->enum('status', ['aktif', 'nonaktif', 'finish'])->default('nonaktif');
            $table->float('bobot')->nullable(); // opsional, jika ingin set bobot/score
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_appraisals');
    }
};
