<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeagueTable>
 */
class LeagueTableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $teamId = 1;

        return [
            'team_id' => $teamId++,
            'points' => 0,
            'goals_scored' => 0,
            'goals_conceded' => 0,
            'goal_difference' => 0,
        ];
    }
}
