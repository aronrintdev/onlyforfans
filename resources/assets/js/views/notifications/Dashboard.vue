<template>
  <div v-if="!isLoading" class="container-fluid" id="view-notifications">

    <section class="row">

      <main class="col-md-12 mx-auto py-3 py-md-0">
        <b-card >

          <h4>Notifications</h4>

          <b-tabs card lazy id="notification-tabs">

            <b-tab data-filter="none" active>
              <template #title>All</template>
              <b-card-text>
                <NotifyList filter="none" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="liked">
              <template #title> <fa-icon fixed-width icon="heart" /><span> Liked</span></template>
              <b-card-text>
                <NotifyList filter="liked" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="tips">
              <!--
              <template #title> <fa-icon fixed-width icon="usd-circle" /> Tips</template>
              -->
              <template #title> <fa-icon fixed-width icon="dollar-sign" /><span> Tips</span></template>
              <b-card-text>
                <NotifyList filter="tips" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="purchases">
              <!--
              <template #title> <fa-icon fixed-width icon="usd-square" /> Purchases</template>
              -->
              <template #title> <fa-icon fixed-width icon="file-invoice-dollar" /><span> Purchases</span></template>
              <b-card-text>
                <NotifyList filter="purchases" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="Followers">
              <template #title> <fa-icon fixed-width icon="walking" /><span> Followers</span></template>
              <b-card-text>
                <NotifyList filter="followers" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="Subscribers">
              <!--
              <template #title> <fa-icon fixed-width icon="lock-open-alt" /> Subscribers</template>
              -->
              <template #title> <fa-icon fixed-width :icon="['fas', 'user-plus']" /><span> Subscribed</span></template>
              <b-card-text>
                <NotifyList filter="subscribers" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="comments">
              <template #title> <fa-icon fixed-width icon="comments" /><span> Comments</span></template>
              <b-card-text>
                <NotifyList filter="comments" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="messages">
              <template #title> <fa-icon fixed-width icon="envelope" /><span> Messages</span></template>
              <b-card-text>
                <NotifyList filter="messages" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="staff" v-if="session_user.is_verified">
              <template #title> <fa-icon fixed-width icon="sitemap" /><span> Staff</span></template>
              <b-card-text>
                <NotifyList filter="staff" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab data-filter="tagged">
              <template #title> <fa-icon fixed-width icon="tags" /><span> Tagged</span></template>
              <b-card-text>
                <NotifyList filter="tagged" :session_user="session_user" />
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

<style lang="scss">
@media (max-width: 768px) {
  #view-notifications {
    .card-body {
      padding: 1em;
    }
    #notification-tabs {
      margin: 0 -0.5em;
      .card-title {
        font-size: 1.2rem;
      }
      .card-header {
        width: 100%;
        background: none;
        padding-left: 0.6em;
        padding-right: 0.6em;

        .nav-tabs {
          flex-flow: row nowrap;
          justify-content: space-between;

          .nav-link {
            white-space: nowrap;
            padding: 0.1em 1em;

            span {
              display: none;
            }
          }
        }
      }
      .tab-content {
        .tab-pane {
          padding: 1em 0.5em;
        }
      }
    }
  }
}
@media (max-width: 576px) {
  #view-notifications {
    #notification-tabs {
      .card-header {
        .nav-tabs {
          .nav-link {
            padding: 0.1em 0.8em;
          }
        }
      }
    }
  }
}
@media (max-width: 480px) {
  #view-notifications {
    #notification-tabs {
      .card-header {
        .nav-tabs {
          .nav-link {
            padding: 0.1em 0.4em;
          }
        }
      }
    }
  }
}
</style>

<i18n lang="json5" scoped>
  {
    "en": {
    }
  }
  </i18n>
