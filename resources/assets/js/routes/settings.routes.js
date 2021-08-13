// js/routes/settings.routes.js

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
    name: 'settings.subscription',
    component: settings.Subscription,
    path: 'subscription',
  },
  {
    component: settings.MySubscriptions,
    path: 'my-subscriptions',
    children: childRoutes.subscriptions,
  },
  {
    name: 'settings.banking',
    component: settings.Banking,
    path: 'banking',
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
  {
    name: 'settings.verify',
    component: settings.Verify,
    path: 'verify',
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
    name: 'settings.managers',
    component: settings.Managers,
    path: 'managers',
  },
  {
    name: 'settings.staffmembers',
    component: settings.StaffMembers,
    path: 'staff-members',
  },
  {
    name: 'settings.default',
    // component: settings.General,
    path: '',
  },
]
