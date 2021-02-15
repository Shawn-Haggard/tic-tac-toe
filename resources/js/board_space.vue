<template>
    <div class="box" v-on:click="play();"><span class="board-space" v-html="character"></span></div>
</template>

<script>
export default {
  data() {
    return {

    };
  },
  created: function(){

  },
  methods: {
    play: function(){
      // alert(this.value);

      if(this.active && this.value === 0){
        // this.value = this.token;
        this.$emit('played', {
          row: this.row,
          col: this.col,
        });
      }else if(this.value === -1 || this.value === 1){ // the player tried to change a box that was already set
        this.$emit('invalid-position', {
          value: this.value, 
          row: this.row,
          col: this.col,
        });
      }
      // alert(this.value);
    }
  },
  props: ['value', 'active', 'token', 'row', 'col'],
  computed: {
    character: function(){
      return this.value == -1 ? 'X' : this.value == 1 ? 'O' : '&nbsp;';
    }
  },
  watch: {
    value: function(){
      console.log('value updated');
    }
  }
};
</script>

<style>

#board .box {
  background-color: white;
  border: 1px solid black;
  text-align: center;
  flex: 1;
  font-size: 10vh;
  padding: 1rem;
}

</style>