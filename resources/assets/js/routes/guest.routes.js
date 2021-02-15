/**
 * Guest Routes
 */
import VueRouter from 'vue-router'
import AuthViews from '../views/auth'

export const router = new VueRouter({
  mode: 'history',
  routes: [
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
    }
  ]
})

export default router
