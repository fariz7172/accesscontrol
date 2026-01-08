<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartemenTableSeeder::class,
            BranchTableSeeder::class,
            UserloginTableSeeder::class,
            ApiSeeder::class,
            DayzoneTableSeeder::class,
        ]);
    }
}
