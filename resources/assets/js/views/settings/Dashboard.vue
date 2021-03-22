<template>
  <div v-if="!isLoading" class="container-fluid" id="view-settings">

    <section class="row">
      <article class="col-sm-12">
      </article>
    </section>

    <section class="row">

      <aside class="col-md-3 col-lg-2">
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
          <b-list-group-item>
            <router-link :to="{ name: 'settings.bookmarks', params: {} }">Bookmarks</router-link>
          </b-list-group-item>
        </b-list-group>
      </aside>

      <main class="col-md-9 col-lg-10">
        <router-view :session_user="session_user" :user_settings="user_settings"></router-view>
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

  data: () => ({ }),

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

<style scoped>
</style>
