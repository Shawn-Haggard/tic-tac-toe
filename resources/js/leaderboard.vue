<template>
  <div id="leaderboard">
    <h3>Leaderboard</h3>
    <ul>
      <li>Name : Wins</li>
      <li v-for="winner in leaders">{{winner.name}} : {{winner.wins}}</li>
    </ul>
  </div>
</template>

<script>
export default {
  data() {
    return {
      leaders: [],
    };
  },
  created: function(){
    this.refreshLeaderboard();
  },
  methods: {
    refreshLeaderboard: function(){
      let data = fetch(`/leaderboard`).then(response => response.json())
      .then(result => {
        console.log('Success:', result);
        this.leaders = result;
      })
      .catch(error => {
        console.error('Error:', error);
      });
    }
  }
};
</script>

<style>
#leaderboard {
  float:right;
  display: block;
}
</style>