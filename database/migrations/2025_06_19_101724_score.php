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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 255)->nullable();    // Untuk score individu
            $table->string('divisi_id', 255)->nullable();  // Untuk score divisi
            $table->float('total_nilai')->nullable();
            $table->date('periode')->nullable();
            $table->timestamps();

            // Jika ingin foreign key, pastikan kolom id di tabel users/divisis juga VARCHAR(255)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('divisi_id')->references('id')->on('divisis')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
