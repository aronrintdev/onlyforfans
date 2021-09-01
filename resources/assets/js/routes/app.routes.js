/**
 * App.js routes
 */
import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'
import StaticPageRoutes from './staticPages.routes'

import banking from '@views/banking'
import settings from '@views/settings'
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

import LivechatDashboard from '@views/live-chat/Dashboard'
import LivechatCreateThread from '@views/live-chat/CreateThread'
import LivechatStats from '@views/live-chat/mass-stats/StatsDashboard'
import livechatRoutes from './livechat.routes'

import settingsRoutes from './settings.routes'
import listRoutes from './list.routes'
//import notificationsRoutes from './notifications.routes'
import statementsRoutes from './statements.routes'

export const routes = [
  {
    name: 'index', // %TODO: should be named 'home' or 'feed.home'
    path: '/',
    component: timelines.Home,
    props: true,
  },
  {
    name: 'timelines.explore',
    path: '/explore',
    component: timelines.Explore,
    props: true,
  },
  {
    name: 'search.home',
    path: '/search',
    component: search.Home,
    props: true,
  },

  // Live Chat
  {
    component: LivechatStats,
    name: 'chatthreads.stats',
    path: '/messages/stats',
  },
  {
    component: LivechatCreateThread,
    name: 'chatthreads.create',
    path: '/messages/new',
  },
  {
    component: LivechatDashboard,
    name: 'chatthreads.dashboard',
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
    children: statementsRoutes,
  },

  {
    name: 'banking.accounts.new',
    path: '/banking/accounts/new',
    component: banking.New,
  },

  // Lists
  {
    name: 'lists.dashboard',
    path: '/fans',
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
    path: '/my-media',
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
    path: '/posts/:slug/details',
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
   * Invitation Accept Page
   */
  {
    name: 'users.acceptstaffinvite',
    path: '/staff/invitations/accept',
    component: users.AcceptInviteSessionUser,
    props: false,
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
  scrollBehavior (to, from, savedPosition) {
    if (to.hash) {
      return {
        selector: to.hash,
        offset: { x: 0, y: 10 }
      }
    }
    return { x: 0, y: 0 }
  }
})

export default router
