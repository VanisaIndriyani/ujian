<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        // Hindari duplikasi bila seeding dijalankan berulang
        $exists = User::query()->where('email', 'guru@radiologi.test')->first();
        if (!$exists) {
            User::factory()
                ->guru()
                ->create([
                    'name' => 'Dr. Maya Lestari',
                    'email' => 'guru@radiologi.test',
                    'nip' => 'GURU001',
                ]);
        }
    }
}