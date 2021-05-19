/**
 * js/routes/settings.routes.js
 */

import settings from '@components/settings'
import childRoutes from './settings'

export default [
  {
    name: 'settings.general',
    component: settings.General,
    path: 'general',
  },
  {
    name: 'settings.profile',
    component: settings.Profile,
    path: 'profile',
  },
  {
    name: 'settings.notifications',
    component: settings.Notifications,
    path: 'notifications',
  },
  {
    name: 'settings.privacy',
    component: settings.Privacy,
    path: 'privacy',
  },
  {
    name: 'settings.security',
    component: settings.Security,
    path: 'security',
  },
  {
    name: 'settings.payments',
    component: settings.Payments,
    path: 'payment-methods',
  },
  {
    component: settings.MySubscriptions,
    path: 'my-subscriptions',
    children: childRoutes.subscriptions,
  },
  {
    name: 'settings.payouts',
    component: settings.Payouts,
    path: 'payouts',
  },
  {
    name: 'settings.sessions',
    component: settings.Sessions,
    path: 'sessions',
  },
  {
    name: 'settings.referrals',
    component: settings.Referrals,
    path: 'referrals',
  },
  /*
  {
    name: 'settings.bookmarks',
    component: settings.Bookmarks,
    path: 'bookmarks',
  },
  */
  {
    name: 'settings.subscriptions',
    component: settings.Subscriptions,
    path: 'subscriptions',
  },
  {
    name: 'settings.default',
    component: settings.General,
    path: '',
  },
]
