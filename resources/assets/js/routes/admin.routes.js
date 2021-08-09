import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'

import admin from '@views/admin'

export const adminRoutes = [
  {
    name: 'user-management',
    path: '/n0g1cg9sbx/user-management',
    component: admin.UserManagement,
  },
  {
    name: 'financial-management',
    path: '/n0g1cg9sbx/financial-management',
    component: admin.FinancialManagement,
  },
  {
    name: 'feed-management',
    path: '/n0g1cg9sbx/feed-management',
    component: admin.FeedManagement,
  },
  {
    name: 'content-management',
    path: '/n0g1cg9sbx/content-management',
    component: admin.ContentManagement,
  },
  {
    name: 'media-management',
    path: '/n0g1cg9sbx/media-management',
    component: admin.MediaManagement,
  },
  {
    name: 'beta-program',
    path: '/n0g1cg9sbx/beta-program',
    component: admin.BetaProgram,
  },
  {
    name: 'home',
    path: '/n0g1cg9sbx',
    component: admin.Home,
    //props: true,
  },
  { // catch all unknowns (404), always register last
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
