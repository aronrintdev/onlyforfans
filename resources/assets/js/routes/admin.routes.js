import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'

export const adminRoutes = [
  /*
  {
    name: 'admin', // %TODO: should be named 'home' or 'feed.home'
    path: '/admin',
    component: admin.Home,
    props: true,
  },
  */

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
