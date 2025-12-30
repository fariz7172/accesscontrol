<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dayzone;
use App\Models\DayzoneDetail;

class DayzoneTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed the dayzone table
        $dayzones = [
            ['ID' => 1, 'Name' => 'Dayzone1'],
            ['ID' => 2, 'Name' => 'Dayzone2'],
            ['ID' => 3, 'Name' => 'Dayzone3'],
            ['ID' => 4, 'Name' => 'Dayzone4'],
            ['ID' => 5, 'Name' => 'Dayzone5'],
            ['ID' => 6, 'Name' => 'Dayzone6'],
            ['ID' => 7, 'Name' => 'Dayzone7'],
            ['ID' => 8, 'Name' => 'Dayzone8'],
        ];

        foreach ($dayzones as $dayzone) {
            Dayzone::updateOrCreate(
                ['ID' => $dayzone['ID']], // Unique key
                ['Name' => $dayzone['Name']]
            );
        }

        // Seed the dayzonedetail table
        $dayzoneDetails = [
            [
                'ID' => 1,
                'dz' => 1,
                'Stz1' => '00:00:00',
                'Etz1' => '00:00:00',
                'Stz2' => '00:00:00',
                'Etz2' => '00:00:00',
                'Stz3' => '00:00:00',
                'Etz3' => '00:00:00',
                'Stz4' => '00:00:00',
                'Etz4' => '00:00:00',
                'Stz5' => '00:00:00',
                'Etz5' => '00:00:00',
            ],
            [
                'ID' => 2,
                'dz' => 2,
                'Stz1' => '00:00:00',
                'Etz1' => '00:00:00',
                'Stz2' => '00:00:00',
                'Etz2' => '00:00:00',
                'Stz3' => '00:00:00',
                'Etz3' => '00:00:00',
                'Stz4' => '00:00:00',
                'Etz4' => '00:00:00',
                'Stz5' => '00:00:00',
                'Etz5' => '00:00:00',
            ],
            [
                'ID' => 3,
                'dz' => 3,
                'Stz1' => '00:00:00',
                'Etz1' => '00:00:00',
                'Stz2' => '00:00:00',
                'Etz2' => '00:00:00',
                'Stz3' => '00:00:00',
                'Etz3' => '00:00:00',
                'Stz4' => '00:00:00',
                'Etz4' => '00:00:00',
                'Stz5' => '00:00:00',
                'Etz5' => '00:00:00',
            ],
            [
                'ID' => 4,
                'dz' => 4,
                'Stz1' => '00:00:00',
                'Etz1' => '00:00:00',
                'Stz2' => '00:00:00',
                'Etz2' => '00:00:00',
                'Stz3' => '00:00:00',
                'Etz3' => '00:00:00',
                'Stz4' => '00:00:00',
                'Etz4' => '00:00:00',
                'Stz5' => '00:00:00',
                'Etz5' => '00:00:00',
            ],
            [
                'ID' => 5,
                'dz' => 5,
                'Stz1' => '00:00:00',
                'Etz1' => '00:00:00',
                'Stz2' => '00:00:00',
                'Etz2' => '00:00:00',
                'Stz3' => '00:00:00',
                'Etz3' => '00:00:00',
                'Stz4' => '00:00:00',
                'Etz4' => '00:00:00',
                'Stz5' => '00:00:00',
                'Etz5' => '00:00:00',
            ],
            [
                'ID' => 6,
                'dz' => 6,
                'Stz1' => '00:00:00',
                'Etz1' => '00:00:00',
                'Stz2' => '00:00:00',
                'Etz2' => '00:00:00',
                'Stz3' => '00:00:00',
                'Etz3' => '00:00:00',
                'Stz4' => '00:00:00',
                'Etz4' => '00:00:00',
                'Stz5' => '00:00:00',
                'Etz5' => '00:00:00',
            ],
            [
                'ID' => 7,
                'dz' => 7,
                'Stz1' => '00:00:00',
                'Etz1' => '00:00:00',
                'Stz2' => '00:00:00',
                'Etz2' => '00:00:00',
                'Stz3' => '00:00:00',
                'Etz3' => '00:00:00',
                'Stz4' => '00:00:00',
                'Etz4' => '00:00:00',
                'Stz5' => '00:00:00',
                'Etz5' => '00:00:00',
            ],
            [
                'ID' => 8,
                'dz' => 8,
                'Stz1' => '00:00:00',
                'Etz1' => '00:00:00',
                'Stz2' => '00:00:00',
                'Etz2' => '00:00:00',
                'Stz3' => '00:00:00',
                'Etz3' => '00:00:00',
                'Stz4' => '00:00:00',
                'Etz4' => '00:00:00',
                'Stz5' => '00:00:00',
                'Etz5' => '00:00:00',
            ],
        ];

        foreach ($dayzoneDetails as $detail) {
            DayzoneDetail::updateOrCreate(
                ['ID' => $detail['ID']], // Unique key
                [
                    'dz' => $detail['dz'],
                    'Stz1' => $detail['Stz1'],
                    'Etz1' => $detail['Etz1'],
                    'Stz2' => $detail['Stz2'],
                    'Etz2' => $detail['Etz2'],
                    'Stz3' => $detail['Stz3'],
                    'Etz3' => $detail['Etz3'],
                    'Stz4' => $detail['Stz4'],
                    'Etz4' => $detail['Etz4'],
                    'Stz5' => $detail['Stz5'],
                    'Etz5' => $detail['Etz5'],
                ]
            );
        }
    }
}
