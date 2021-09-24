const isProduction = process.env.NODE_ENV === 'production';

import store from './store';

import Vue from 'vue';

// Bootstrapping
require('./bootstrap');
require('./bootstrap/fontAwesome');
require('./bootstrap/copy');

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// https://github.com/imcvampire/vue-axios#readme
// Use `this.axios`, `Vue.axios`, or `this.$htttp` to access axios.
import VueAxios from 'vue-axios';
Vue.use(VueAxios, window.axios);

// Use: `this.$log.error(error)`
// logLevels : ['debug', 'info', 'warn', 'error', 'fatal']
import VueLogger from 'vuejs-logger';
const options = {
    isEnabled: true,
    logLevel : isProduction ? 'error' : 'debug',
    stringifyArguments : false,
    showLogLevel : true,
    showMethodName : !isProduction,
    separator: '|',
    showConsoleColors: true
};
Vue.use(VueLogger, options);

require('./bootstrap/filters')

import VueI18n from 'vue-i18n';
Vue.use(VueI18n);

import IsEmpty from './plugins/isEmpty';
Vue.use(IsEmpty);

import ForceCompute from './plugins/forceCompute';
Vue.use(ForceCompute);

import WhenAvailable from './plugins/whenAvailable';
Vue.use(WhenAvailable);

//import BootstrapVue from 'bootstrap-vue' //Importing
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
Vue.use(BootstrapVue) // Telling Vue to use this in whole application
Vue.use(BootstrapVueIcons)

//export const eventBus = new Vue({ });

import i18n from './i18n'

// Admin Components
//require('./components/admin');
// Vue.component('main-navbar', require('./components/common/MainNavbar.vue').default);

import VueRouter from 'vue-router';
Vue.use(VueRouter);
import router from './routes/admin.routes';

import AdminApp from './views/templates/Admin.vue';

const app = new Vue({
  router,
  i18n,
  store,
  render: h => h(AdminApp),
}).$mount('#app');
