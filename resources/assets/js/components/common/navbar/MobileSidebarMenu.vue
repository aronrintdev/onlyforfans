<template>
  <div class="wrapper">
    <div
      v-if="session_user"
      class="mobile-sidebar pb-1"
      :class="{ 'show': show, 'from-left': !fromRight, 'from-right': fromRight }"
    >
      <!-- Header -->
      <div class="header p-3 d-flex justify-content-between align-items-center border-bottom">
        <AvatarWithStatus :user="session_user" noLink size="sm" />
        <div class="close cursor-pointer ml-3" @click="onClose">
          <fa-icon icon="times" size="lg" fixed-width />
        </div>
      </div>

      <b-nav class="nav-list flex-grow-1">
        <!-- Top Section -->
        <div class="scroll">
          <b-nav-item
            v-for="item in topSection"
            :key="item.key"
            :to="item.linkTo || null"
            @click="(typeof item.action === 'function') ? item.action() : onClick"
            class="nav-item"
            :class="{ active: item.selected }"
            :active="item.selected"
          >
            <fa-icon
              :icon="item.selected ? [ 'fas', item.icon ] : [ 'far', item.icon ]"
              class="icon mr-2"
              fixed-width
            />
            <span class="label" v-text="$t(`label.${item.key}`)" />
          </b-nav-item>
        </div>

        <!-- Bottom Section -->
        <!-- Settings -->
        <li class="divider mt-auto border-top" />
        <b-nav-item
          v-for="item in bottomSection"
          :key="item.key"
          :to="item.linkTo || null"
          @click="(typeof item.action === 'function') ? item.action() : onClick"
          class="nav-item"
        >
          <fa-icon
            :icon="item.selected ? [ 'fas', item.icon ] : [ 'far', item.icon ]"
            class="icon mr-2"
            fixed-width
          />
          <span class="label" v-text="$t(`label.${item.key}`)" />
        </b-nav-item>

      </b-nav>

    </div>
    <transition name="fade">
      <div v-if="show" class="overlay" @click="onClose" />
    </transition>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/common/navbar/MobileSidebarMenu.vue
 */
import Vuex from 'vuex'

import AvatarWithStatus from '@components/user/AvatarWithStatus'
import MenuItems from './MenuItems'

export default {
  name: 'MobileSidebarMenu',

  components: {
    AvatarWithStatus,
  },

  model: { prop: 'show', event: 'change'},

  props: {
    show: { type: Boolean, default: false },
    fromRight: { type: Boolean, default: false },
  },

  computed: {
    ...Vuex.mapGetters([
      'session_user',
      'timeline',
      'uiFlags',
    ]),

    topSection() {
      var items = []

      items = [ ...items,
        MenuItems.home,
        MenuItems.profile(this.timeline.slug),
        MenuItems.vault,
        MenuItems.fans,
        MenuItems.explore,
        MenuItems.notifications,
        MenuItems.messages,
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

      for (var item of items) {
        if (item.linkTo && item.linkTo.name === this.$route.name) {
          item.selected = true
        } else {
          item.selected = false
        }
      }

      return items
    },

    bottomSection() {
      var items = [
        MenuItems.settings,
        MenuItems.logout(this.logout),
      ]
      for (var item of items) {
        if (item.linkTo && item.linkTo.name === this.$route.name) {
          item.selected = true
        } else {
          item.selected = false
        }
      }
      return items
    },
  },

  data: () => ({}),

  methods: {
    logout() {
      Echo.leave('user-status');
      window.setLastSeenOfUser(0);
      this.axios.post('/logout').then(() => {
        window.location = '/login'
      })
      this.onClose()
    },

    onClick() {
      this.onClose()
    },

    onClose() {
      this.$emit('change', false)
      this.$emit('close')
    },
  },

  watch: {
    $route() {
      this.onClose()
      this.$forceCompute('topSection')
      this.$forceCompute('bottomSection')
    },

    show(value) {
      if (value) {
        // TODO: Having some issues with resetting page scroll with this at the moment
        // document.querySelector('html').classList.add('prevent-scrolling')
      } else {
        document.querySelector('html').classList.remove('prevent-scrolling')
      }
    },
  },

  destroyed() {
    this.onClose()
    // Incase this component is removed when switching back to desktop mode
    document.querySelector('html').classList.remove('prevent-scrolling')
  },

  created() {},
}
</script>

<style lang="scss" scoped>
.wrapper {
  position: fixed;
  top: 0;
  bottom: 64px;
  left: 0;
  right: 0;
  // z-index 1000 is where $zindex-dropdown for bootstrap begins
  // See 'bootstrap'/scss/_variables:680 for bootstrap Z-index master list
  z-index: 999;

  pointer-events: none;

  .overlay {
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1;

    pointer-events: auto;
  }
}

.mobile-sidebar {
  display: flex;
  flex-direction: column;

  position: absolute;
  top: 0;
  bottom: 0;

  max-width: 100vw;
  max-height: 100vh;

  z-index: 2;
  transition: transform 0.3s ease;
  background-color: var(--background, #fff);

  pointer-events: auto;

  &.from-left {
    left: 0;
    transform: translate3d(-100%, 0, 0);

    &.show {
      transform: translate3d(0, 0, 0);
    }
  }

  &.from-right {
    right: 0;
    transform: translate3d(100%, 0, 0);

    &.show {
      transform: translate3d(0, 0, 0);
    }
  }

  .nav.nav-list {
    position: relative;
    min-height: 0;
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;

    .scroll {
      overflow-y: auto;
      flex-shrink: 1;
    }
    .nav-item {
      font-size: 125%;
      .nav-link {
        color: rgba(0, 0, 0, 0.5);
      }
      &.active {
        background-color: #cce5ff;
      }
      &:hover {
        color: rgba(0, 0, 0, 0.65);
        background-color: var(--hover, #f8f9fa);
      }
    }
  }
}



</style>

<i18n lang="json5" scoped>
{
  "en": {
    "label": {
      "banking": "Banking",
      "home": "Home",
      "earnings": "Earnings",
      "explore": "Explore",
      "fans": "Fans",
      "logout": "Logout",
      "messages": "Messages",
      "notifications": "Notifications",
      "paymentMethod": "Payment Method",
      "profile": "My Profile",
      "settings": "Settings",
      "statements": "Statements",
      "vault": "My Vault"
    }
  }
}
</i18n>
