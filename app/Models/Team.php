<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'strength',
    ];

    public function homeMatches()
    {
        return $this->hasMany('App\Models\Matche', 'home_team_id');
    }

    public function awayMatches()
    {
        return $this->hasMany('App\Models\Matche', 'away_team_id');
    }

    public function leagueTable()
    {
        return $this->hasOne('App\Models\LeagueTable');
    }
    
    public function statistics(){
        $homeMatches = $this->homeMatches;
        $awayMatches = $this->awayMatches;
        
        $wins = 0;
        $lose = 0;
        $draw = 0;
        $goalsScored = 0;
        $goalsConceded = 0;

    
        foreach ($homeMatches as $match) {
            if ($match->home_team_goals > $match->away_team_goals) {
                $wins += 1;
            } elseif ($match->home_team_goals < $match->away_team_goals) {
                $lose += 1;
            } else {
                $draw += 1;
            } 
            $goalsScored += $match->home_team_goals;
            $goalsConceded += $match->away_team_goals;
        }
    
        foreach ($awayMatches as $match) {
            if ($match->away_team_goals > $match->home_team_goals) {
                $wins += 1;
            } elseif ($match->away_team_goals < $match->home_team_goals) {
                $lose += 1;
            } else {
                $draw += 1;
            }
            $goalsScored += $match->away_team_goals;
            $goalsConceded += $match->home_team_goals;
        }

        $results = [
            'wins' => $wins,
            'lose' => $lose,
            'draw' => $draw,
            'goalsScored' => $goalsScored,
            'goalsConceded' => $goalsConceded,
        ];

        // Convert to json and return
        // $results = json_encode($results);

        return $results;
    }


}
