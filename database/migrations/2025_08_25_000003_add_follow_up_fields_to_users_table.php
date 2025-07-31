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
        Schema::table('users', function (Blueprint $table) {
            // Field untuk tracking riwayat tindak lanjut (sesuai skenario step 9)
            $table->string('last_follow_up_action')->nullable()->after('email'); // Tindak lanjut terakhir
            $table->timestamp('last_follow_up_date')->nullable()->after('last_follow_up_action'); // Tanggal tindak lanjut terakhir
            $table->json('follow_up_history')->nullable()->after('last_follow_up_date'); // Riwayat semua tindak lanjut
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_follow_up_action', 'last_follow_up_date', 'follow_up_history']);
        });
    }
};
