<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matche extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'home_team_goals',
        'away_team_goals',
        'played',
    ];

    
    public function homeTeam()
    {
        return $this->belongsTo('App\Models\Team', 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo('App\Models\Team', 'away_team_id');
    }

}
