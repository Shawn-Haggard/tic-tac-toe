<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Board;
use App\Leaderboard;

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
        $board->x_name = $request->get('name');
        $board->o_name = '';
        $board->save();
        $request->session()->put('board.' . $board->code . '.token', -1); // Make the game creator go first 
        $request->session()->put('board.' . $board->code . '.code', $board->code); // Make the game creator go first 
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

    private function checkWin($board){
        function checkRows($board){

            for($i = 0; $i < 3; $i++){
                if(abs($board[$i][0] + $board[$i][1] + $board[$i][2]) == 3){
                    return $board[$i][0];
                }
            }

            return 0;
        }

        function checkCols($board){
            for($i = 0; $i < 3; $i++){
                if(abs($board[0][$i] + $board[1][$i] + $board[2][$i]) == 3){
                    return $board[0][$i];
                }
            }
            
            return 0;
        }

        function checkDiagonals($board){
            if(abs($board[0][0] + $board[1][1] + $board[2][2]) == 3){
                return board[0][0];
            }else if(abs($board[0][2] + $board[1][1] + $board[2][0]) == 3){
                return $board[0][2];
            }
            
            return 0;
        }

        $winningToken = 0;

        $winningToken = checkRows($board);
        if ($winningToken != 0){
          return $winningToken;
        }
  
        $winningToken = checkCols($board);
        if ($winningToken != 0){
          return $winningToken;
        }
  
        $winningToken = checkDiagonals($board);
        if ($winningToken != 0){
          return $winningToken;
        }

        return 0;
    }

    public function setName(Request $request, $id){
        if($request->session()->has('board.' . $id . '.token')){
            $token = $request->session()->get('board.' . $id . '.token');
        }else{
            abort(401);
        }

        $board = Board::where('code', strval($id))->first();

        if($token == -1){
            $board->x_name = $request->get('name');
        }else if ($token == 1){
            $board->o_name = $request->get('name');
        }else{
            abort(500); //something went wrong
        }

        $board->save();

        return $board;
    }

    public function endGame(Request $request, $id){
        $board = Board::where('code', strval($id))->first();
        $sentBoard = $request->get('board');
        $lastMove = $request->get('last_move');
        
        // This avoids a race condition where the database isn't updated when this request comes in, and so the boards don't match.
        // There is a better solution. If I were to re-do this, this process would happen when the board is updated in the controller
        // Instead of being called separately from the client. That would avoid the race condition. The function would also have to be
        // rewritten a bit to make that work, but it would be worth the time spent refactoring, imo.
        $updated_board = $request->get('board');
        $original_board = $board->board;
        $board_update = $request->get('last_move');
        $original_board[$board_update['row']][$board_update['col']] = $updated_board[$board_update['row']][$board_update['col']];

        if ($updated_board != $original_board) {

        }else{
            $board->board = $updated_board;
        }

        $winnerToken = $this->checkWin($board->board);
        if($winnerToken == -1){
            $winner = $board->x_name;
            $loser = $board->o_name;
        }else if($winnerToken == 1){
            $winner = $board->o_name;
            $loser = $board->x_name;
        }else{
            // something bad has happened
        }

        $winningLeader = new Leaderboard();
        $winningLeader->name = $winner;
        $winningLeader->code = $board->code;
        $winningLeader->win = true;
        $winningLeader->save();

        $losingLeader = new Leaderboard();
        $losingLeader->name = $loser;
        $losingLeader->code = $board->code;
        $losingLeader->win = false;
        $losingLeader->save();

        $board->winner = $winnerToken;

        $board->save();

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
