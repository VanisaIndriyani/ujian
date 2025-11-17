<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            'Radiologi 2025 A',
            'Radiologi 2025 B',
            'Radiologi 2025 C',
            'Radiologi 2024 A',
            'Radiologi 2024 B',
            'Radiologi 2023 A',
        ];

        foreach ($classes as $name) {
            Classroom::query()->updateOrCreate([
                'name' => $name,
            ], [
                'name' => $name,
            ]);
        }
    }
}