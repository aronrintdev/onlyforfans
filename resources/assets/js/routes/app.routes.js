/**
 * App.js routes
 */
import VueRouter from 'vue-router'
import ErrorViews from '@views/errors'
import StaticPageRoutes from './staticPages.routes'

import settings from '@views/settings'
import stories from '@views/stories'
import timelines from '@views/timelines'
import users from '@views/users'
import vaults from '@views/vaults'
import search from '@views/search'
import posts from '@views/posts'

import SettingsGeneral from '@components/settings/SettingsGeneral.vue';
import SettingsProfile from '@components/settings/SettingsProfile.vue';
import SettingsPrivacy from '@components/settings/SettingsPrivacy.vue';
import SettingsSecurity from '@components/settings/SettingsSecurity.vue';
import SettingsEarnings from '@components/settings/SettingsEarnings.vue';
import SettingsSessions from '@components/settings/SettingsSessions.vue';
import SettingsReferrals from '@components/settings/SettingsReferrals.vue';

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

  // Settings
  {
    path: '/settings',
    component: settings.Dashboard,
    children: [
      {
        name: 'settings.general',
        component: SettingsGeneral,
        path: 'general',
      },
      {
        name: 'settings.profile',
        component: SettingsProfile,
        path: 'profile',
      },
      {
        name: 'settings.privacy',
        component: SettingsPrivacy,
        path: 'privacy',
      },
      {
        name: 'settings.security',
        component: SettingsSecurity,
        path: 'security',
      },
      {
        name: 'settings.earnings',
        component: SettingsEarnings,
        path: 'earnings',
      },
      {
        name: 'settings.sessions',
        component: SettingsSessions,
        path: 'sessions',
      },
      {
        name: 'settings.referrals',
        component: SettingsReferrals,
        path: 'referrals',
      },
      {
        name: 'settings.default',
        component: SettingsGeneral,
        path: '',
      },
    ],
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

  // User
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
  {
    name: 'timeline.show',
    path: '/:slug',
    component: timelines.Show,
    props: true,
  },

  // Posts
  {
    name: 'post.show',
    path: '/post/:slug',
    component: posts.Show,
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