<?php

namespace Database\Seeders;

use App\Models\Classroom;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Seed daftar kelas terlebih dahulu agar tersedia di UI
            (new ClassroomSeeder())->run();
            // Tambahkan contoh ujian UTS/UAS dengan hasil agar halaman guru/grades terisi
            (new ExamTypeSeeder())->run();
            $admin = User::factory()
                ->admin()
                ->create([
                    'name' => 'Admin Radiologi',
                    'email' => 'admin@radiologi.test',
                ]);

            $gurus = User::factory()
                ->count(6)
                ->guru()
                ->sequence(
                    ['name' => 'Dr. Maya Lestari'],
                    ['name' => 'Dr. Rahmat Pratama'],
                    ['name' => 'Dr. Intan Safira'],
                    ['name' => 'Dr. Bima Nugraha'],
                    ['name' => 'Dr. Sari Wulandari'],
                    ['name' => 'Dr. Dedi Prakoso'],
                )
                ->create();

            $murids = User::factory()
                ->count(5)
                ->murid()
                ->sequence(
                    ['name' => 'Budi Santoso'],
                    ['name' => 'Dewi Kartika'],
                    ['name' => 'Rizky Ramadhan'],
                    ['name' => 'Aulia Putri'],
                    ['name' => 'Nadia Safitri'],
                )
                ->create();

            $gurus->first()->update([
                'nip' => 'GURU001',
                'email' => 'guru@radiologi.test',
            ]);

            // Set identitas dan kelas untuk murid
            $kelas = Classroom::pluck('name')->all() ?: ['Radiologi 2025 A', 'Radiologi 2025 B'];
            foreach ($murids as $idx => $m) {
                $m->update([
                    'nisn' => 'MHS' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                    'email' => $idx === 0 ? 'mahasiswa@radiologi.test' : null,
                    'classroom' => Arr::random($kelas),
                ]);
            }

            $subjects = collect([
                [
                    'name' => 'Dasar Radiologi',
                    'code' => 'RAD101',
                    'description' => 'Pengantar radiologi dan prinsip dasar pencitraan.',
                    'guru_id' => $gurus[0]->id,
                ],
                [
                    'name' => 'Anatomi Pencitraan',
                    'code' => 'RAD102',
                    'description' => 'Studi anatomi melalui berbagai modalitas pencitraan.',
                    'guru_id' => $gurus[1]->id,
                ],
                [
                    'name' => 'Radiologi Intervensi',
                    'code' => 'RAD103',
                    'description' => 'Teknik lanjut radiologi intervensi dan aplikasinya.',
                    'guru_id' => $gurus[2]->id,
                ],
                [
                    'name' => 'Fisik Pencitraan Medis',
                    'code' => 'RAD104',
                    'description' => 'Dasar fisika untuk CT, MRI, USG dan X-Ray.',
                    'guru_id' => $gurus[3]->id,
                ],
                [
                    'name' => 'Proteksi Radiasi',
                    'code' => 'RAD105',
                    'description' => 'Keselamatan kerja dan proteksi radiasi klinis.',
                    'guru_id' => $gurus[4]->id,
                ],
                [
                    'name' => 'Pencitraan Lanjut MRI',
                    'code' => 'RAD106',
                    'description' => 'Teknik dan protokol MRI tingkat lanjut.',
                    'guru_id' => $gurus[5]->id,
                ],
            ])->map(fn (array $data) => Subject::create($data));

            $murids->each(function (User $student) use ($subjects) {
                $subjects->random(min(3, $subjects->count()))->each(function (Subject $subject) use ($student) {
                    $subject->students()->syncWithoutDetaching([$student->id]);
                });
            });

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
        });
    }
}
