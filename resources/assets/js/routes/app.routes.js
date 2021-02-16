/**
 * App.js routes
 */
import VueRouter from 'vue-router'
import ErrorViews from '../views/errors'

import Home from '../views/Home.vue'
import Timelines from '../views/Timelines.vue'

const router = new VueRouter({
  routes: [
    {
      name: 'index',
      path: '/',
      component: Home,
    },
    {
      name: 'home',
      path: '/home',
      component: Home,
    },
    {
      name: 'timelines',
      path: '/timelines',
      component: Timelines,
    },


    /**
     * 404, catch all unknowns
     * This should always be registered last
     */
    {
      name: 'error-not-found',
      path: '*',
      component: ErrorViews.NotFound,
    },
  ]
})
