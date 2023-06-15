<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', 'App\Http\Controllers\LeagueController@index');

Route::post('/generate-fixtures', 'App\Http\Controllers\LeagueController@generateFixtures')->name('generate.fixtures');
Route::get('/fixtures', 'App\Http\Controllers\LeagueController@listFixtures')->name('list.fixtures');
Route::get('/simulation', 'App\Http\Controllers\LeagueController@simulate')->name('simulation');
Route::post('/play-next-week', 'App\Http\Controllers\LeagueController@playNextWeek');
Route::post('/play-all-weeks', 'App\Http\Controllers\LeagueController@playAllWeeks');
Route::post('/reset', 'App\Http\Controllers\LeagueController@resetData');


