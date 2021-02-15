require('./bootstrap');

import _ from 'lodash';

import Vue from 'vue';

import Test from "./test.vue";
import Board from "./board.vue";
import BoardSpace from './board_space.vue';
Vue.component('board', Board);
Vue.component('board-space', BoardSpace);
Vue.component('test',Test);

var vm = new Vue({
    el: '#app',
    created: function(){

    }

});
