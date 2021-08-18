<template>
  <div class="app d-flex flex-column">
    <transition name="quick-fade" mode="out-in">
      <SiteLoading v-if="loading" key="loading" />
    </transition>
    <!-- Header -->
    <MainNavBar v-if="!mobile" class="header" />

    <div class="content flex-grow-1 d-flex" :class="{ 'p-3': !mobile, 'mobile': mobile }">
      <MobileSidebarMenu v-if="mobile" :show="mobileMenuOpen" fromRight @change="onMobileMenuChange" />
      <transition name="quick-fade" mode="out-in">
        <router-view />
      </transition>
    </div>

    <NavButtonsMobile v-if="mobile" :mobile-style="mobile" class="bottom-nav" />
    <SiteFooter v-if="!isFooterHidden && !mobile" />

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
    ...Vuex.mapState(['session_user', 'mobile', 'mobileMenuOpen', 'screenSize', 'unread_messages_count']),

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
    // If full app is loading
    loading: false,
    onlineMonitor: null,
    unreadMessagesCount: 0,
    isFooterHidden: false,
  }),

  methods: {
    ...Vuex.mapActions(['getMe', 'getUnreadMessagesCount']),
    ...Vuex.mapMutations([ 'UPDATE_MOBILE', 'UPDATE_SCREEN_SIZE', 'UPDATE_MOBILE_MENU_OPEN' ]),
    startOnlineMonitor() {
      if (this.session_user) {
        this.onlineMonitor = this.$echo.join(`user.status.${this.session_user.id}`)
        this.$echo.private(`${this.session_user.id}-message`)
          .listen('MessageSentEvent', () => {
            this.getUnreadMessagesCount();
          });
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
      if (newVal.name.indexOf('messages') > -1) {
        this.isFooterHidden = true;
      }
    }
  },

  mounted() {
    this.updateScreenSize(this.$vssWidth)
  },

  created() {
    if (this.$route.name.indexOf('messages') > -1) {
      this.isFooterHidden = true;
    }
    if (!this.session_user) {
      this.loading = true
      this.getMe();
      this.getUnreadMessagesCount();
    }
    if (this.$vssWidth < this.mobileWidth) {
      this.UPDATE_MOBILE(true)
    }
  },
}
</script>

<style lang="scss" scoped>
.content.mobile {
  padding-bottom: 64px;
}
</style>
