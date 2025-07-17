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
        Schema::create('performance_appraisal_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('performance_appraisal_id');
            $table->string('user_id', 255); // id karyawan
            $table->float('score')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('performance_appraisal_id')->references('id')->on('performance_appraisals')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // aktifkan jika id user string
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_appraisal_scores');
    }
};
