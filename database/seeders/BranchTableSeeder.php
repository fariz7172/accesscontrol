<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('branch')->insert([
            [
                'id' => 1,
                'number' => 1,
                'name' => 'IT CABANG PEMBANTU',
                'description' => 'Kantor IT',
            ],
        ]);
    }
}
