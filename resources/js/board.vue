<template>

  <div id="board">
  <!--  
  {{ csrf }}
  <br />
  {{ token }} : {{ turn }} : {{ winner }} : {{ mode }} : {{ code }}
  <br />
  {{ boards }} 
  !-->

    <p>
    <h1>Welcome to Tic-Tac-Toe</h1>
    </p>
    <div v-if="!name">
      <label for="name">Enter your name</label>
      <input id="name" v-model="tmpName" /> <button v-on:click="setName();">Save</button>
    </div>
    <div v-else>
      <div v-if="winner !== 0"><h1>{{ winnerName() }} ({{ tokenToChar(this.winner) }}) wins!</h1></div>
      <div>
        Playing as: {{ name }} <span v-if="opponent_name !== ''">Playing against: {{ opponent_name }}</span>
      </div>
      <div v-if="winner === 0">
        It is {{ playersTurn() }} turn
      </div>
      <label for="modes">Game Mode</label>    
      <select v-model="mode" id="modes">
        <option v-for="game_mode in modes" :value="game_mode">
            {{ game_mode }}
        </option>
      </select>    
      <div v-if="code && mode === 'vs'">
      Playing game: <strong>{{ code }}</strong>
      </div>
      <div v-if="mode == 'vs'">
        <label for="existing_boards">Pick an existing game</label>    
        <select v-model="code" id="existing_boards">
          <option v-for="(board, index) in boards" :value="index">
              {{ index }} ({{ tokenToChar(board.token) }})
          </option>
        </select>
      </div>
      <span v-if="mode == 'vs'"><input v-model='code' /> <button v-on:click="join();">Join</button> or </span><button v-on:click="newGame();">New Game</button>
      <template  v-for="row in 3">
        <div class="board-row">
          <template v-for="col in 3">
            <board-space @played="played" @invalid-position="invalidPosition" v-bind:active="turn === token" v-bind:token="token" v-bind:value="board[row-1][col-1]" v-bind:row="row-1" v-bind:col="col-1"></board-space>
          </template>
        </div>
      </template>
    </div>
  </div>
</template>

<script>

export default {
  data() {
    return {
      code: '',
      turn: -1,
      tmpName: '',
      name: '',
      opponent_name: '',
      board: [[0, 0, 0], 
              [0, 0, 0], 
              [0, 0, 0]],
      boards: [],
      token: -1,
      winner: 0,
      mode: 'computer',
      modes: ['computer', 'hotseat', 'vs', ],
      csrf: '',
      updateFrequency: 1000,
      pendingUpdate: false,
      lastMove: [],
    };
  },
  created: function(){
    this.loadExistingBoards();
  },
  mounted: function(){
    this.csrf = document.querySelector('meta[name="csrf-token"]').content;
    // console.log(document.cookie);
  },
  methods: {
    winnerName: function(){
      if (this.winner === this.token){
        return this.name;
      }else if (this.winner === this.token * -1){
        return this.opponent_name;
      }
      return '';
    },

    tokenToChar: function(token) {
      return token == -1 ? 'X' : token == 1 ? 'O' : '&nbsp;';
    },

    loadExistingBoards: function(){
      let data = fetch(`/board`).then(response => response.json())
      .then(result => {
        // console.log('Success:', result);
        this.boards = result;
      })
      .catch(error => {
        console.error('Error:', error);
      });      
    },

    resetData: function(){
      this.board = [[0, 0, 0], 
                    [0, 0, 0], 
                    [0, 0, 0]];
      this.opponent_name = '';
      this.winner = 0;
    },

    newGame: _.debounce(function() {
      this.resetData();
      if (this.mode === 'hotseat'){
        this.opponent_name = `${this.name}'s hotseat opponenet`;
      }else if (this.mode === 'computer'){
        this.opponent_name = 'Computer';
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
          body: JSON.stringify({name: this.name})
        }).then(response => response.json())
        .then(result => {
          // console.log('Success!:', result);
          this.code = result.code;
          this.loadBoard();
        }).catch(error => {
          console.error('Error:', error);
        });

      }
    }, 300),
    
    updateNameInGame: function(){
      // console.log('updateNameInGame');
        let url = `/board/${this.code}/name`;
        // console.log('endGame');
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
          body: JSON.stringify({'name': this.name})
        }).then(response => response.json())
        .then(result => {
          // console.log('Success!:', result);
        }).catch(error => {
          console.error('Error:', error);
        });

    },

    loadBoard: function(){
      // console.log('loadBoard');
      if(!this.pendingUpdate){
        let data = fetch(`/board/${this.code}`).then(response => response.json())
        .then(result => {
          // console.log('Success:', result);
          // console.log('result.length', Object.keys(result).length);
          if(Object.keys(result).length > 0){
            this.code = result.code;
            this.board = result.board;
            this.turn = result.turn;
            this.token = result.token;
            this.winner = result.winner;
            let myName = this.token === -1 ? result.x_name : result.o_name;
            if(myName !== this.name){
              this.updateNameInGame();
            }
            if(this.winner === 0){
              this.findWinner();
            }
            if(this.token === -1){
              this.opponent_name = result.o_name;
            }else if(this.token === 1){
              this.opponent_name = result.x_name;
            }
          }else{
            this.resetData();
          }
        })
        .catch(error => {
          this.resetData();
          console.error('Error:', error);
        });
        this.checkForPeriodicUpdate(this.code);
      }else{
        // console.log('waiting for pending update');
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
      this.lastMove = data;
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
        // console.log(this.board);
        this.endGame();
      }
    },

    checkForPeriodicUpdate: function(code){
        setTimeout(()=> { 
          if(this.winner === 0 && code === this.code){
              this.loadBoard();
              this.checkForPeriodicUpdate();
            }else{
              // console.log(this.mode, this.turn, this.token, this.winner);
            }          
          }, this.updateFrequency);
    },

    invalidPosition: function(data){
      // console.log(data);
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
                // console.log([row,col]);
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
      if(this.mode === 'vs'){
        let url = `/board/${this.code}/end`;
        // console.log('endGame');
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
          body: JSON.stringify({'board': this.board, 'last_move': this.lastMove, 'name': this.name, 'opponent_name': this.opponent_name, 'code': this.code})
        }).then(response => response.json())
        .then(result => {
          console.log('Success!:', result);
          this.code = result.code;

        }).catch(error => {
          console.error('Error:', error);
        });
      }
    },

    setName: function(){
      this.name = this.tmpName;
    },

    playersTurn: function(){
      if(this.turn === this.token){
        return "your";
      }else{
        return `${this.opponent_name}'s`;
      }
    },

  },
  watch: {
    winner: function(val){
      this.turn = 0;
    },
    // code: function(){
    //   this.loadBoard();
    // }
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