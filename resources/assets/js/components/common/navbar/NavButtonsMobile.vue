<template>
  <b-navbar-nav class="nav-buttons flex-row justify-content-around">
    <b-nav-item
      v-if="session_user"
      @click="sidebarToggle"
      class="pt-2"
    >
      <AvatarWithStatus :user="session_user" centerAvatar noTooltip noLink imageOnly />
    </b-nav-item>
    <b-nav-item
      v-for="button in buttons"
      :key="button.name"
      :to="button.to"
      :title="$t(button.name)"
      class="pt-2"
    >
      <fa-layers fixed-width class="fa-lg">
        <fa-icon :icon="button.icon" class="mx-auto" />
        <fa-layers-text
          v-if="button.alerts"
          counter
          :value="button.alerts < 1000 ? button.alerts : $t('999+')"
          position="top-right"
          class="alert-number"
        />
      </fa-layers>
      <div v-if="showNames" class="font-size-smaller">
        {{ $t(button.name) }}
      </div>
    </b-nav-item>

  </b-navbar-nav>
</template>

<script>
/**
 * resources/assets/js/components/common/navbar/NavButtonsMobile.vue
 */
import Vuex from 'vuex'

import AvatarWithStatus from '@components/user/AvatarWithStatus'

export default {
  name: 'NavButtonsMobile',

  components: {
    AvatarWithStatus,
  },

  props: {},

  computed: {
    ...Vuex.mapGetters([ 'session_user', 'unread_messages_count', 'mobileMenuOpen' ]),

    buttons() {
      var items = []
      items = [ ...items,
        {
          name: 'Home',
          icon: 'home',
          to: { name: 'index' },
        },
      ]
      if (this.session_user) {
        items = [ ...items,
          // {
          //   key: "fans",
          //   name: 'Fans',
          //   icon: 'users',
          //   to: { name: 'lists.followers' },
          // },
          {
            key: 'explore',
            name: 'Explore',
            icon: 'compass',
            to: { name: 'timelines.explore' },
          },
          {
            key: 'notifications',
            name: 'Notifications',
            icon: 'bell',
            to: { name: 'notifications.dashboard' },
            // alerts: 4,
          },
          {
            key: 'messages',
            name: 'Messages',
            icon: 'envelope',
            to: { name: 'livechat.default' },
            alerts: this.unread_messages_count,
          },
        ]
      }
      return items
    },
  },

  data: () => ({
    showNames: true,
  }),

  methods: {
    ...Vuex.mapMutations([ 'UPDATE_MOBILE_MENU_OPEN' ]),
    sidebarToggle() {
      this.UPDATE_MOBILE_MENU_OPEN(!this.mobileMenuOpen)
    }
  },

  watch: {
    unread_messages_count() {
      this.$forceCompute('buttons')
    },
  },

  created() {},
}
</script>

<style lang="scss" scoped>
.nav-buttons {
  position: fixed;
  bottom: 0;
  z-index: 100;

  width: 100vw;
  background-color: var(--light);
  .nav-item {
    width: 20%;
    flex-grow: 1;
    margin-left: 0;
    margin-right: 0;
    color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-content: center;
    justify-content: center;
    flex-grow: 1;
    position: relative;
    .nav-link {
      display: flex;
      align-items: center;
    }

  }
  .nav-link {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    align-content: center;
    justify-content: center;
    width: 100%;
    color: rgba(0, 0, 0, 0.5);
    // padding-left: 1rem;
    // padding-right: 1rem;
    // border: 1px var(--gray) solid;
    // border-bottom: 0;
    // border-top: 0;
    // border-radius: 0.5rem 0.5rem 0 0;

    svg {
      margin-left:  auto;
      margin-right: auto;
    }

    .label {
      text-align: center;
      font-size: 0.75rem;
    }
  }

  .alert-number {
    transform: scale(0.5);
    right: -0.5rem;
    top: -0.5rem;
    background-color: var(--danger);
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "Profile": "Menu",
    "Home": "Home",
    "Fans": "Fans",
    "Explore": "Explore",
    "Notifications": "Notifications",
    "Messages": "Messages",
    "999+": "999+",
  }
}
</i18n>
