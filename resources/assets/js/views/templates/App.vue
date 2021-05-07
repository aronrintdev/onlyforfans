<template>
  <div class="app d-flex flex-column">
    <!-- Header -->
    <MainNavBar class="header" />
    <div class="content flex-grow-1" :class="{ 'p-3': !mobile }">
      <transition name="quick-fade" mode="out-in">
        <router-view />
      </transition>
    </div>

    <Toaster />

    <EventUpdater />

    <SiteFooter />

  </div>
</template>

<script>
import _ from 'lodash'
import Vuex from 'vuex';
import VueScreenSize from 'vue-screen-size'
import MainNavBar from '@components/common/MainNavbar'
import EventUpdater from '@components/EventUpdater'
import Toaster from '@components/Toaster'
import SiteFooter from '@views/templates/SiteFooter'

export default {
  name: 'App',
  components: {
    MainNavBar,
    EventUpdater,
    Toaster,
    SiteFooter,
  },

  mixins: [VueScreenSize.VueScreenSizeMixin],

  props: {
    toggleMobileAt: { type: [String, Number], default: 'md', },
    screenSizesTypes: { type: Object, default: () => ({ xs: 0, sm: 0, md: 0, lg: 0, xl: 0 }) }
  },

  computed: {
    ...Vuex.mapState(['session_user', 'mobile', 'screenSize']),

    mobileWidth() {
      if (typeof this.toggleMobileAt === 'number') {
        return this.toggleMobileAt
      }
      return parseInt(getComputedStyle(document.documentElement)
        .getPropertyValue(`--breakpoint-${this.toggleMobileAt}`).replace('px', ''))
    },
    screenSizes() {
      return _.mapValues(this.screenSizesTypes ,(value, key) => {
        return parseInt(getComputedStyle(document.documentElement)
          .getPropertyValue(`--breakpoint-${key}`).replace('px', ''))
          || value
      })
    }
  },

  data: () => ({
    onlineMonitor: null,
    unreadMessagesCount: 0,
  }),

  methods: {
    ...Vuex.mapActions(['getMe']),
    ...Vuex.mapMutations([ 'UPDATE_MOBILE', 'UPDATE_SCREEN_SIZE', 'UPDATE_UNREAD_MESSAGES_COUNT' ]),
    startOnlineMonitor() {
      if (this.session_user) {
        this.onlineMonitor = this.$echo.join(`user.status.${this.session_user.id}`)
        this.$echo.private(`${this.session_user.id}-message`)
          .listen('MessageSentEvent', () => {
            this.getUnreadMessagesCount();
          });
      }
    },
    getUnreadMessagesCount: function() {
      this.axios.get('/unread-messages-count')
        .then((res) => {
          this.UPDATE_UNREAD_MESSAGES_COUNT({
            unread_messages_count: res.data.unread_messages_count,
          });
        });
    }
  },

  watch: {
    session_user(value) {
      if (value) {
        this.startOnlineMonitor()
      }
    },
    $vssWidth(value) {
      var mobile = value < this.mobileWidth
      if (this.mobile !== mobile) {
        this.UPDATE_MOBILE(mobile)
      }
      var screenSize = _.findKey(this.screenSizes, i => (i === _.max(_.filter(this.screenSizes, size => ( value >= size )))))
      if (screenSize !== this.screenSize) {
        this.UPDATE_SCREEN_SIZE(screenSize)
      }
    },
  },

  mounted() {
    if (!this.session_user) {
      this.getMe();
      this.getUnreadMessagesCount();
    }
    if (this.$vssWidth < this.mobileWidth) {
      this.mobile = true
    }
  },
}
</script>

<style lang="scss">
</style>
