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
    private const MATCH_SUPPORTER_STRENGTH = 5;
    private const MATCH_GOALKEEPER_FACTOR = 3;


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
        $teams = Team::all();
        $fixtures = Matche::with('homeTeam', 'awayTeam')->where('week_number', $currentWeek)->get();
        //dd($fixtures);
        return view('league.simulation', compact('currentWeek', 'teams', 'fixtures'));
    }


    public function getData()
    {
        // Retrieve the league table data from the database
        $leagueTable = LeagueTable::get();
        // Retrieve the current week from the session
        $currentWeek = Session::get('currentWeek', 1);
        // Retrieve the current week matches data from the database
        $currentWeekMatches = Matche::where('week_number', $currentWeek)->get();
    
        // Calculate the championship predictions
        $championshipPredictions = $this->calculateChampionshipPredictions();
    
        return response()->json([
            'leagueTable' => $leagueTable,
            'currentWeekMatches' => $currentWeekMatches,
            'championshipPredictions' => $championshipPredictions,
        ]);
    }

    private function calculateChampionshipPredictions()
    {
        // Logic to calculate championship predictions
        // Replace this with your actual calculation logic
        // Return the calculated predictions
    }
    public function playNextWeek()
    {
        // Get the current week from the session
        $currentWeek = Session::get('currentWeek', 1);

        // Increment the current week by 1
        $nextWeek = $currentWeek + 1;

        // Store the next week in the session
        Session::put('currentWeek', $nextWeek);

        // Redirect or return a response as needed
    }

    public function playAllWeeks()
    {
        // Get the current week from the session
        $currentWeek = Session::get('currentWeek', 1);

        // Iterate over each week and simulate the matches
        $totalWeeks = 12;

        for ($week = $currentWeek; $week <= $totalWeeks; $week++) {
            $this->playNextWeek();
        }

        // Redirect or return a response as needed
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
            DB::commit();

            // Turncate causes error if i use it before commit
            Matche::truncate();
            LeagueTable::truncate();

            return response()->json(['message' => 'Reset successfully']);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => 'Reset failed ' . $th]);
        }
    }
}
    
