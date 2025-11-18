<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class MuridSeeder extends Seeder
{
    public function run(): void
    {
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

        // Tetapkan identitas tambahan dan kelas untuk murid
        $kelas = Classroom::pluck('name')->all() ?: ['Radiologi 2025 A', 'Radiologi 2025 B'];
        $existingDefaultStudent = User::query()->where('email', 'mahasiswa@radiologi.test')->first();
        foreach ($murids as $idx => $m) {
            $email = $m->email;
            if ($idx === 0 && !$existingDefaultStudent) {
                $email = 'mahasiswa@radiologi.test';
            }
            $desiredNisn = 'MHS' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
            $nisnInUse = User::query()->where('nisn', $desiredNisn)->exists();
            $nisn = $nisnInUse ? $m->nisn : $desiredNisn;
            $m->update([
                'nisn' => $nisn,
                'email' => $email,
                'classroom' => Arr::random($kelas),
            ]);
        }
    }
}