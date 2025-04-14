<?php

namespace database\seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Chelsea',
                'strength' => 80,
            ],
            [
                'name' => 'Arsenal',
                'strength' => 70,
            ],
            [
                'name' => 'Manchester City',
                'strength' => 65,
            ],
            [
                'name' => 'Liverpool',
                'strength' => 50,
            ],
        ];

        Team::insert($teams);
    }
}
