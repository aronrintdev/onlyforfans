// js/routes/settings.routes.js

//import livechat from '@components/live-chat'
import livechat from '@views/live-chat/components'

export default [
  {
    component: livechat.ListScheduled,
    name: 'chatmessages.scheduled',
    path: 'scheduled',
  },
  {
    component: livechat.CreateThread,
    name: 'chatthreads.create',
    path: 'new',
  },
  {
    component: livechat.Gallery,
    name: 'chatthreads.gallery',
    path: ':id/gallery',
  },
  {
    component: livechat.ShowThread,
    name: 'chatthreads.show',
    path: ':id',
  },
  {
    component: livechat.CreateThread,
    name: 'livechat.default',
    path: '',
  },
]
