<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_results', 'status')) {
                $table->string('status')->nullable()->after('score');
            }
            if (!Schema::hasColumn('exam_results', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            if (Schema::hasColumn('exam_results', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('exam_results', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};