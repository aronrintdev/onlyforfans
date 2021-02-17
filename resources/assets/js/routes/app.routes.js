/**
 * App.js routes
 */
import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'
import StaticPageRoutes from './staticPages.routes'

import stories from '@views/stories'
import timelines from '@views/timelines'
import users from '@views/users'
import vaults from '@views/vaults'

export const routes = [
  {
    name: 'index',
    path: '/',
    component: timelines.Home,
  },

  // Timelines
  {
    name: 'timelines.home',
    path: '/timelines/home',
    component: timelines.Home,
  },
  {
    name: 'timelines.show',
    path: '/timelines/:timelineId',
    component: timelines.Show,
    props: true,
  },

  // Stories
  {
    name: 'stories.dashboard',
    path: '/stories/dashboard',
    component: stories.Dashboard,
  },
  {
    name: 'stories.player',
    path: '/stories/player',
    component: stories.Player,
  },

  // Vaults
  {
    name: 'vault.dashboard',
    path: '/my-vault',
    component: vaults.Dashboard
  },

  ...StaticPageRoutes,

  {
    name: 'user.page',
    path: '/:username',
    component: users.Home,
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

const router = new VueRouter({
  mode: 'history',
  routes: routes,
})

export default router
