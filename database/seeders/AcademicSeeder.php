<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SemesterGrade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::query()->with(['students', 'guru'])->get();
        if ($subjects->isEmpty()) {
            return;
        }

        $subjects->each(function (Subject $subject) {
            $students = $subject->students()->get();

            $assignment = Assignment::create([
                'subject_id' => $subject->id,
                'guru_id' => $subject->guru_id,
                'title' => 'Tugas ' . $subject->code,
                'description' => 'Kerjakan analisis kasus terkait ' . Str::lower($subject->name) . '.',
                'due_at' => now()->addDays(5),
            ]);

            $exam = Exam::create([
                'subject_id' => $subject->id,
                'creator_id' => $subject->guru_id,
                'title' => 'Ujian Tengah ' . $subject->code,
                'description' => 'Ujian evaluasi pemahaman materi ' . Str::lower($subject->name) . '.',
                'start_at' => now()->addDays(7)->setTime(9, 0),
                'end_at' => now()->addDays(7)->setTime(11, 0),
            ]);

            foreach (range(0, 2) as $offset) {
                $date = now()->subDays($offset)->toDateString();

                $students->each(function (User $student) use ($subject, $date) {
                    Attendance::create([
                        'subject_id' => $subject->id,
                        'student_id' => $student->id,
                        'recorded_by' => $subject->guru_id,
                        'attendance_date' => $date,
                        'status' => Arr::random(['hadir', 'hadir', 'izin', 'sakit', 'alpa']),
                        'notes' => fake()->boolean(20) ? fake()->sentence(6) : null,
                    ]);
                });
            }

            $students->random(min(5, $students->count()))
                ->each(function (User $student) use ($assignment) {
                    AssignmentSubmission::create([
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                        'answer' => fake()->paragraph(2),
                        'score' => fake()->numberBetween(70, 100),
                        'feedback' => fake()->sentence(),
                        'submitted_at' => now()->subDays(fake()->numberBetween(0, 3)),
                    ]);
                });

            $students->random(min(6, $students->count()))
                ->each(function (User $student) use ($exam) {
                    ExamResult::create([
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'score' => fake()->numberBetween(65, 100),
                        'submitted_at' => now()->addDays(7)->setTime(10, 30),
                    ]);
                });

            $students->each(function (User $student) use ($subject) {
                SemesterGrade::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'score' => fake()->numberBetween(70, 98),
                    'semester' => Arr::random(['Ganjil', 'Genap']),
                    'notes' => fake()->boolean(30) ? fake()->sentence() : null,
                    'recorded_by' => $subject->guru_id,
                ]);
            });
        });
    }
}