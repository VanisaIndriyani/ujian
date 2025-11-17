<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan kolom photo_path jika belum ada
        if (!Schema::hasColumn('users', 'photo_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('photo_path')->nullable()->after('classroom');
            });
        }

        // Tambahkan kolom classroom jika belum ada (untuk konsistensi form)
        if (!Schema::hasColumn('users', 'classroom')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('classroom')->nullable()->after('role');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
            if (Schema::hasColumn('users', 'classroom')) {
                $table->dropColumn('classroom');
            }
        });
    }
};