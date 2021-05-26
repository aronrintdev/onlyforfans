<template>
  <div v-if="!isLoading" class="container-fluid" id="view-livechat">

    <section class="row">
      <article class="col-sm-12">
        Live Chat Dashboard
      </article>
    </section>

    <section class="row">

      <aside class="col-md-3 col-lg-2">

        <div>
          Messages
        </div>

        <div>
          Search
        </div>

        <div>
          Unread First
        </div>

        <div>
        <b-list-group>
          <b-list-group-item
            v-for="(ct, idx) in chatthreads"
            :key="ct.id"
            :to="link(ct.id)"
            :active="false"
            class=""
          >
            <div>{{ ct.id }}</div>
            <div>{{ ct.chatmessages[0].mcontent || "none" }}</div>
            <div>{{ ct.chatmessages.length }}</div>
          </b-list-group-item>
        </b-list-group>
      </div>
      </aside>

      <main class="col-md-9 col-lg-10">
        <transition mode="out-in" name="quick-fade">
          <router-view :session_user="session_user" />
        </transition>
      </main>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment'

export default {
  name: 'LivechatDashboard',

  components: {
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user || !this.chatthreads
    },

    routes() {
      let routes = [
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
        active: this.isActiveRoute(route)
      }))

      return routes
    }

  },

  data: () => ({

    moment: moment,

    chatthreads: null,
    meta: null,
    perPage: 10,
    currentPage: 1,

  }), // data

  created() { 
    this.getMe()
  },

  mounted() { },

  methods: {

    ...Vuex.mapActions([
      'getMe',
    ]),

    link(id) {
      //return { name: 'chatthreads.show', params: }
      return { name: 'chatthreads.show', params: { id: id } }
    },


    async getChatthreads() {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
        participant_id: this.session_user.id,
      }
      const response = await axios.get( this.$apiRoute('chatthreads.index'), { params } )
      this.chatthreads = response.data.data
      this.meta = response.meta
    },

    isActiveRoute(route) {
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

    getRouteByName(name) {
      return this.routes.find(r => r.name === name)
    },

  }, // methods

  watch: {

    $route() {
      this.$forceCompute('routes')
    },

    session_user(value) {
      if (value) {
        if (!this.chatthreads) { // initial load only, depends on sesssion user (synchronous)
          this.getChatthreads()
        }
      }
    },

  }, // watch

}
</script>

<style lang="scss" scoped>
</style>

<style lang="scss">
</style>
