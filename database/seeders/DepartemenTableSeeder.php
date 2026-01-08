<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartemenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departemen')->insert([
            [
                'id' => 1,
                'number' => '1',
                'name' => 'IT',
                'description' => 'IT Depaartment',
            ],
        ]);
    }
}
