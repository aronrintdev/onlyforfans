import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'

import admin from '@views/admin'

export const adminRoutes = [
  {
    name: 'home', // %TODO: should be named 'home' or 'feed.home'
    path: '/admin',
    component: admin.Home,
    //props: true,
  },
  {
    name: 'user-management', // %TODO: should be named 'home' or 'feed.home'
    path: '/admin/user-management',
    component: admin.UserManagement,
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

const adminRouter = new VueRouter({
  mode: 'history',
  routes: adminRoutes,
})

export default adminRouter
