<template>
  <div v-if="!isLoading" class="container-fluid" id="view-lists">

    <section class="row">
      <article class="col-sm-12">
        <h1>Lists</h1>
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
          <router-view :session_user="session_user" />
        </transition>
      </main>

    </section>

    <Modals />

  </div>
</template>

<script>
import Vuex from 'vuex';
import Modals from '@components/Modals'

export default {

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user
    },
  },

  data: () => ({
    routes: [
      {
        name: 'Bookmarks',
        to: { name: 'lists.bookmarks', params: {} },
      }, 
      {
        name: 'Following',
        to: { name: 'lists.following', params: {} },
      }, 
      {
        name: 'Followers',
        to: { name: 'lists.followers', params: {} },
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
      }
    }
  },

  components: {
    Modals,
  },

}
</script>

<style scoped>
</style>
