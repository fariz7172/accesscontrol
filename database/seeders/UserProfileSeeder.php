<?php

namespace Database\Seeders;

use App\Models\userProfileModel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $faker = Faker::create();

        // // Define common attributes
        // $commonAttributes = [
        //     'END_DATE' => '2079-12-31 23:59:59',
        //     'Branchid' => 1,
        //     'photo' => null, // Assuming photo is null as no specific data is provided
        //     'timezone' => 0,
        //     'PIN' => 1,
        //     'shiftpatternID' => 1,
        // ];

        // // Create 10 records with Depid = 1
        // for ($i = 0; $i < 10; $i++) {
        //     userProfileModel::create(array_merge($commonAttributes, [
        //         'NAME' => $faker->name,
        //         'BEGIN_DATE' => Carbon::today()->setTime($faker->numberBetween(0, 23), $faker->numberBetween(0, 59), $faker->numberBetween(0, 59))->format('Y-m-d H:i:s'),
        //         'Depid' => 1,
        //         'Card' => $faker->unique()->numerify('########'),
        //         'NoIdentitas' => $faker->unique()->numerify('############'),
        //         'MemberNo' => $faker->unique()->numerify('########'),
        //         'BIRTHDAY' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
        //     ]));
        // }

        // // Create 50 records with Depid = 2
        // for ($i = 0; $i < 50; $i++) {
        //     userProfileModel::create(array_merge($commonAttributes, [
        //         'NAME' => $faker->name,
        //         'BEGIN_DATE' => Carbon::today()->setTime($faker->numberBetween(0, 23), $faker->numberBetween(0, 59), $faker->numberBetween(0, 59))->format('Y-m-d H:i:s'),
        //         'Depid' => 2,
        //         'Card' => $faker->unique()->numerify('########'),
        //         'NoIdentitas' => $faker->unique()->numerify('############'),
        //         'MemberNo' => $faker->unique()->numerify('########'),
        //         'BIRTHDAY' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
        //     ]));
        // }
    }
}
