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
            :active="$router.currentRoute.name === link.to.name"
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
  components: {
  },

  computed: {
    ...Vuex.mapGetters(['session_user', 'user_settings']),

    isLoading() {
      return !this.session_user || !this.user_settings
    },
  },

  data: () => ({
    routes: [
      {
        name: 'General',
        to: { name: 'settings.general', params: {} },
      }, {
        name: 'Profile',
        to: { name: 'settings.profile', params: {} },
      }, {
        name: 'Privacy',
        to: { name: 'settings.privacy', params: {} },
      }, {
        name: 'Security',
        to: { name: 'settings.security', params: {} },
      }, {
        name: 'Payment Methods',
        to: { name: 'settings.payments', params: {} },
      }, {
        name: 'Earnings',
        to: { name: 'settings.earnings', params: {} },
      }, {
        name: 'Login Sessions',
        to: { name: 'settings.sessions', params: {} },
      }, {
        name: 'Referrals',
        to: { name: 'settings.referrals', params: {} },
      }, {
        name: 'Bookmarks',
        to: { name: 'settings.bookmarks', params: {} },
      }, {
        name: 'Message with Tip Only',
        to: { name: 'settings.subscriptions', params: {} },
      },
    ]
  }),

  created() { },

  mounted() {
    this.getMe()
  },

  methods: {
    ...Vuex.mapActions([
      'getMe',
      'getUserSettings',
    ]),
  },

  watch: {
    session_user(value) {
      if (value) {
        if (!this.user_settings) {
          this.getUserSettings( { userId: this.session_user.id })
        }
      }
    }
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
    "Login Sessions": "Login Sessions",
    "Referrals": "Referrals",
    "Bookmarks": "Bookmarks",
    "Message with Tip Only": "Message with Tip Only"
  }
}
</i18n>
