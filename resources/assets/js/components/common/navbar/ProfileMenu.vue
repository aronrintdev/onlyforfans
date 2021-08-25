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
      :is="dropdown
        ? item.divider ? 'b-dropdown-divider' : 'b-dropdown-item'
        : 'b-nav-item'"
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
import MenuItems from './MenuItems'

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
        MenuItems.profile(this.timeline.slug),
        MenuItems.vault,
        MenuItems.fans,
      ]

      if ( true ) { // TODO: Verified
        items.push(MenuItems.statements)
      }

      if ( true ) { // TODO: Verified and doesn't have bank account
        items.push(MenuItems.banking)
      }

      // if (this.uiFlags.isCreator && this.uiFlags.hasEarnings) {
      //   items.push({
      //     label: 'Earnings',
      //     icon: 'dollar-sign',
      //     linkTo: { name: 'settings.earnings' }
      //   })
      // }
      if (this.uiFlags.hasPaymentMethod === false) {
        items.push(MenuItems.paymentMethod)
      }

      items.push({ key: 'divider-bottom', divider: true })
      items.push(MenuItems.settings)
      items.push(MenuItems.logout(this.logout))

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
    "Banking": "Banking",
    "Fans": "Fans",
    "Lists": "Lists",
    "Logout": "Logout",
    "My Profile": "My Profile",
    "My Media": "My Media",
    "Payment Method": "Payment Method",
    "Settings": "Settings",
    "statements": "Statements",
  }
}
</i18n>
