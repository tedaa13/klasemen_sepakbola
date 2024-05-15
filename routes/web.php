<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\generalController;

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

Route::get('/', [generalController::class, 'index']);
Route::post('/addTeamToDivision', [generalController::class, 'addTeamToDivision']);
Route::post('/standings', [generalController::class, 'standings']);
Route::post('/calculate', [generalController::class, 'calculateSchedule']);
Route::post('/fixtures', [generalController::class, 'fixtures']);
Route::post('/scoreSubmit', [generalController::class, 'scoreSubmit']);
Route::post('/getTeam', [generalController::class, 'getTeam']);
Route::post('/participants', [generalController::class, 'participants']);
Route::post('/list_teams', [generalController::class, 'list_teams']);