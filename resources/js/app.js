require('./bootstrap');

import _ from 'lodash';

import Vue from 'vue';

import LeaderBoard from "./leaderboard.vue";
import Board from "./board.vue";
import BoardSpace from './board_space.vue';
Vue.component('board', Board);
Vue.component('board-space', BoardSpace);
Vue.component('leaderboard', LeaderBoard);

var vm = new Vue({
    el: '#app',
    created: function(){

    }

});
