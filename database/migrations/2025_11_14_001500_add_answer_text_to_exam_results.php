<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->longText('answer_text')->nullable()->after('answer_path');
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn('answer_text');
        });
    }
};