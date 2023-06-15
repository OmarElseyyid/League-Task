<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Matche;
use App\Models\LeagueTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class LeagueControllerTest extends TestCase
{
    use WithoutMiddleware;
    use RefreshDatabase;
    public function testGenerateFixtures()
    {
        $teams = Team::factory()->count(4)->create();
        $tables = LeagueTable::factory()->count(4)->create();

        $response = $this->post('/generate-fixtures');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Fixtures generated successfully']);

        $weeks = (Count(Team::all())-1)*2;
        $match = $weeks*2; // 2 matches per week

        $this->assertCount($match, Matche::all());
    }
    
    public function testListFixtures()
    {
        $response = $this->post('/generate-fixtures');
        
        $response = $this->get('/fixtures');

        $response->assertStatus(200);
        $response->assertViewHas('param');
        $response->assertViewHas('component', 'fixtures');
    }
    
    public function testPlayNextWeek()
    {        
        
        $response = $this->post('/play-next-week');
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Week played successfully']);
        
        $this->assertCount(2, Team::where('strength', '>', 0)->get());
        $this->assertCount(2, LeagueTable::where('points', '>', 0)->get());
    }
    
    public function testPlayAllWeeks()
    {
        $response = $this->post('/play-all-weeks');
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'All weeks played successfully']);
        
        $this->assertCount(12, Matche::where('played', 1)->get());
    }

    public function testResetData()
    {
        $response = $this->post('/reset');
        $response->assertStatus(200);
        $this->assertCount(4, Team::where('strength', 0)->get());
        $this->assertCount(0, Matche::all());
        $this->assertCount(4, LeagueTable::all());
    }
}


