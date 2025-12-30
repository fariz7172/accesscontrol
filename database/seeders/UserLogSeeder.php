<?php

namespace Database\Seeders;

use App\Models\userLogModel;
use App\Models\userProfileModel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $faker = Faker::create();

        // // Get all user profiles
        // $userProfiles = userProfileModel::all();

        // foreach ($userProfiles as $userProfile) {
        //     // Generate a random number of log entries (1 to 5) for each user
        //     $logCount = $faker->numberBetween(1, 5);

        //     for ($i = 0; $i < $logCount; $i++) {
        //         userLogModel::create([
        //             'USER_ADDR' => $userProfile->ID,
        //             'TM_EVENT' => Carbon::today()->setTime(
        //                 $faker->numberBetween(0, 23),
        //                 $faker->numberBetween(0, 59),
        //                 $faker->numberBetween(0, 59)
        //             )->format('Y-m-d H:i:s'),
        //             'DEVICESN' => 'ZYSL20032920',
        //             'IMGPATH' => null,
        //             'devicetype' => 1,
        //             'card' => $userProfile->Card,
        //         ]);
        //     }
        // }
    }
}
