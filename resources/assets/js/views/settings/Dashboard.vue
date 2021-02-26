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
          <b-list-group-item>Dapibus ac facilisis in</b-list-group-item>
          <b-list-group-item>Morbi leo risus</b-list-group-item>
          <b-list-group-item>Porta ac consectetur ac</b-list-group-item>
          <b-list-group-item>Vestibulum at eros</b-list-group-item>
        </b-list-group>
      </aside>

      <main class="col-md-7 col-lg-8">
        <router-view :session_user="session_user"></router-view>
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
    ...Vuex.mapGetters(['session_user']),
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
    ...Vuex.mapActions([ 'getMe' ]),
  },

  watch: {
    session_user(value) {
      if (value) {
        this.state = 'loaded'
      }
    }
  },


}
</script>

<style scoped>
</style>
