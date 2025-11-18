<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan satu guru default tersedia untuk login demo
        $defaultGuru = User::query()->where('email', 'guru@radiologi.test')->first();
        if (!$defaultGuru) {
            $defaultGuru = User::factory()
                ->guru()
                ->create([
                    'name' => 'Dr. Maya Lestari',
                    'email' => 'guru@radiologi.test',
                    'nip' => 'GURU001',
                ]);
        } else {
            $defaultGuru->update([
                'name' => $defaultGuru->name ?: 'Dr. Maya Lestari',
                'nip' => 'GURU001',
            ]);
        }

        // Tambah 5 guru lainnya (email acak, agar tidak bentrok)
        User::factory()
            ->count(5)
            ->guru()
            ->sequence(
                ['name' => 'Dr. Rahmat Pratama'],
                ['name' => 'Dr. Intan Safira'],
                ['name' => 'Dr. Bima Nugraha'],
                ['name' => 'Dr. Sari Wulandari'],
                ['name' => 'Dr. Dedi Prakoso'],
            )
            ->create();
    }
}