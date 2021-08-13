/**
 * resources/assets/js/components/common/navbar/MenuItems.js
 */

export const menuItems = {
  home: {
    key: 'home',
    icon: 'home',
    linkTo: { name: 'index', params: {} },
  },

  explore: {
    key: 'explore',
    icon: 'compass',
    linkTo: { name: 'timelines.explore', params: {} },
  },

  notifications: {
    key: 'notifications',
    icon: 'bell',
    linkTo: { name: 'notifications.dashboard', params: {} },
  },

  messages: {
    key: 'notification',
    icon: 'envelope',
    linkTo: { name: 'chatthreads.dashboard', params: {} }
  },

  profile: slug => ({
    key: 'profile',
    label: 'My Profile',
    icon: 'user',
    linkTo: { name: 'timeline.show', params: { slug } },
  }),

  vault: {
    key: 'vault',
    label: 'My Vault',
    icon: 'user',
    linkTo: { name: 'vault.dashboard', params: { } },
  },

  settings: {
    key: 'settings',
    label: 'Settings',
    icon: 'cog',
    linkTo: { name: 'settings.default' },
  },

  fans: {
    key: 'fans',
    label: 'Fans',
    icon: 'users',
    linkTo: { name: 'lists.followers' },
  },

  statements: {
    key: 'statements',
    label: 'statements',
    icon: 'receipt',
    linkTo: { name: 'statements.dashboard' },
  },

  banking: {
    key: 'banking',
    label: 'Banking',
    icon: 'university',
    linkTo: { name: 'settings.banking' },
  },

  earnings: {
    key: 'earnings',
    label: 'Earnings',
    icon: 'dollar-sign',
    linkTo: { name: 'settings.earnings' },
  },

  paymentMethod: {
    key: 'paymentMethod',
    label: 'Payment Method',
    icon: 'credit-card',
    linkTo: { name: 'settings.payments' },
  },

  logout: action => ({
    key: 'logout',
    label: 'Logout',
    icon: 'sign-out-alt',
    action,
  }),
}

export default menuItems
