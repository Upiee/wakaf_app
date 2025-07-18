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
        // Update kpi_sub_activities table - only drop bobot since sub_activity doesn't exist
        Schema::table('kpi_sub_activities', function (Blueprint $table) {
            // Drop bobot column that's no longer needed
            $table->dropColumn('bobot');
        });

        // Check if okr_sub_activities table exists and has the same structure
        if (Schema::hasTable('okr_sub_activities')) {
            Schema::table('okr_sub_activities', function (Blueprint $table) {
                // Drop bobot column that's no longer needed
                if (Schema::hasColumn('okr_sub_activities', 'bobot')) {
                    $table->dropColumn('bobot');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore kpi_sub_activities table
        Schema::table('kpi_sub_activities', function (Blueprint $table) {
            // Add back the bobot column
            $table->decimal('bobot', 5, 2)->default(0)->after('output');
        });

        // Restore okr_sub_activities table
        if (Schema::hasTable('okr_sub_activities')) {
            Schema::table('okr_sub_activities', function (Blueprint $table) {
                // Add back the bobot column
                $table->decimal('bobot', 5, 2)->default(0)->after('output');
            });
        }
    }
};
