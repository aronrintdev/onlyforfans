<template>
  <div v-if="!isLoading" class="container-fluid" id="view-settings">

    <section class="row">
      <article class="col-sm-12">
      </article>
    </section>

    <section class="row">

      <aside class="col-md-3 col-lg-2">
        <b-list-group>
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
      </aside>

      <main class="col-md-9 col-lg-10">
        <transition mode="out-in" name="quick-fade">
          <router-view :session_user="session_user" :user_settings="user_settings" />
        </transition>
      </main>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';

export default {
  name: 'SettingsDashboard',

  components: {
  },

  computed: {
    ...Vuex.mapGetters(['session_user', 'user_settings']),
    ...Vuex.mapState({
      subscriptionCount: state => state.subscriptions.count
    }),

    isLoading() {
      return !this.session_user || !this.user_settings
    },

    routes() {
      var routes = [
        {
          name: 'General',
          to: { name: 'settings.general', params: {} },
        }, 
        {
          name: 'Profile',
          to: { name: 'settings.profile', params: {} },
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
        name: 'Subscription and promotions',
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

      routes.push({
        name: 'Login Sessions',
        to: { name: 'settings.sessions', params: {} },
      })

      routes.push({
        name: 'Referrals',
        to: { name: 'settings.referrals', params: {} },
      })

      routes.push({
        name: 'Bookmarks',
        to: { name: 'settings.bookmarks', params: {} },
      })

      routes.push({
        name: 'Message with Tip Only',
        to: { name: 'settings.subscriptions', params: {} },
      })

      routes = routes.map(route => ({
        ...route,
        active: this.checkActive(route)
      }))

      return routes
    }

  },

  data: () => ({}),

  created() { },

  mounted() {
    this.getMe()
    this['subscriptions/updateCount']()
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
  },

  watch: {
    $route() {
      this.$forceCompute('routes')
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
    "General": "General",
    "Profile": "Profile",
    "Privacy": "Privacy",
    "Security": "Security",
    "Payment Methods": "Payment Methods",
    "Earnings": "Earnings",
    "My Subscriptions": "My Subscriptions",
    "Subscription and promotions": "Subscription and promotions",
    "Banking": "Banking",
    "Login Sessions": "Login Sessions",
    "Referrals": "Referrals",
    "Bookmarks": "Bookmarks",
    "Message with Tip Only": "Message with Tip Only"
  }
}
</i18n>
