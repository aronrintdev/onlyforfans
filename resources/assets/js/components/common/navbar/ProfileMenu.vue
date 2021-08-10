<template>
  <div>

    <component
      v-if="uiFlags.isAdmin"
      :is="dropdown ? 'b-dropdown-item' : 'b-nav-item'"
      href="/n0g1cg9sbx"
    >
      <fa-icon icon="user-shield" fixed-width class="mr-3" />
      <span v-text="$t('Admin Dashboard')" />
    </component>

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
    ...Vuex.mapGetters([
      'session_user', 
      'timeline',
      'uiFlags',
    ]),
    menuItems() {
      var items = []

      items = [ ...items,
        {
          label: 'My Profile',
          icon: 'user',
          linkTo: { name: 'timeline.show', params: { slug: this.timeline.slug } }
        },
        {
          label: 'My Vault',
          icon: 'user',
          linkTo: { name: 'vault.dashboard', params: { } },
        },
        {
          label: 'Settings',
          icon: 'cog',
          linkTo: { name: 'settings.profile' }
        },
        {
          label: 'Fans',
          icon: 'users',
          linkTo: { name: 'lists.followers' }
        },
      ]



      items.push({
        label: 'statements',
        icon: 'receipt',
        linkTo: { name: 'statements.dashboard' },
      })

      // if (this.uiFlags.isCreator && !this.uiFlags.hasBanking) {
        items.push({
          label: 'Banking',
          icon: 'university',
          linkTo: { name: 'settings.banking' }
        })
      // }
      if (this.uiFlags.isCreator && this.uiFlags.hasEarnings) {
        items.push({
          label: 'Earnings',
          icon: 'dollar-sign',
          linkTo: { name: 'settings.earnings' }
        })
      }
      if (this.uiFlags.hasPaymentMethod === false) {
        items.push({
          label: 'Payment Method',
          icon: 'credit-card',
          linkTo: { name: 'settings.payments' }
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
      Echo.leave('user-status');
      window.setLastSeenOfUser(0);
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
    "My Vault": "My Vault",
    "Settings": "Settings",
    "statements": "Statements",
    "Payment Method": "Payment Method",
    "Lists": "Lists",
    "Logout": "Logout",
  }
}
</i18n>
