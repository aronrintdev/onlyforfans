/**
 * App.js routes
 */
import VueRouter from 'vue-router'
import ErrorViews from '../views/errors'
import StaticPageRoutes from './staticPages.routes'

import Home from '../views/Home.vue'
import Timelines from '../views/Timelines.vue'

import HomeFeed from '../pages/HomeFeed.vue'

export const routes = [
  {
    name: 'index',
    path: '/',
    component: HomeFeed,
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


  ...StaticPageRoutes,

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

const router = new VueRouter({
  mode: 'history',
  routes: routes,
})

export default router
