<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Hindari duplikasi bila seeding dijalankan berulang
        $exists = User::query()->where('email', 'admin@radiologi.test')->first();
        if (!$exists) {
            User::factory()
                ->admin()
                ->create([
                    'name' => 'Admin Radiologi',
                    'email' => 'admin@radiologi.test',
                ]);
        }
    }
}