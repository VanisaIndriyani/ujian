<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ExamTypeSeeder extends Seeder
{
    /**
     * Seed 5 exams with explicit UTS/UAS types and results.
     */
    public function run(): void
    {
        // Fokuskan pada guru default yang digunakan login (DatabaseSeeder menetapkan email ini)
        $guru = User::query()->where('email', 'guru@radiologi.test')->first();
        $subjects = Subject::query()
            ->when($guru, fn ($q) => $q->where('guru_id', $guru->id))
            ->with(['students', 'guru'])
            ->get();

        if ($subjects->isEmpty()) {
            // Fallback: gunakan seluruh subject jika tidak ditemukan
            $subjects = Subject::query()->with(['students', 'guru'])->get();
            if ($subjects->isEmpty()) return;
        }

        $types = ['UTS', 'UTS', 'UTS', 'UAS', 'UAS']; // total 5

        foreach ($types as $type) {
            $subject = $subjects->random();

            $startAt = now()->subDays(1)->setTime(9, 0); // sudah lewat supaya submitted_at masuk
            $endAt = (clone $startAt)->addHours(2);

            $exam = Exam::create([
                'subject_id' => $subject->id,
                'creator_id' => $subject->guru_id,
                'type' => $type,
                'title' => ($type === 'UTS' ? 'Ujian Tengah ' : 'Ujian Akhir ') . $subject->code,
                'description' => 'Ujian ' . $type . ' untuk mata kuliah ' . $subject->name . '.',
                'start_at' => $startAt,
                'end_at' => $endAt,
            ]);

            $students = $subject->students()->inRandomOrder()->take(3)->get();
            foreach ($students as $student) {
                ExamResult::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'score' => fake()->numberBetween(65, 100),
                    'submitted_at' => (clone $startAt)->addHour(),
                ]);
            }
        }
    }
}