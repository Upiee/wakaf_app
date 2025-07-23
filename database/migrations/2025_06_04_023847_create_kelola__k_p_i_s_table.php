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
        Schema::create('kelola__k_p_i_s', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-increment bigint primary key
            $table->string('activity');
            $table->float('bobot');
            $table->string('output')->nullable();
            $table->text('indikator_progress')->nullable();
            $table->float('progress')->nullable();
            $table->text('dokumen')->nullable();
            $table->string('periode')->nullable();
            $table->string('timeline')->nullable();
            $table->float('timeline_realisasi')->default(100);
            $table->float('realisasi')->nullable();
            $table->enum('tipe', ['kpi', 'okr'])->default('kpi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelola__k_p_i_s');
    }
};
