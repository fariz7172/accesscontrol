<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class logData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $data = [];

        for ($i = 0; $i < 200; $i++) {
            $data[] = [
                'USER_ADDR' => "1",
                'TM_EVENT' => now(),
                'DEVICESN' => "AYSK17105912",
                'IMGPATH' => "gambar", // Nullable dengan 30% chance kosong
                'devicetype' => $faker->optional(0.8)->numberBetween(1, 5), // Nullable dengan 20% chance kosong
                'card' => "2113", // Nullable dengan 40% chance kosong
            ];
        }

        DB::table('tbl_userlog')->insert($data);
    }
}