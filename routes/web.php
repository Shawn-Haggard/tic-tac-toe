<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/board/{id}', 'BoardController@getBoard');

Route::post('/board', 'BoardController@newBoard');

Route::get('/board', 'BoardController@listBoards');

Route::put('/board/{id}', 'BoardController@updateBoard');

Route::get('/reset', function (){
    Session::flush();
});

Route::post('/board/{id}/end', 'BoardController@endGame');

Route::post('/board/{id}/name', 'BoardController@setName');

Route::get('/leaderboard', 'LeaderboardController@list');
// Route::get('/board/leaders', 'BoardController@leaders');
