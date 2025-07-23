<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing divisis kode (field already exists but empty)
        DB::transaction(function () {
            $divisis = DB::table('divisis')->where('kode', '')->orWhereNull('kode')->get();
            foreach ($divisis as $index => $divisi) {
                $kode = 'DIV-' . str_pad($divisi->id, 3, '0', STR_PAD_LEFT);
                DB::table('divisis')->where('id', $divisi->id)->update(['kode' => $kode]);
            }
        });

        // Add kode field to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('kode', 15)->nullable()->after('id');
        });

        // Generate kode for existing users
        DB::transaction(function () {
            $users = DB::table('users')->get();
            foreach ($users as $index => $user) {
                $kode = 'EMP' . str_pad($user->id, 5, '0', STR_PAD_LEFT);
                DB::table('users')->where('id', $user->id)->update(['kode' => $kode]);
            }
        });

        // Make kode fields unique and not nullable
        Schema::table('divisis', function (Blueprint $table) {
            $table->string('kode', 10)->nullable(false)->unique()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('kode', 15)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset divisis kode to null
        DB::table('divisis')->update(['kode' => null]);
        
        // Remove kode field from users table only (divisis already has it)
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }
};
