/**
 * js/routes/settings.routes.js
 */

import settings from '@components/settings'

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
    name: 'settings.earnings',
    component: settings.Earnings,
    path: 'earnings',
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
