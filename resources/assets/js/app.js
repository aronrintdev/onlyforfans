/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import store from './store';

require('./bootstrap');
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//axios.defaults.baseURL = '/';

window.Vue = require('vue');


//import BootstrapVue from 'bootstrap-vue' //Importing
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
Vue.use(BootstrapVue) // Telling Vue to use this in whole application
Vue.use(BootstrapVueIcons)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('create-story', require('./components/stories/Wizard.vue'));
Vue.component('story-player', require('./components/stories/AutoPlayer.vue'));
Vue.component('my-vault', require('./components/vault/Dashboard.vue'));
Vue.component('my-saved', require('./components/saved/Dashboard.vue'));

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
    store,
    el: '#app',
});

/*
const app = new Vue({
    //router,
    store,
    render: h => h(App),
}).$mount('#app');
*/
