<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil daftar guru yang sudah disediakan oleh GuruSeeder
        $gurus = User::query()->where('role', 'guru')->orderBy('name')->get();
        if ($gurus->count() < 6) {
            // Jika belum ada, jangan error — cukup keluar agar seeding lain tetap jalan
            return;
        }

        $subjects = [
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
        ];

        foreach ($subjects as $data) {
            Subject::query()->updateOrCreate([
                'code' => $data['code'],
            ], $data);
        }
    }
}