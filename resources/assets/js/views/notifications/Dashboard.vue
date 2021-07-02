<template>
  <div v-if="!isLoading" class="container-fluid" id="view-notifications">

    <section class="row">

      <main class="col-md-9 col-lg-9 mx-auto">
        <b-card >

          <h1>Notifications</h1>

          <b-tabs card lazy id="notification-tabs">

            <b-tab data-filter="none" active>
              <template #title> <fa-icon fixed-width icon="folder-plus" /> All</template>
              <b-card-text>
                <NotifyList filter="none" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="liked">
              <template #title> <fa-icon fixed-width icon="heart" /> Liked</template>
              <b-card-text>
                <NotifyList filter="liked" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="tips">
              <!--
              <template #title> <fa-icon fixed-width icon="usd-circle" /> Tips</template>
              -->
              <template #title> <fa-icon fixed-width icon="dollar-sign" /> Tips</template>
              <b-card-text>
                <NotifyList filter="tips" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="purchases">
              <!--
              <template #title> <fa-icon fixed-width icon="usd-square" /> Purchases</template>
              -->
              <template #title> <fa-icon fixed-width icon="file-invoice-dollar" /> Purchases</template>
              <b-card-text>
                <NotifyList filter="purchases" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="Followers">
              <template #title> <fa-icon fixed-width icon="walking" /> Followers</template>
              <b-card-text>
                <NotifyList filter="followers" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="Subscribers">
              <!--
              <template #title> <fa-icon fixed-width icon="lock-open-alt" /> Subscribers</template>
              -->
              <template #title> <fa-icon fixed-width icon="lock-open" /> Subscribers</template>
              <b-card-text>
                <NotifyList filter="subscribers" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="comments">
              <template #title> <fa-icon fixed-width icon="comments" /> Comments</template>
              <b-card-text>
                <NotifyList filter="comments" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="messages">
              <template #title> <fa-icon fixed-width icon="envelope" /> Messages</template>
              <b-card-text>
                <NotifyList filter="messages" :session_user="session_user" />
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
