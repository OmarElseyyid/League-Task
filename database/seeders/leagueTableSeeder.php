<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeagueTable;
use App\Models\Team;

class leagueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();
        foreach ($teams as $team) {
            LeagueTable::create([
                'team_id' => $team->id,
                'points' => 0,
                'goals_scored' => 0,
                'goals_conceded' => 0,
                'goal_difference' => 0,                
            ]);   
        }
    }
}
