<template>
  <b-nav-item-dropdown
    v-if="session_user"
    id="profile-dropdown"
    toggle-class="nav-link-custom"
    right
    class="py-0"
  >
  <template #button-content>
    <b-avatar v-if="session_user.avatar.filepath" :src="session_user.avatar.filepath" class="mr-2" size="2rem" />
    <b-avatar v-else class="mr-2" size="2rem" />
    <span v-text="session_user.name" />
  </template>
    <b-dropdown-item
      v-for="(item, i) in menuItems"
      :key="i"
      :to="item.linkTo || null"
      @click="(typeof item.action === 'function') ? item.action() : null"
    >
      <fa-icon :icon="item.icon" fixed-width class="mr-3" /> {{ item.label }}
    </b-dropdown-item>
  </b-nav-item-dropdown>
</template>

<script>
/**
 * components/common/navbar/ProfileMenu.vue
 */
import Vuex from 'vuex'
export default {
  name: 'ProfileMenu',

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
          linkTo: { name: 'index' } // TODO: Add route when settings page is added
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
