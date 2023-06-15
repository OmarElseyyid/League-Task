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

    public function matches()
    {
        return $this->hasMany('App\Models\Match', 'home_team_id', 'id')
            ->orWhere('away_team_id', $this->id);
    }

    public function homeMatches()
    {
    return $this->hasMany('App\Models\Match', 'home_team_id');
    }

    public function awayMatches()
    {
        return $this->hasMany('App\Models\Match', 'away_team_id');
    }

    public function leagueTable()
    {
        return $this->hasOne('App\Models\LeagueTable');
    }

}
