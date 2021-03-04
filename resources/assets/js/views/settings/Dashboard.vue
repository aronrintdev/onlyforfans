<template>
  <div class="container" id="view-home_timeline">

    <section class="row" v-if="state !== 'loading'">
      <article class="col-sm-12">
      </article>
    </section>

    <section class="row" v-if="state !== 'loading'">

      <aside class="col-md-5 col-lg-4">
        <b-list-group>
          <b-list-group-item>
            <router-link :to="{ name: 'settings.general', params: {} }">General</router-link>
          </b-list-group-item>
          <b-list-group-item>
            <router-link :to="{ name: 'settings.profile', params: {} }">Profile</router-link>
          </b-list-group-item>
          <b-list-group-item>
            <router-link :to="{ name: 'settings.privacy', params: {} }">Privacy</router-link>
          </b-list-group-item>
          <b-list-group-item>
            <router-link :to="{ name: 'settings.security', params: {} }">Security</router-link>
          </b-list-group-item>
          <b-list-group-item>
            <router-link :to="{ name: 'settings.earnings', params: {} }">Earnings</router-link>
          </b-list-group-item>
          <b-list-group-item>
            <router-link :to="{ name: 'settings.sessions', params: {} }">Login Sessions</router-link>
          </b-list-group-item>
          <b-list-group-item>
            <router-link :to="{ name: 'settings.referrals', params: {} }">Referrals</router-link>
          </b-list-group-item>
        </b-list-group>
      </aside>

      <main class="col-md-7 col-lg-8">
        <router-view :session_user="session_user" :user_settings="user_settings"></router-view>
      </main>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
//import MiniMyStatsWidget from '@components/user/MiniMyStatsWidget.vue';

export default {
  components: {
  },

  computed: {
    ...Vuex.mapGetters(['session_user', 'user_settings']),
  },

  data: () => ({
    state: 'loading', // loading | loaded
  }),

  created() {
  },

  mounted() {
    if (!this.session_user) {
      this.getMe()
    } else {
      this.state = 'loaded'
    }
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
        this.state = 'loaded'
        if (!this.user_settings) {
          this.getUserSettings( { userId: this.session_user.id })
          //this.$store.dispatch('getUserSettings', { userId: this.session_user.id })
        }
      }
    }
  },

}
</script>

<style scoped>
</style>
