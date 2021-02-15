<template>

  <div id="board">
<!--  {{ csrf }}
  <br />
  {{ token }} : {{ turn }} : {{ winner }} : {{ mode }} : {{ code }} : {{ codeValid ? "valid" : "invalid" }}
  <br />
  {{ boards }} !-->

    <p>
    <h1>Welcome to Tic-Tac-Toe</h1>
    </p>
    <div v-if="winner !== 0"><h1>{{ tokenToChar(this.winner) }} wins!</h1></div>
    <label for="modes">Game Mode</label>    
    <select v-model="mode" id="modes">
      <option v-for="game_mode in modes" :value="game_mode">
          {{ game_mode }}
      </option>
    </select>    
    <div v-if="code && mode === 'vs'">
    Have others use this code to join this game: <strong>{{ code }}</strong>
    </div>
    <div v-if="!code && mode === 'vs'">
    <label for="existing_boards">Pick an existing game</label>    
    <select v-model="code" id="existing_boards">
      <option v-for="(board, index) in boards" :value="index">
          {{ index }} ({{ tokenToChar(board.token) }})
      </option>
    </select>    

    <input v-model='code' /> <button v-on:click="join();">Join</button> or <button v-on:click="newGame();">New Game</button>
    {{ code }}
    </div>
    <template v-else v-for="row in 3">
      <div class="board-row">
        <template v-for="col in 3">
          <board-space @played="played" @invalid-position="invalidPosition" v-bind:active="turn === token" v-bind:token="token" v-bind:value="board[row-1][col-1]" v-bind:row="row-1" v-bind:col="col-1"></board-space>
        </template>
      </div>
    </template>
  </div>
</template>

<script>

export default {
  data() {
    return {
      // tmpCode: '',
      codeValid: false,
      code: '',
      turn: -1,
      board: [[0, 0, 0], 
              [0, 0, 0], 
              [0, 0, 0]],
      boards: [],
      token: -1,
      winner: 0,
      mode: 'vs',
      modes: ['hotseat', 'computer', 'vs', ],
      csrf: '',
      updateFrequency: 1000,
      pendingUpdate: false,
    };
  },
  created: function(){
    this.loadExistingBoards();
  },
  mounted: function(){
    this.csrf = document.querySelector('meta[name="csrf-token"]').content;
    console.log(document.cookie);
  },
  methods: {
    tokenToChar: function(token) {
      return token == -1 ? 'X' : token == 1 ? 'O' : '&nbsp;';
    },
    loadExistingBoards: function(){
      let data = fetch(`/board`).then(response => response.json())
      .then(result => {
        console.log('Success:', result);
        this.boards = result;
      })
      .catch(error => {
        this.codeValid = false;
        console.error('Error:', error);
      });      
    },

    newGame: _.debounce(function() {
      if (this.mode === 'hotseat'){

      }else if (this.mode === 'computer'){

      }else if (this.mode === 'vs'){
        let url = `/board`;

        fetch(url, {
          method: 'POST',
          // mode: 'cors',
          cache: 'no-cache',
          credentials: 'same-origin', // include, *same-origin, omit
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': this.csrf,
          },
          redirect: 'follow',
          referrerPolicy: 'no-referrer',
          body: JSON.stringify({})
        }).then(response => response.json())
        .then(result => {
          console.log('Success!:', result);
          this.code = result.code;
          this.loadBoard();
        }).catch(error => {
          console.error('Error:', error);
        });

      }
    }, 300),

    loadBoard: function(){
      if(!this.pendingUpdate){
        let data = fetch(`/board/${this.code}`).then(response => response.json())
        .then(result => {
          console.log('Success:', result);
          console.log('result.length', Object.keys(result).length);
          if(Object.keys(result).length > 0){
            this.codeValid = true;
            this.code = result.code;
            this.board = result.board;
            this.turn = result.turn;
            this.token = result.token;
          }else{
            this.codeValid = false;
          }
        })
        .catch(error => {
          this.codeValid = false;
          console.error('Error:', error);
        });
        this.checkForPeriodicUpdate(this.code);
      }else{
        console.log('waiting for pending update');
        setTimeout(() => { this.loadBoard(); }, 500);
      }
    },

    join: _.debounce(function() {
      this.loadBoard();
    }, 300),

    updateServer: function(data){
      this.pendingUpdate = true;
      let url = `/board/${this.code}`;

      fetch(url, {
        method: 'PUT',
        // mode: 'cors',
        cache: 'no-cache',
        credentials: 'same-origin', // include, *same-origin, omit
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': this.csrf,
        },
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        body: JSON.stringify({board: this.board, data: data})
      }).then(response => response.json())
      .then(result => {
        // console.log('Success!:', result);
        this.pendingUpdate = false;
      }).catch(error => {
        // console.error('Error:', error);
      });

    },

    played: function(data){
      this.$set(this.board[data.row], data.col, this.token);
      this.findWinner();
      if(this.mode === 'hotseat'){
        this.token *= -1;
        this.turn *= -1;
      }else if(this.mode === 'computer'){
        setTimeout(() => { this.computerPlay(); }, (Math.random() * 1000)); // simulate "thinking"
      }else if(this.mode === 'vs'){
        this.turn = this.token * -1;
        this.updateServer(data);
      }

      if(this.winner !== 0){
        this.endGame();
      }
    },

    checkForPeriodicUpdate: function(code){
        setTimeout(()=> { 
          if(this.winner === 0 && code === this.code){
              this.loadBoard();
              this.checkForPeriodicUpdate();
            }else{
              console.log(this.mode, this.turn, this.token, this.winner);
            }          
          }, this.updateFrequency);
    },

    invalidPosition: function(data){
      console.log(data);
    },

    countEmptySpots: function(){
      let emptySpots = 0;
      for(let row = 0; row < 3; row ++){
        for(let col = 0; col < 3; col ++){
          if(this.board[row][col] === 0){
            emptySpots ++;
          }
        }
      }
      return emptySpots;
    },

    vsPlay: function(){

    },

    computerPlay: function(){
      let computerToken = this.token * -1;
      
      let spots = Math.floor(Math.random() * this.countEmptySpots());

      let done = false;

      if(this.winner === 0){
        for(let row = 0; row < 3 && !done; row ++){
          for(let col = 0; col < 3 && !done; col ++){
            if(this.board[row][col] === 0){
              spots --;
              if(spots < 1){
                console.log([row,col]);
                this.$set(this.board[row], col, computerToken);
                done = true;
                break;
              }
            }
          }
        }
      }

    },

    checkRows: function(){
      for(let i = 0; i < 3; i++){
        if(Math.abs(this.board[i][0] + this.board[i][1] + this.board[i][2]) == 3){
          return this.board[i][0];
        }
      }

      return 0; // no winner
    },

    checkCols: function(){
      for(let i = 0; i < 3; i++){
        if(Math.abs(this.board[0][i] + this.board[1][i] + this.board[2][i]) == 3){
          return this.board[0][i];
        }
      }

      return 0; // no winner
    },

    checkDiagonals: function(){
      console.log('asd');
      console.log(this.board[0][0]);
        if(Math.abs(this.board[0][0] + this.board[1][1] + this.board[2][2]) == 3){
          return this.board[0][0];
        }else if(Math.abs(this.board[0][2] + this.board[1][1] + this.board[2][0]) == 3){
          return this.board[0][2];
        }

        return 0; // no winner
    },

    // This function should have a server-side counterpart. But I ran out of time.

    findWinner: function(){ 
      let winningToken = 0;

      winningToken = this.checkRows();
      if (winningToken != 0){
        this.winner = winningToken;
      }

      winningToken = this.checkCols();
      if (winningToken != 0){
        this.winner = winningToken;
      }

      winningToken = this.checkDiagonals();
      if (winningToken != 0){
        this.winner = winningToken;
      }

    },
    endGame: function(){
      //TODO: finish game
    }
  },
  watch: {
    winner: function(val){
      // console.log(val);
      // console.log('WINNER!!!!!!!!');
      this.turn = 0;
    },
    code: function(){
      this.loadBoard();
    }
  }
};
</script>

<style>
#board {
  display: flex;
  flex-direction: column;
  width: 75vw;
  font-size: 18px;
  font-family: 'Roboto', sans-serif;
}

.board-row {
  flex: 1;
  display: flex;
  flex-direction: row;
  width: 100%;
}
</style>