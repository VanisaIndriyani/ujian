<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'type')) {
                $table->string('type', 10)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};