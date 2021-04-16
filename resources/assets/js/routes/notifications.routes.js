/**
 * js/routes/notifications.routes.js
 */

import notifications from '@components/notifications'

export default [
  {
    name: 'notifications.likes',
    component: notifications.Likes,
    path: 'likes',
  },
  {
    name: 'notifications.subscribe',
    component: notifications.Subscribe,
    path: 'subscribe',
  },
  {
    name: 'notifications.tips',
    component: notifications.Tips,
    path: 'tips',
  },
  {
    name: 'notifications.purchases',
    component: notifications.Purchases,
    path: 'purchases',
  },
  {
    name: 'notifications.default',
    component: notifications.Likes,
    path: '',
  },
]
