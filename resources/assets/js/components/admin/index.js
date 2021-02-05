/**
 * Admin Component Initialization
 */
import Vue from 'vue';

Vue.component('dashboard', require('./Dashboard.vue').default);

/* --------------------------------- Common --------------------------------- */
Vue.component('error-alert', require('./common/ErrorAlert.vue').default);
