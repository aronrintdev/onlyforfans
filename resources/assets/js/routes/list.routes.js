/**
 * js/routes/list.routes.js
 */

import lists from '@components/lists'

export default [
  {
    name: 'lists.favorites',
    component: lists.Favorites,
    path: 'favorites',
  },
  {
    name: 'lists.following',
    component: lists.Following,
    path: 'following',
  },
  {
    name: 'lists.followers',
    component: lists.Followers,
    path: 'followers',
  },
  {
    name: 'lists.default',
    component: lists.Favorites,
    path: '',
  },
]
