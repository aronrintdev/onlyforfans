import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'

import admin from '@views/admin'

export const adminRoutes = [
  {
    name: 'user-management',
    path: '/admin/user-management',
    component: admin.UserManagement,
  },
  {
    name: 'account-management',
    path: '/admin/account-management',
    component: admin.AccountManagement,
  },
  {
    name: 'txn-management',
    path: '/admin/txn-management',
    component: admin.TxnManagement,
  },
  {
    name: 'home',
    path: '/admin',
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
