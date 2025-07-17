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
        Schema::create('set_kpi_okrs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama setting
            $table->enum('type', ['kpi', 'okr']); // Jenis setting
            $table->date('start_date'); // Tanggal mulai input
            $table->date('end_date'); // Tanggal selesai input
            $table->date('cutoff_date'); // Tanggal cut off
            $table->boolean('is_active')->default(true); // Status aktif/non-aktif
            $table->text('description')->nullable(); // Deskripsi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('set_kpi_okrs');
    }
};
