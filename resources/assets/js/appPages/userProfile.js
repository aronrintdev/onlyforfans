/**
 * Temp Vue container for user profile page
 */
require('../bootstrap');
import Vue from 'vue';

import VueI18n from 'vue-i18n';
Vue.use(VueI18n);

import ForceCompute from '../plugins/forceCompute';
Vue.use(ForceCompute);


//import BootstrapVue from 'bootstrap-vue' //Importing
// import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
// Vue.use(BootstrapVue) // Telling Vue to use this in whole application
// Vue.use(BootstrapVueIcons)

Vue.component('online-status', require('../components/user/OnlineStatus.vue').default);

/**
 * Loading localization translations
 */
import i18n from '../i18n';

const app = new Vue({
    i18n,
    el: '#user-cover',
});