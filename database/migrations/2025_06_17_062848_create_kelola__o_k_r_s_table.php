<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\table;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelola__o_k_r_s', function (Blueprint $table) {
            $table->bigIncrements('id'); // atau $table->bigInteger('id')->primary(); jika manual
            $table->string('activity');
            $table->string('output')->nullable();
            $table->string('tipe')->default('okr'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelola__o_k_r_s');
    }
};
