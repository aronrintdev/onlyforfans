<template>
  <div>
    <component
      :is="dropdown ? 'b-dropdown-item' : 'b-nav-item'"
      v-for="(item, i) in menuItems"
      :key="i"
      :to="item.linkTo || null"
      @click="(typeof item.action === 'function') ? item.action() : null"
    >
      <fa-icon :icon="item.icon" fixed-width class="mr-3" />
      <span v-text="$t(item.label)" />
    </component>
  </div>
</template>

<script>
/**
 * components/common/navbar/ProfileMenu.vue
 */
import Vuex from 'vuex'
import ProfileButton from './ProfileButton'

export default {
  name: 'ProfileMenu',

  props: {
    dropdown: { type: Boolean, default: false },
  },

  data: () => ({
    loading: true,
  }),

  computed: {
    ...Vuex.mapGetters(['session_user', 'uiFlags']),
    menuItems() {
      var items = []

      if (this.uiFlags.isAdmin) {
        items.push({
          label: 'Admin Dashboard',
          icon: 'user-shield',
          linkTo: { name: 'index' }, // TODO: Add link to admin dashboard
        })
      }

      items = [ ...items,
        {
          label: 'My Profile',
          icon: 'user',
          linkTo: { name: 'timeline.show', params: { slug: this.session_user.timeline.slug } }
        },
        {
          label: 'Settings',
          icon: 'cog',
          linkTo: { name: 'settings.general' }
        },
      ]

      if (this.uiFlags.isCreator && !this.uiFlags.hasBanking) {
        items.push({
          label: 'Banking',
          icon: 'university',
          linkTo: { name: 'index' } // TODO: Add route when settings page is added
        })
      }
      if (this.uiFlags.isCreator && this.uiFlags.hasEarnings) {
        items.push({
          label: 'Earnings',
          icon: 'dollar-sign',
          linkTo: { name: 'index' } // TODO: Add route when settings page is added
        })
      }
      if (this.uiFlags.hasPaymentMethod === false) {
        items.push({
          label: 'Payment Method',
          icon: 'credit-card',
          linkTo: { name: 'index' } // TODO: Add route when settings page is added
        })
      }

      items = [ ...items, {
        label: 'Logout',
        icon: 'sign-out-alt',
        action: this.logout,
      }]

      return items
    }
  },

  methods: {
    logout() {
      this.axios.post('/logout').then(() => {
        window.location = '/login'
      })
    },
  },
}
</script>

<i18n lang="json5">
{
  "en": {
    "Admin Dashboard": "Admin Dashboard",
    "My Profile": "My Profile",
    "Settings": "Settings",
    "Payment Method": "Payment Method",
    "Logout": "Logout",
  }
}
</i18n>
