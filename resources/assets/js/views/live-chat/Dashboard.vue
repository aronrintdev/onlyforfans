<template>
  <div v-if="!isLoading" class="container-fluid" id="view-livechat">

    <section class="row">
      <article class="col-sm-12">
        Live Chat Dashboard
      </article>
    </section>

    <section class="row">

      <aside class="col-md-3 col-lg-2">
        Sidebar
      </aside>

      <main class="col-md-9 col-lg-10">
          <!--
        <router-view />
          -->
        <transition mode="out-in" name="quick-fade">
          <router-view :session_user="session_user" />
        </transition>
      </main>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';

export default {
  name: 'LivechatDashboard',

  components: {
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user
    },

    routes() {
      var routes = [
        {
          name: 'ListScheduled',
          to: { name: 'chatmessages.scheduled', params: {} },
        }, 
        {
          name: 'CreateThread',
          to: { name: 'chatthreads.create', params: {} },
        }, 
        {
          name: 'Gallery',
          to: { name: 'chatthreads.gallery', params: {} },
        }, 
        {
          name: 'ShowThread',
          to: { name: 'chatthreads.show', params: {} },
        }, 
      ]

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
  },

  methods: {
    ...Vuex.mapActions([
      'getMe',
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
        //        if (!this.user_settings) {
        //          this.getUserSettings( { userId: this.session_user.id })
        //        }
      }
    },
  },

}
</script>
