<template>
  <div class="app d-flex flex-column">
    <transition name="quick-fade" mode="out-in">
      <SiteLoading v-if="loading" key="loading" />
    </transition>
    <!-- Header -->
    <MainNavBar class="header" />

    <div class="content flex-grow-1 d-flex" :class="{ 'p-3': !mobile, 'mobile': mobile, 'messages': isMessagesView, 'thread': isThreadView }">
      <MobileSidebarMenu v-if="mobile" :show="mobileMenuOpen" fromRight @change="onMobileMenuChange" />
      <transition name="quick-fade" mode="out-in">
        <router-view />
      </transition>
    </div>

    <NavButtonsMobile v-if="mobile && !isThreadView" :mobile-style="mobile" class="bottom-nav" />
    <SiteFooter v-if="isFooterVisible" />

    <Modals />

    <Toaster />

    <EventUpdater />
  </div>
</template>

<script>
import _ from 'lodash'
import Vuex from 'vuex';
import VueScreenSize from 'vue-screen-size'

import EventUpdater from '@components/EventUpdater'
import MainNavBar from '@components/common/MainNavbar'
import Modals from '@components/Modals'
import MobileSidebarMenu from '@components/common/navbar/MobileSidebarMenu'
import NavButtonsMobile from '@components/common/navbar/NavButtonsMobile'
import SiteFooter from '@views/templates/SiteFooter'
import SiteLoading from '@components/common/SiteLoading'
import Toaster from '@components/Toaster'

export default {
  name: 'App',
  components: {
    EventUpdater,
    MainNavBar,
    Modals,
    MobileSidebarMenu,
    NavButtonsMobile,
    SiteFooter,
    SiteLoading,
    Toaster,
  },

  mixins: [VueScreenSize.VueScreenSizeMixin],

  props: {
    toggleMobileAt: { type: [String, Number], default: 'md', },
    screenSizesTypes: { type: Object, default: () => ({ xs: 0, sm: 0, md: 0, lg: 0, xl: 0 }) }
  },

  computed: {
    ...Vuex.mapState(['session_user', 'mobile', 'mobileMenuOpen', 'screenSize', 'unread_messages_count', 'unread_notifications_count']),

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
    },

    isFooterVisible() {
      if (this.mobile || this.$route.path.startsWith('/messages')) {
        // hide footer if we're in Messages, or on mobile
        return false
      }

      return true
    },

    isMessagesView() {
      return this.$route.path.startsWith('/messages')
    },

    isThreadView() {
      return this.$route.path.startsWith('/messages') && this.$route.params.id
    },
  },

  data: () => ({
    // If full app is loading
    loading: false,
    onlineMonitor: null,
    unreadMessagesCount: 0,
    unreadNotificationsCount: 0,
  }),

  methods: {
    ...Vuex.mapActions(['getMe', 'getUnreadMessagesCount', 'getUnreadNotificationsCount']),
    ...Vuex.mapMutations([ 'UPDATE_MOBILE', 'UPDATE_SCREEN_SIZE', 'UPDATE_MOBILE_MENU_OPEN' ]),
    startOnlineMonitor() {
      if (this.session_user) {
        this.onlineMonitor = this.$echo.join(`user.status.${this.session_user.id}`)

        // TODO: Replace this with a different event channel listener, this channel no longer exists
        // this.$echo.private(`${this.session_user.id}-message`)
        //   .listen('MessageSentEvent', () => {
        //     this.getUnreadMessagesCount();
        //   });
      }
    },

    onMobileMenuChange(value) {
      this.UPDATE_MOBILE_MENU_OPEN(value)
    },

    updateScreenSize(value) {
      var screenSize = _.findKey(this.screenSizes, i => (i === _.max(_.filter(this.screenSizes, size => ( value >= size )))))
      if (screenSize !== this.screenSize) {
        this.UPDATE_SCREEN_SIZE(screenSize)
      }
    },

    scrollToTop() {
      this.$el.scrollTo(0, 0)
    },
  },

  watch: {
    session_user(value) {
      if (value) {
        this.startOnlineMonitor()
        this.loading = false
      }
    },
    $vssWidth(value) {
      var mobile = value < this.mobileWidth
      if (this.mobile !== mobile) {
        this.UPDATE_MOBILE(mobile)
      }
      this.updateScreenSize(value)
    },
    $route: function(newVal) {
      // whenever the route changes, go to the top of the new page
      this.scrollToTop()
    }, 

  },

  mounted() {
    this.updateScreenSize(this.$vssWidth)

    // intercept so tooltips don't display on mobile
    this.$root.$on('bv::tooltip::show', bvEvent => {
      if (this.mobile) {
        bvEvent.preventDefault() 
      }
    })
  },

  created() {
    if (!this.session_user) {
      this.loading = true
      this.getMe();
      this.getUnreadMessagesCount();
      this.getUnreadNotificationsCount();
    }
    if (this.$vssWidth < this.mobileWidth) {
      this.UPDATE_MOBILE(true)
    }
  },
}
</script>

<style lang="scss" scoped>
.content.mobile {
  padding-top: 15px;
  padding-bottom: 68px;
  min-height: calc(100% - 68px);

  &.messages {
    padding-top: 0;
  }

  &.thread {
    padding-bottom: 0;
  }
}
</style>
