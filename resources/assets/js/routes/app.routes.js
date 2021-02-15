/**
 * App.js routes
 */
import VueRouter from 'vue-router'

import Home from '../views/Home.vue'
import Timelines from '../views/Timelines.vue'

const router = new VueRouter({
  routes: [
    {
      name: 'home',
      path: '/home',
      component: Home,
    },
    {
      name: 'timelines',
      path: '/timelines',
      component: Timelines,
    }
  ]
})
