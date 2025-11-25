<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hanya seed admin dan guru untuk login
        $this->call([
            AdminSeeder::class,
            GuruSeeder::class,
        ]);
    }
}
