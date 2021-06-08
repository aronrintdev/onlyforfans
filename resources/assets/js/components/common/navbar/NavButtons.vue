<template>
  <b-navbar-nav class="nav-buttons flex-row justify-content-around" :class="{ 'mobile': mobileStyle }">
    <b-nav-item
      v-for="button in buttons"
      :key="button.name"
      :to="button.to"
      v-b-tooltip
      :title="$t(button.name)"
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
      <b-badge
        v-if="button.name == 'Messages'"
        variant="danger"
        class="unread-messages-count"
        :class="unreadMessagesCount > 0 ? '' : 'd-none'"
      >
        {{unreadMessagesCount}}
      </b-badge>
      <div v-if="showNames" class="label" v-text="$t(button.name)" />
    </b-nav-item>
  </b-navbar-nav>
</template>

<script>
import Vuex from 'vuex'

export default {
  props: {
    mobileStyle: { type: Boolean, default: false },
    unreadMessagesCount: { type: Number, default: 0 }
  },
  computed: {
    ...Vuex.mapGetters([
      'session_user',
      'timeline',
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
            to: { name: 'timeline.followers', params: { slug: (this.timeline ? this.timeline.slug : ''), } },
          },
          {
            name: 'Discover',
            icon: 'compass',
            to: { name: 'index' },
          },
          {
            name: 'Notifications',
            icon: 'bell',
            to: { name: 'notifications.dashboard' },
            // alerts: 4,
          },
          {
            name: 'Messages',
            icon: 'envelope',
            to: { name: 'livechat.default' },
            // alerts: 10000,
          },
        ]
      }
      return items
    },
  },


}
</script>

<style lang="scss" scoped>
.alert-number {
  transform: scale(0.5);
  right: -0.5rem;
  top: -0.5rem;
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
  .unread-messages-count {
    position: absolute;
    top: 7px;
    right: 4px;
    border-radius: 50%;
    font-size: 9px;
    font-weight: 400;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 15px;
    height: 15px;
    padding: 0px 0 0px 0;
  }
}

.mobile {
  .nav-link {
    display: flex;
    flex-direction: column;
    align-content: center;
    justify-content: center;
    width: 100%;
    padding-left: 1rem;
    padding-right: 1rem;
    border: 1px var(--gray) solid;
    border-bottom: 0;
    border-radius: 0.5rem 0.5rem 0 0;

    svg {
      margin-left:  auto;
      margin-right: auto;
    }

    .label {
      text-align: center;
      font-size: 0.75rem;
    }
  }
}
</style>

<i18n lang="json5">
{
  "en": {
    "Home": "Home",
    "Fans": "Fans",
    "Discover": "Discover",
    "Notifications": "Notifications",
    "Messages": "Messages",
    "999+": "999+",
  }
}
</i18n>
