<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Leaderboard;

class LeaderboardController extends Controller
{
    public function list(Request $request) {
        $leaderboard = [];
        
        foreach(Leaderboard::get()->where('win', 1) as $win){
            if(!array_key_exists($win['name'], $leaderboard)){
                $leaderboard[$win['name']] = ['name' => $win['name'], 'wins' => 0];
            }
            $leaderboard[$win['name']]['wins'] ++;
        }

        // sort($leaderboard);
        usort($leaderboard, function($b, $a) {return strcmp($a['wins'], $b['wins']);});
        return $leaderboard;//$leaderboard;
    }
}
