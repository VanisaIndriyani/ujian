<?php

namespace Database\Seeders;

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
            // 1) Kelas
            (new ClassroomSeeder())->run();
            // 2) Pengguna
            (new AdminSeeder())->run();
            (new GuruSeeder())->run();
            (new MuridSeeder())->run();
            // 3) Mata kuliah
            (new SubjectSeeder())->run();

            // 4) Enroll murid ke mata kuliah secara acak
            $subjects = Subject::all();
            $murids = User::query()->where('role', 'murid')->get();
            $murids->each(function (User $student) use ($subjects) {
                $subjects->random(min(3, $subjects->count()))->each(function (Subject $subject) use ($student) {
                    $subject->students()->syncWithoutDetaching([$student->id]);
                });
            });

            // 5) Data akademik turunan (tugas, ujian, absensi, nilai)
            (new AcademicSeeder())->run();

            // 6) Tambahan: seeding 5 ujian bertipe UTS/UAS dengan hasil
            (new ExamTypeSeeder())->run();
        });
    }
}
