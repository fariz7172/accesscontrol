<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserloginTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('userlogin')->insert([
            [
                'id' => 1,
                'username' => 'admin',
                'password' => '$2y$12$fnOu9hpQegB7ccGaXdt7WO7C/RQsXbZNitvPUllvjENSwpz5T8Y66',
                'priv' => json_encode([
                    'user' => true,
                    'device' => true,
                    'log' => true,
                    'setting' => true,
                    'userAdmin' => true,
                    'attendance' => true,
                ]),
                'bactive' => '{"user":true,"device":true,"log":true,"setting":true,"userAdmin":true,"attendance":true}',
            ],
        ]);
    }
}
