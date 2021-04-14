<template>
  <div v-if="!isLoading" class="container-fluid" id="view-notifications">

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

      <main class="col-md-9 col-lg-9">
          <router-view :session_user="session_user" />
        <!--
        <transition mode="out-in" name="quick-fade">
          <router-view :session_user="session_user" />
        </transition>
        -->
      </main>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';

export default {
  computed: {
    ...Vuex.mapGetters([
      'session_user', 
      //'user_settings',
    ]),

    isLoading() {
      return !this.session_user
      //return !this.session_user || !this.user_settings
    },
  },

  data: () => ({
    routes: [
      {
        name: 'Likes',
        to: { name: 'notifications.likes', params: {} },
      }, 
      {
        name: 'Subscribers',
        to: { name: 'notifications.subscribe', params: {} },
      },
    ]
  }),

  methods: {
    ...Vuex.mapActions([
      'getMe',
      //'getUserSettings',
    ]),
  },

  created() { },

  mounted() {
    this.getMe()
  },

  watch: {
    session_user(value) {
      if (value) {
        /*
        if (!this.user_settings) {
          this.getUserSettings( { userId: this.session_user.id })
        }
         */
      }
    }
  },

  components: { },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "Likes": "Likes",
    "Subscribers": "Subscribers",
  }
}
</i18n>
