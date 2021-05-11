/**
 * Guest Routes
 */
import VueRouter from 'vue-router'
import AuthViews from '@views/auth'
import ErrorViews from '@views/errors'
import StaticPageRoutes from './staticPages.routes'

export const routes = [
  {
    name: 'index',
    path: '/',
    redirect: to => {
      return '/login'
    },
  },
  {
    name: 'login',
    path: '/login',
    component: AuthViews.Login,
  },
  {
    name: 'register',
    path: '/register',
    component: AuthViews.Register,
  },
  {
    name: 'forgot-password',
    path: '/forgot-password',
    component: AuthViews.ForgotPassword,
  },
  {
    name: 'reset-password',
    path: '/reset-password/:token',
    component: AuthViews.ResetPassword,
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


export const router = new VueRouter({
  mode: 'history',
  routes: routes
})

export default router
