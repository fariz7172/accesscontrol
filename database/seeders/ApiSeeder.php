<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apis = [
            [
                'id' => 1,
                'name' => 'http://127.0.0.1:8000/api/soyal/book/v1/1/adduser',
                'desc' => 'URL ADD USER SOYAL MECHINE',
            ],
            [
                'id' => 2,
                'name' => 'http://localhost:787/sztimmy/registerface',
                'desc' => 'ADD USER RESGISTER TASOFT',
            ],
            [
                'id' => 3,
                'name' => 'http://localhost:787/sztimmy/useraccess',
                'desc' => 'User Access Tasoft',
            ],
            [
                'id' => 4,
                'name' => 'http://localhost:787/sztimmy/getuserlist',
                'desc' => 'Get User List Tasoft',
            ],
            [
                'id' => 5,
                'name' => 'http://127.0.0.1:8000/api/soyal/book/v1/1/deleteuser',
                'desc' => 'Delete User Soyal Mechine',
            ],
            [
                'id' => 6,
                'name' => 'http://localhost:787/sztimmy/deleteface',
                'desc' => 'Delete User Tasoft',
            ],
            [
                'id' => 7,
                'name' => 'http://localhost:8080/api/members',
                'desc' => 'Sync Member Dreampos',
            ],
            [
                'id' => 8,
                'name' => 'http://localhost:8080/api/view-members',
                'desc' => 'Get Data Member Dreampos',
            ],
            [
                'id' => 9,
                'name' => 'http://localhost:787/sztimmy/opengate',
                'desc' => 'OPEN GATE',
            ],
            [
                'id' => 10,
                'name' => 'http://localhost:787/sztimmy/cleanlogs',
                'desc' => 'cLEAN',
            ],
            [
                'id' => 11,
                'name' => 'http://localhost:787/sztimmy/gettime',
                'desc' => 'Get Active Mechine',
            ],
            [
                'id' => 12,
                'name' => 'http://localhost:787/sztimmy/settime',
                'desc' => 'Set Time',
            ],
            [
                'id' => 13,
                'name' => 'http://localhost:787/sztimmy/reboot',
                'desc' => 'Reboot',
            ],
            [
                'id' => 14,
                'name' => 'http://localhost:787/sztimmy/dayzone',
                'desc' => 'DayZone',
            ],
            [
                'id' => 15,
                'name' => 'http://localhost:787/sztimmy/weekzone',
                'desc' => 'WeekZone',
            ],
            [
                'id' => 16,
                'name' => 'http://localhost:787/sztimmy/userWeekzone',
                'desc' => 'User WeekZone',
            ],
        ];

        DB::table('api')->insert($apis);
    }
}