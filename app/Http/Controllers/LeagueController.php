<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Matche;
use App\Models\LeagueTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;


class LeagueController extends Controller
{
    private const HOME_MATCH_ADVANTAGE = 20;
    private const WIN_POINTS = 3;
    private const DRAW_POINTS = 1;
    private const WIN_STRENGTH_INCREASE = 5;
    private const LOSS_STRENGTH_DECREASE = 5;
    private const DRAW_STRENGTH_INCREASE = 3;
    private const LOSE_IN_HOME_DECREASE = 10;

    public function index()
    {
        $param = Team::all();
        $component = 'teams';
        return view('league.home', compact('param', 'component'));
    }
    public function generateFixtures()
    {
        try {
            // Get all teams
            $teams = Team::all();
            // Shuffle the teams
            $shuffledTeamIds = $teams->shuffle()->pluck('id');
            // Get the number weeks
            $weeks = (count($teams) - 1) * 2;
            // Clear existing matches if any
            Matche::truncate();
        
            $matches = [];
            $usedWeeks = [];
            
            // Generate matches for the home round
            for ($week = 1; $week <= $weeks / 2; $week++) {
                $usedWeeks[] = $week;   
                for ($i = 0; $i < count($shuffledTeamIds) / 2; $i++) {
                    $homeTeamId = $shuffledTeamIds[$i];
                    $awayTeamId = $shuffledTeamIds[count($shuffledTeamIds) - 1 - $i];
                    // Create home match
                    $match = [
                        'home_team_id' => $homeTeamId,
                        'away_team_id' => $awayTeamId,
                        'week_number' => $week,
                    ];
                    $matches[] = $match;
                }
                // Rotate the teams
                $shuffledTeamIds->splice(1, 0, $shuffledTeamIds->pop());
            }  

            // Generate matches for the away round
            for ($week = $weeks / 2 + 1; $week <= $weeks; $week++) {
                // Check if the week number is already used
                if (in_array($week, $usedWeeks)) {
                    // Skip this week and move to the next one
                    continue;
                }  
                $usedWeeks[] = $week;  
                for ($i = 0; $i < count($shuffledTeamIds) / 2; $i++) {
                    $homeTeamId = $shuffledTeamIds[$i];
                    $awayTeamId = $shuffledTeamIds[count($shuffledTeamIds) - 1 - $i];
            
                    // Create away match
                    $match = [
                        'home_team_id' => $awayTeamId,
                        'away_team_id' => $homeTeamId,
                        'week_number' => $week,
                    ];
                    $matches[] = $match;
                }   
                // Rotate the teams
                $shuffledTeamIds->splice(1, 0, $shuffledTeamIds->pop());
            }     
            // Save matches to the database
            Matche::insert($matches);       

            return response()->json(['message' => 'Fixtures generated successfully']);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Fixtures generation failed']);
        }
        
    }
    public function listFixtures()
    {
        $matches = Matche::with(['homeTeam', 'awayTeam'])->get();
        // Group matches by week number
        $param = $matches->groupBy('week_number');
        $component = 'fixtures';
        return view('league.home', compact('param' , 'component'));        
    }

    public function simulate(){

        $currentWeek = Session::get('currentWeek', 1);
        $teams = Team::with('leagueTable')->with('homeMatches')->with('awayMatches')->get();      
        foreach ($teams as $team) {
            $team->prediction = $this->calculateChampionshipRate($team);
            
            $team->wins = $team->statistics()['wins'];
            $team->draws = $team->statistics()['draw'];
            $team->losses = $team->statistics()['lose'];
        }

        $teams = $this->normalizeChampionshipRates($teams);
        // Sort the teams based on their prediction rate 
        $teams = $teams->sortByDesc('prediction')->values();

        $fixtures = Matche::with('homeTeam', 'awayTeam')->where('week_number', $currentWeek)->get();
        $weekAllow = Session::get('weekAllow', 0);
        //dd($fixtures);
        return view('league.simulation', compact('currentWeek', 'teams', 'fixtures', 'weekAllow'));
    }

    public function playNextWeek()
    {
        // Get the current week from the session
        $currentWeek = Session::get('currentWeek', 1);
        // Get the Teams will play this week from the database
        $matches = Matche::where('week_number', $currentWeek)->get();
        // Adjust the teams strength based on the home match advantage
        foreach ($matches as $match) {
            $match->homeTeam->strength += self::HOME_MATCH_ADVANTAGE;
            $match->homeTeam->save();
        }
        // Simulate the matches
        foreach ($matches as $matche) {
            //Based on the teams strength calculate the probability of winning
            $homeTeamGoals = $this->calculateWinRatingEachTeam($matche->homeTeam);
            $awayTeamGoals = $this->calculateWinRatingEachTeam($matche->awayTeam);
            // Simulate the match
            $matche->home_team_goals = $homeTeamGoals;
            $matche->away_team_goals = $awayTeamGoals;
            $matche->played = 1;
            $matche->save();
            // Update the teams status and points and strength and etc..
            $this->updateTeamsStats($matche->homeTeam, $matche->awayTeam, $homeTeamGoals, $awayTeamGoals);
        }
        // Store the next week in the session
        if ($currentWeek < Matche::max('week_number')) {
            Session::put('currentWeek', $currentWeek+1);
        }
        else{
            Session::put('weekAllow', 1);
        }
        return response()->json(['message' => 'Week played successfully']);
    }

    private function calculateWinRatingEachTeam($team)
    {
        $strength = $team->strength;
        // Minimum and maximum possible strengths
        $minStrength = 0; 
        $maxStrength = 100;   
        // Calculate the win rating based on the team strength
        $winRating = ($strength - $minStrength) / ($maxStrength - $minStrength) * 100;
        $randomFactor = rand(1, 20) / 100; // Random factor between 0.01 and 0.20
        $winRating += $randomFactor;
        $goalsCount = round(($winRating / 100) * 10);// Adjust the goals count based on the win rating
        return $goalsCount;
    }

    private function updateTeamsStats($homeTeam, $awayTeam ,$homeGoals, $awayGoals){
        // Update the teams stats based on the match result
        if ($homeGoals > $awayGoals) {

            // Home team won
            $homeTeam->strength += self::WIN_STRENGTH_INCREASE;
            $awayTeam->strength -= self::LOSS_STRENGTH_DECREASE;
            
            // Home Team (winner)
            $league_table = LeagueTable::where('team_id', $homeTeam->id)->first();
            $league_table->points += self::WIN_POINTS;
            $league_table->goals_scored += $homeGoals;
            $league_table->goals_conceded += $awayGoals;
            $league_table->goal_difference = $league_table->goals_scored - $league_table->goals_conceded;

            // Away Team (loser)
            $league_table_loser = LeagueTable::where('team_id', $awayTeam->id)->first();
            $league_table_loser->goals_scored += $awayGoals;
            $league_table_loser->goals_conceded += $homeGoals;
            $league_table_loser->goal_difference = $league_table_loser->goals_scored - $league_table_loser->goals_conceded;

        } elseif ($homeGoals < $awayGoals) {
            // Away team won
            $awayTeam->strength += self::WIN_STRENGTH_INCREASE;
            $homeTeam->strength -= self::LOSS_STRENGTH_DECREASE + self::LOSE_IN_HOME_DECREASE;

            $league_table = LeagueTable::where('team_id', $awayTeam->id)->first();
            $league_table->points += self::WIN_POINTS;
            $league_table->goals_scored += $awayGoals;
            $league_table->goals_conceded += $homeGoals;
            $league_table->goal_difference = $league_table->goals_scored - $league_table->goals_conceded;

            // Home Team (loser)
            $league_table_loser = LeagueTable::where('team_id', $homeTeam->id)->first();
            $league_table_loser->goals_scored += $homeGoals;
            $league_table_loser->goals_conceded += $awayGoals;
            $league_table_loser->goal_difference = $league_table_loser->goals_scored - $league_table_loser->goals_conceded;

        } else {
            // Draw
            $homeTeam->strength += self::DRAW_STRENGTH_INCREASE;
            $awayTeam->strength += self::DRAW_STRENGTH_INCREASE;

            $league_table = LeagueTable::where('team_id', $homeTeam->id)->first();
            $league_table->points += self::DRAW_POINTS;
            $league_table->goals_scored += $homeGoals;
            $league_table->goals_conceded += $awayGoals;
            $league_table->goal_difference = $league_table->goals_scored - $league_table->goals_conceded;

            // Away Team (loser)
            $league_table_loser = LeagueTable::where('team_id', $awayTeam->id)->first();
            $league_table->points += self::DRAW_POINTS;
            $league_table_loser->goals_scored += $awayGoals;
            $league_table_loser->goals_conceded += $homeGoals;
            $league_table_loser->goal_difference = $league_table_loser->goals_scored - $league_table_loser->goals_conceded;

        }
        $homeTeam->save();
        $awayTeam->save();
        $league_table->save();
        $league_table_loser->save();
    }

    public function playAllWeeks()
    {
        $currentWeek = Session::get('currentWeek', 1);
        $totalWeeks = Matche::max('week_number');

        for ($week = $currentWeek; $week <= $totalWeeks; $week++) {
            $this->playNextWeek();
        }
        
        return response()->json(['message' => 'All weeks played successfully']);
    }

    private function calculateChampionshipRate($team)
    {
        // 12 possible wins 100/12 = 8.33 points for each old win, 12 possible draws 50/12 = 4.16 points for each old draw, 12 possible losses 0/12 = 0 points for each old loss. Matches old resaults will take 50% of the global rate
        // Strength will take 20% of the global rate. the strength will be modified to be between 0 and 100 for each team
        // The most goals scored will take 20% of the global rate
        // The least goals conceded will take 10% of the global rate

        $resultWeight = 0.5;
        $strengthWeight = 0.2;
        $goalsScoredWeight = 0.2;
        $goalsConcededWeight = 0.1;
    
        $statistics = $team->statistics();
        $wins = $statistics['wins'] ?? 0;
        $draws = $statistics['draw'] ?? 0;
        $losses = $statistics['lose'] ?? 0;
    
        $oldResultsRate = ($wins * 8.33 + $draws * 4.16 + $losses * 0) / 12;
    
        $strength = $team->strength;
        $strengthRate = $strength * $strengthWeight;
    
        $goalsScored = $statistics['goalsScored'] ?? 0;
        $goalsScoredRate = ($goalsScored / 12) * $goalsScoredWeight;
    
        $goalsConceded = $statistics['goalsConceded'] ?? 0;
        $goalsConcededRate = ((12 - $goalsConceded) / 12) * $goalsConcededWeight;
    
        $championshipRate = ($oldResultsRate + $strengthRate + $goalsScoredRate + $goalsConcededRate) * $resultWeight;
    

        //dd($wins, $draws, $losses, $oldResaultsRate, $strength, $strengthRate, $goalsScored, $goalsScoredRate, $goalsConceded, $goalsConcededRate, $championshipRate);

        return $championshipRate;
    }

    private function normalizeChampionshipRates($teams)
    {
        $totalRate = 0;

        // Total rate
        foreach ($teams as $team) {
            $championshipRate = $this->calculateChampionshipRate($team);
            $totalRate += $championshipRate;
        }

        // Normalize the rates based on the total rate
        foreach ($teams as $team) {
            $championshipRate = $this->calculateChampionshipRate($team);
            $normalizedRate = ($championshipRate / $totalRate) * 100;
            $team->prediction = round($normalizedRate, 2);
        }

        return $teams;
    }

    public function resetData()
    {
        
        DB::beginTransaction();
        try {
            $teams = Team::all();
            foreach ($teams as $team) {
                $team->strength = 0;
                $team->save();
            }
     
            $table = LeagueTable::all();
            foreach ($table as $teamRow) {
                $teamRow->points = 0;
                $teamRow->goals_scored= 0;
                $teamRow->goals_conceded = 0;
                $teamRow->goal_difference = 0;
                $teamRow->save();
            }

            DB::commit();

            // Turncate causes error if i use it before commit
            Matche::truncate();
            session()->flush();

            return response()->json(['message' => 'Reset successfully']);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => 'Reset failed ' . $th]);
        }
    }
}
    
