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
    ...Vuex.mapState(['session_user']),
    menuItems() {
      return [
        {
          label: 'My Profile',
          icon: 'user',
          action: this.action,
        },
        {
          label: 'Settings',
          icon: 'cog',
          action: this.action,
        },
        {
          label: 'Fans',
          icon: 'users',
          action: this.action,
        },
        {
          label: 'Saved & Purchased',
          icon: 'bookmark',
          action: this.action,
        },
        {
          label: 'Banking',
          icon: 'university',
          action: this.action,
        },
        {
          label: 'Earnings',
          icon: 'dollar-sign',
          action: this.action,
        },
        {
          label: 'Payment Method',
          icon: 'credit-card',
          action: this.action,
        },
        {
          label: 'Vault',
          icon: 'lock',
          linkTo: { name: 'vault.dashboard' },
        },
        {
          label: 'Referrals',
          icon: 'retweet',
          action: this.action,
        },
        {
          label: 'FAQ',
          icon: 'question',
          action: this.action,
        },
        {
          label: 'Contact Support',
          icon: 'envelope',
          action: this.action,
        },
        {
          label: 'Logout',
          icon: 'sign-out-alt',
          action: this.logout,
        },
      ]
    }
  },

  methods: {
    action() {
      //
    },
    logout() {
      this.axios.post('/logout').then(() => {
        window.location = '/login'
      })
    },
  },
}
</script>

<style lang="scss" scoped></style>
