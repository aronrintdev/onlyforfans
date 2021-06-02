/**
 * App.js routes
 */
import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'
import StaticPageRoutes from './staticPages.routes'

import banking from '@views/banking'
import settings from '@views/settings'
import LivechatDashboard from '@views/live-chat/Dashboard'
import lists from '@views/lists'
import notifications from '@views/notifications'
import stories from '@views/stories'
import statements from '@views/statements'
import timelines from '@views/timelines'
import users from '@views/users'
import vaults from '@views/vaults'
import search from '@views/search'
import posts from '@views/posts'
import payments from '@views/payments'
//import livechat from '@views/live-chat/components'

import settingsRoutes from './settings.routes'
import livechatRoutes from './livechat.routes'
import listRoutes from './list.routes'
//import notificationsRoutes from './notifications.routes'

export const routes = [
  {
    name: 'index',
    path: '/',
    component: timelines.Home,
  },
  {
    name: 'search.home',
    path: '/search',
    component: search.Home,
    props: true,
  },

  // Live Chat
  {
    component: LivechatDashboard,
    //name: 'livechat.dashboard', // was 'home'
    //path: '/messages',
    path: '/messages',
    children: livechatRoutes,
  },

  // Timelines
  {
    name: 'timelines.home',
    path: '/timelines/home',
    component: timelines.Home,
  },
  // {
  //   name: 'timelines.show',
  //   path: '/timelines/:timelineId',
  //   component: timelines.Show,
  //   props: true,
  // },

  // Notifications
  {
    name: 'notifications.dashboard',
    path: '/notifications/dashboard',
    component: notifications.Dashboard,
    props: true,
  },

  // Settings
  {
    path: '/settings',
    component: settings.Dashboard,
    children:  settingsRoutes,
  },

  // Statements
  {
    name: 'statements.dashboard',
    path: '/statements',
    component: statements.Dashboard,
  },

  {
    name: 'banking.accounts.new',
    path: '/banking/accounts/new',
    component: banking.New,
  },

  // Lists
  {
    name: 'lists.dashboard',
    path: '/lists/dashboard',
    component: lists.Dashboard,
    children:  listRoutes,
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
    component: vaults.Dashboard,
  },

  // Static Pages
  ...StaticPageRoutes,

  // Timeline
  {
    name: 'timeline.posts',
    path: '/:slug/posts',
    props: true,
  },
  {
    name: 'timeline.followers',
    path: '/:slug/followers',
    props: true,
  },
  {
    name: 'timeline.following',
    path: '/:slug/following',
    props: true,
  },
  {
    name: 'timeline.earnings',
    path: '/:slug/earnings',
    props: true,
  },

  // Payments
  {
    name: 'payments.makePayment',
    path: '/payment-home',
    component: payments.MakePayment,
  },

  // Posts
  {
    name: 'posts.show',
    path: '/posts/:slug',
    component: posts.Show,
    props: true,
  },
  {
    name: 'posts.edit',
    path: '/posts/:slug/edit',
    component: posts.Edit,
    props: true,
  },


  /**
   * timeline.show must be declared after other routes due to it only being a slug
   */
  {
    name: 'timeline.show',
    path: '/:slug',
    component: timelines.Show,
    props: true,
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
