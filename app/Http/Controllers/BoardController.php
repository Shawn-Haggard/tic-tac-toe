<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Board;

class BoardController extends Controller
{

    public function listBoards(Request $request){
        if($request->session()->has('board')){
            return $request->session()->get('board');
        }else{
            return [];
        }
    }

    public function newBoard(Request $request){
        $board = new Board();
        $board->code = strtoupper(bin2hex(random_bytes(4)));
        $board->save();
        $request->session()->put('board.' . $board->code . '.token', -1); // Make the game creator go first 
        $request->session()->put('board.' . $board->code . '.code', $board->code); // Make the game creator go first 
        // var_dump($request->session()->get('board'));
        return $board;
    }

    public function getBoard(Request $request, $id){
        $board = Board::where('code', strval($id))->first();
        if($board == null){
            return [];
        }

        if(!$request->session()->has('board.' . $id . '.token')){
            $request->session()->put('board.' . $id . '.token', 1);
        }
        $board->token = $request->session()->get('board.' . $id . '.token');
        return $board;
    }

    public function updateBoard(Request $request, $id){
        $board = Board::where('code', strval($id))->first();

        if($request->session()->has('board.' . $id . '.token')){
            $token = $request->session()->get('board.' . $id . '.token');
        }else{
            abort(401);
        }

        $turn = $token * -1;
        $board->turn = $turn;
        
        $updated_board = $request->get('board');
        $original_board = $board->board;
        $board_update = $request->get('data');
        $original_board[$board_update['row']][$board_update['col']] = $token;

        if ($updated_board != $original_board) {
            var_dump($token);
            var_dump($board->board);
            var_dump($updated_board);
            var_dump($original_board);
            var_dump($board_update);
            abort(400);
        }

        $board->board = $updated_board;
        $board->save();
        if($board == null){
            return [];
        }
        return $board;
    }
    
}
