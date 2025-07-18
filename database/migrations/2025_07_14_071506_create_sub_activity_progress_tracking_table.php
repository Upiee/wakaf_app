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
        Schema::create('sub_activity_progress_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kpi_sub_activity_id')->nullable();
            $table->unsignedBigInteger('okr_sub_activity_id')->nullable();
            $table->string('periode', 20); // Q1-2025, Q2-2025, atau Apr-2025, May-2025
            $table->decimal('progress_value', 5, 2)->default(0); // Progress untuk periode ini
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'blocked'])->default('not_started');
            $table->text('keterangan')->nullable(); // Catatan progress periode ini
            $table->text('dokumen')->nullable(); // Dokumen pendukung
            $table->unsignedBigInteger('updated_by')->nullable(); // User yang update
            $table->timestamp('completed_at')->nullable(); // Waktu selesai
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('kpi_sub_activity_id')->references('id')->on('kpi_sub_activities')->onDelete('cascade');
            $table->foreign('okr_sub_activity_id')->references('id')->on('okr_sub_activities')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['kpi_sub_activity_id', 'periode']);
            $table->index(['okr_sub_activity_id', 'periode']);
            $table->index(['periode', 'status']);
            
            // Unique constraint
            $table->unique(['kpi_sub_activity_id', 'periode'], 'unique_kpi_sub_periode');
            $table->unique(['okr_sub_activity_id', 'periode'], 'unique_okr_sub_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_activity_progress_tracking');
    }
};
