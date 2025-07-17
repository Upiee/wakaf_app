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
        // Add assignment fields to kelola__k_p_i_s table
        Schema::table('kelola__k_p_i_s', function (Blueprint $table) {
            $table->enum('assignment_type', ['divisi', 'individual'])->default('divisi')->after('tipe');
            $table->unsignedBigInteger('divisi_id')->nullable()->after('assignment_type');
            $table->unsignedBigInteger('user_id')->nullable()->after('divisi_id');
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft')->after('user_id');
            $table->text('notes')->nullable()->after('status');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('notes');
            
            // Add foreign key constraints
            $table->foreign('divisi_id')->references('id')->on('divisis')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Add assignment fields to kelola__o_k_r_s table
        Schema::table('kelola__o_k_r_s', function (Blueprint $table) {
            $table->enum('assignment_type', ['divisi', 'individual'])->default('divisi')->after('tipe');
            $table->unsignedBigInteger('divisi_id')->nullable()->after('assignment_type');
            $table->unsignedBigInteger('user_id')->nullable()->after('divisi_id');
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft')->after('user_id');
            $table->text('notes')->nullable()->after('status');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('notes');
            
            // Add foreign key constraints
            $table->foreign('divisi_id')->references('id')->on('divisis')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelola__k_p_i_s', function (Blueprint $table) {
            $table->dropForeign(['divisi_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'assignment_type',
                'divisi_id', 
                'user_id',
                'status',
                'notes',
                'priority'
            ]);
        });

        Schema::table('kelola__o_k_r_s', function (Blueprint $table) {
            $table->dropForeign(['divisi_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'assignment_type',
                'divisi_id',
                'user_id', 
                'status',
                'notes',
                'priority'
            ]);
        });
    }
};
