/**
 * App.js routes
 */
import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'
import StaticPageRoutes from './staticPages.routes'

import settings from '@views/settings'
import lists from '@views/lists'
import notifications from '@views/notifications'
import stories from '@views/stories'
import timelines from '@views/timelines'
import users from '@views/users'
import vaults from '@views/vaults'
import search from '@views/search'
import posts from '@views/posts'
import payments from '@views/payments'
import liveChat from '@views/live-chat'

import settingsRoutes from './settings.routes'
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
  {
    name: 'messages.home',
    path: '/messages',
    component: liveChat.Home,
  },
  {
    name: 'messages.new',
    path: '/messages/new',
    component: liveChat.New,
  },
  {
    name: 'messages.details',
    path: '/messages/:id',
    component: liveChat.Details,
  },
  {
    name: 'messages.gallery',
    path: '/messages/:id/gallery',
    component: liveChat.Gallery,
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

  // Lists
  {
    path: '/lists',
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
