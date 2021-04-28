/**
 * js/routes/settings/subscriptions.routes.js
 */

import Subscriptions from '@components/subscriptions'

export const subscriptions = [
  {
    name: 'subscriptions.details',
    component: Subscriptions.Details,
    path: ':id',
    props: true,
  },
  {
    name: 'settings.my-subscriptions',
    component: Subscriptions.List,
    path: '',
    props: route => ({ tab: parseInt(route.query.t) || 0 }),
  },
]

export default subscriptions
