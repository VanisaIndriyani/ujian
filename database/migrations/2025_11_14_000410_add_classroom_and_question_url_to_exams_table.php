<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('classroom')->nullable()->after('creator_id');
            $table->string('question_url')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['classroom', 'question_url']);
        });
    }
};