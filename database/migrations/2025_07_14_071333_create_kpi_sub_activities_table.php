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
        Schema::create('kpi_sub_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kpi_id');
            $table->decimal('bobot', 5, 2)->nullable(); // Bobot dalam persen (0.00 - 100.00)
            $table->text('output'); // Output/deliverable dari sub-activity
            $table->text('indikator')->nullable(); // Indikator progress untuk sub-activity
            $table->decimal('progress_percentage', 5, 2)->default(0); // Progress 0-100%
            $table->enum('status', ['draft', 'in_progress', 'completed', 'overdue'])->default('draft');
            $table->date('target_date')->nullable(); // Target completion date
            $table->date('actual_date')->nullable(); // Actual completion date
            $table->text('dokumen')->nullable(); // Path to uploaded documents
            $table->text('keterangan')->nullable(); // Additional notes
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('kpi_id')->references('id')->on('kelola__k_p_i_s')->onDelete('cascade');
            
            // Indexes
            $table->index(['kpi_id', 'status']);
            $table->index(['status', 'target_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_sub_activities');
    }
};
