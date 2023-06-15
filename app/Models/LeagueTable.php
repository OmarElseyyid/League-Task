<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'points',
        'goals_scored',
        'goals_conceded',
        'goal_difference',
    ];

    public function team()
    {
        return $this->belongsTo('App\Models\Team');
    }

}
