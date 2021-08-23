<template>
  <div v-if="!isLoading" class="container-fluid" :class="mobile ? 'px-0' : ''" id="view-settings">
    <WithSidebar
      :focusMain="$route.name !== 'settings.default'"
      @back="onBack"
    >

      <template #mobileTitle>
        <div class="h2 px-3 py-2 d-flex align-items-center">
          <b-btn variant="link" :to="{ name: 'index' }" class="d-flex align-items-center">
            <fa-icon icon="arrow-left" size="lg" fixed-width />
          </b-btn>
          <fa-icon icon="cogs" fixed-width class="ml-3 mr-2" />
          {{ $t('title.sidebar') }}
        </div>
      </template>

      <template #mobileMainNavTopTitle>
        <span class="h4 mb-0" v-if="currentRoute">
          {{ $t(`title.${currentRoute.name}`) }}
        </span>
      </template>

      <template #sidebar>
        <b-list-group :flush="mobile">
          <b-list-group-item
            v-for="(link, i) in routes"
            :key="i"
            :to="link.to"
            :active="link.active"
            class="d-flex align-items-center"
          >
            <span v-text="$t(link.name)" />
            <fa-icon icon="caret-right" class="ml-auto" />
          </b-list-group-item>
        </b-list-group>
      </template>

      <transition mode="out-in" name="quick-fade">
        <router-view
          :session_user="session_user"
          :user_settings="user_settings"
          :timeline="timeline"
          :class="mobile ? '' : ''"
        />
      </transition>
    </WithSidebar>
  </div>
</template>

<script>
import Vuex from 'vuex'
import helpers from '@helpers'

import WithSidebar from '@views/layouts/WithSidebar'

export default {
  name: 'SettingsDashboard',

  components: {
    WithSidebar,
  },

  computed: {
    ...Vuex.mapGetters(['session_user', 'user_settings', 'timeline']),
    ...Vuex.mapState({
      mobile: state => state.mobile,
      subscriptionCount: state => state.subscriptions.count
    }),

    isLoading() {
      return !this.session_user || !this.user_settings || !this.timeline
    },

    routes() {
      var routes = [
        {
          name: 'Profile',
          to: { name: 'settings.profile', params: {} },
        },
        {
          name: 'Account',
          to: { name: 'settings.general', params: {} },
        },
        {
          name: 'Notifications',
          to: { name: 'settings.notifications', params: {} },
        },
        {
          name: 'Privacy',
          to: { name: 'settings.privacy', params: {} },
        },
        {
          name: 'Security',
          to: { name: 'settings.security', params: {} },
        },
        {
          name: 'Verification',
          to: { name: 'settings.verify', params: {} },
        },
      ]

      routes.push({
        name: 'Payment Methods',
        to: { name: 'settings.payments', params: {} },
      })

      routes.push({
        name: 'Subscription and Promotions',
        to: { name: 'settings.subscription', params: {} },
      })

      if (this.subscriptionCount.active > 0 || this.subscriptionCount.inactive > 0) {
        routes.push({
          name: 'My Subscriptions',
          to: { name: 'settings.my-subscriptions' },
        })
      }

      routes.push({
        name: 'Banking',
        to: { name: 'settings.banking', params: {} },
      })

      // routes.push({
      //   name: 'Login Sessions',
      //   to: { name: 'settings.sessions', params: {} },
      // })

      routes.push({
        name: 'Referrals',
        to: { name: 'settings.referrals', params: {} },
      })

      // routes.push({
      //   name: 'Bookmarks',
      //   to: { name: 'settings.bookmarks', params: {} },
      // })

      routes.push({
        name: 'Messages',
        to: { name: 'settings.subscriptions', params: {} },
      })

      // if (this.user_settings.is_creator) {
        routes.push({
          name: 'Managers',
          to: { name: 'settings.managers', params: {} },
        })
      // }

      if (this.user_settings?.is_manager || false) {
        routes.push({
          name: 'Staff Members',
          to: { name: 'settings.staffmembers', params: {} },
        })
      }

      routes = routes.map(route => ({
        ...route,
        active: this.checkActive(route)
      }))

      return routes
    }, // routes

    currentRoute() {
      if (!this.$route) {
        return {}
      }
      return helpers.firstWhere(this.routes, o => this.checkActive(o) )
    }
  },

  data: () => ({}),

  created() { },

  mounted() {
    this.getMe()
    this['subscriptions/updateCount']()
    if (this.$route.name === 'settings.default' && !this.mobile) {
      this.$router.push({ name: 'settings.profile' })
      this.$forceCompute('routes')
    }
  },

  methods: {
    ...Vuex.mapActions([
      'getMe',
      'getUserSettings',
      'subscriptions/updateCount'
    ]),

    checkActive(route) {
      if (this.$router.currentRoute.name === route.to.name) {
        return true
      }
      for (var matched of this.$router.currentRoute.matched) {
        if (matched.name === route.to.name) {
          return true
        }
      }
      return false
    },

    onBack() {
      this.$router.push({ name: 'settings.default' })
    },
  },

  watch: {
    $route() {
      if (this.$route.name === 'settings.default' && !this.mobile) {
        this.$router.push({ name: 'settings.profile' })
      }
      this.$forceCompute('routes')
      this.$forceCompute('currentRoute')

    },

    session_user(value) {
      if (value) {
        if (!this.user_settings) {
          this.getUserSettings( { userId: this.session_user.id })
        }
      }
    },
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "title": {
      "sidebar": "Settings",
      "Account": "Account",
      "Banking": "Banking",
      "Bookmarks": "Bookmarks",
      "Earnings": "Earnings",
      "General": "General",
      "Login Sessions": "Login Sessions",
      "Message with Tip Only": "Message with Tip Only",
      "My Subscriptions": "My Subscriptions",
      "Managers": "Managers",
      "Messages": "Messages",
      "Notifications": "Notifications",
      "Payment Methods": "Payment Methods",
      "Privacy": "Privacy",
      "Profile": "Edit Profile",
      "Referrals": "Referrals",
      "Security": "Security",
      "Subscription and promotions": "Subscriptions",
      "Subscription and Promotions": "Subscriptions",
      "Verification": "Verification"
    },

    "Account": "Account",
    "Banking": "Banking",
    "Bookmarks": "Bookmarks",
    "Earnings": "Earnings",
    "General": "General",
    "Login Sessions": "Login Sessions",
    "Message with Tip Only": "Message with Tip Only",
    "My Subscriptions": "My Subscriptions",
    "Managers": "Managers",
    "Messages": "Messages",
    "Notifications": "Notifications",
    "Payment Methods": "Payment Methods",
    "Privacy": "Privacy",
    "Profile": "Profile",
    "Referrals": "Referrals",
    "Security": "Security",
    "Subscription and promotions": "Subscription and promotions",
    "Subscription and Promotions": "Subscription and Promotions",
    "Verification": "Verification"
  }
}
</i18n>
