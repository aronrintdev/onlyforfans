<template>
  <b-navbar-nav class="nav-buttons flex-row justify-content-around" :class="{ 'mobile': mobileStyle }">
    <b-nav-item
      v-for="button in buttons"
      :key="button.name"
      :to="button.to"
      v-b-tooltip.hover.bottom
      :title="$t(button.name)"
      class="pt-2"
    >
      <fa-layers fixed-width class="fa-lg">
        <fa-icon :icon="button.icon" class="mx-auto" />
        <fa-layers-text
          v-if="button.alerts"
          counter
          :value="button.alerts < 1000 ? button.alerts.toString() : $t('999+')"
          position="top-right"
          class="alert-number"
        />
      </fa-layers>
      <div v-if="mobileStyle" class="font-size-smaller">
        {{ $t(button.name) }}
      </div>
      <div v-if="showNames" class="label" v-text="$t(button.name)" />
    </b-nav-item>
  </b-navbar-nav>
</template>

<script>
import Vuex from 'vuex'

export default {
  props: {
    mobileStyle: { type: Boolean, default: false },
    unreadMessagesCount: { type: Number, default: 0 },
    unreadNotificationsCount: { type: Number, default: 0 },
  },
  computed: {
    ...Vuex.mapGetters([
      'session_user',
    ]),

    showNames() {
      return false
    },

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
          {
            name: 'Fans',
            icon: 'users',
            to: { name: 'lists.followers' },
          },
          {
            name: 'Explore',
            icon: 'compass',
            to: { name: 'timelines.explore' },
          },
          {
            name: 'Notifications',
            icon: 'bell',
            to: { name: 'notifications.dashboard' },
            alerts: this.unreadNotificationsCount,
            // alerts: 4,
          },
          {
            name: 'Messages',
            icon: 'envelope',
            to: { name: 'livechat.default' },
            alerts: this.unreadMessagesCount,
          },
        ]
      }
      return items
    },
  },

  watch: {
    unread_messages_count() {
      this.$forceCompute('buttons')
    },
    unread_notifications_count() {
      this.$forceCompute('buttons')
    },
  },

}
</script>

<style lang="scss" scoped>
.alert-number {
  transform: scale(0.5);
  right: -0.5rem;
  top: -0.5rem;
  background-color: var(--danger);
}
.nav-item {
  margin-right: 0.25 * 1rem;
  margin-left: 0.25 * 1rem;
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
</style>

<i18n lang="json5">
{
  "en": {
    "Home": "Home",
    "Fans": "Fans",
    "Explore": "Explore",
    "Notifications": "Notifications",
    "Messages": "Messages",
    "999+": "999+",
  }
}
</i18n>
