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

import VueI18n from 'vue-i18n';
Vue.use(VueI18n);

import ForceCompute from './plugins/forceCompute';
Vue.use(ForceCompute);

//import BootstrapVue from 'bootstrap-vue' //Importing
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
Vue.use(BootstrapVue) // Telling Vue to use this in whole application
Vue.use(BootstrapVueIcons)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('create-story', require('./components/stories/Wizard.vue').default);
Vue.component('story-player', require('./components/stories/AutoPlayer.vue').default);
Vue.component('my-vault', require('./components/vault/Dashboard.vue').default);
Vue.component('my-saved', require('./components/saved/Dashboard.vue').default);

Vue.component('online-status', require('./components/user/OnlineStatus.vue').default);

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

/**
 * Loading localization translations
 */
import i18n from './i18n'

const app = new Vue({
    i18n,
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
