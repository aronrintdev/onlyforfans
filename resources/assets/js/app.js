/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

//require('./bootstrap');
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Vue = require('vue');


import BootstrapVue from 'bootstrap-vue' //Importing
Vue.use(BootstrapVue) // Telling Vue to use this in whole application

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('create-story', require('./components/stories/Wizard.vue'));
//Vue.component('story-player', require('./components/stories/Player.vue'));
Vue.component('story-player', require('./components/stories/AutoPlayer.vue'));

export const eventBus = new Vue({
/*
    methods: {
        switchDetails: function(obj) {
            this.$emit('detailsWasSwitched', obj);
        },
        fixStatus: function(index) {
            this.$emit('statusWasFixed', index);
        },
    }
*/
});

const app = new Vue({
    el: '#app'
});

