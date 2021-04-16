<template>
  <div v-if="!isLoading" class="container-fluid" id="view-notifications">

    <section class="row">
      <article class="col-sm-12">
        <h1>Notifications</h1>
      </article>
    </section>

    <section class="row">

      <main class="col-md-9 col-lg-9">
        <b-card no-body>
          <b-tabs card lazy id="notification-tabs">

            <b-tab title="All" data-filter="none" active>
              <b-card-text>
                <NotifyList filter="none" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab title="Liked" data-filter="liked">
              <b-card-text>
                <NotifyList filter="liked" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab title="Tips" data-filter="tips">
              <b-card-text>
                <NotifyList filter="tips" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab title="Purchases" data-filter="purchases">
              <b-card-text>
                <NotifyList filter="purchases" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab title="Followers" data-filter="Followers">
              <b-card-text>
                <NotifyList filter="followers" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab title="Subscribers" data-filter="Subscribers">
              <b-card-text>
                <NotifyList filter="subscribers" :session_user="session_user" />
              </b-card-text>
            </b-tab>

          </b-tabs>
        </b-card>

      </main>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
import NotifyList from '@components/notifications/NotifyList'

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
  }),

  methods: {
    ...Vuex.mapActions([
      'getMe',
      //'getUserSettings',
    ]),

  },

  created() { 
    this.getMe()
  },

  mounted() { },

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

  components: {
    NotifyList,
  },

}
</script>

<i18n lang="json5" scoped>
  {
    "en": {
    }
  }
  </i18n>
