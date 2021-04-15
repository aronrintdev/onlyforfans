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
          <!--
          <b-tabs card lazy @activate-tab="renderTab" id="notification-tabs">
            -->
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

            <b-tab title="followers" data-filter="Followers">
              <b-card-text>
                <NotifyList filter="followers" :session_user="session_user" />
              </b-card-text>
            </b-tab>

            <b-tab title="subscribers" data-filter="Subscribers">
              <b-card-text>
                <NotifyList filter="subscribers" :session_user="session_user" />
              </b-card-text>
            </b-tab>

          </b-tabs>
        </b-card>

        <!--
        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          aria-controls="notifications-list"
          v-on:page-click="pageClickHandler"
          class="mt-3"
        ></b-pagination>
        -->

      </main>

    </section>

  </div>
</template>

<script>
import Vuex from 'vuex';
//import { DateTime } from 'luxon'
//import moment from 'moment'
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

    /*
    totalRows() {
      return this.meta ? this.meta.total : 1
    },
     */
  },

  data: () => ({
    //notifications: null,
    //meta: null,
    //moment: moment,

    perPage: 10,
    currentPage: 1,
  }),

  methods: {
    ...Vuex.mapActions([
      'getMe',
      //'getUserSettings',
    ]),

    /*
    renderTab(newTabIndex, prevTabIndex, bvEvent) {
      const filter = $('.tab-pane')[newTabIndex].dataset.filter
      this.currentPage = 1
      switch (filter) {
        case 'likes_received':
          this.getPagedData('ResourceLiked')
          break;
        case 'tips_received':
          this.getPagedData('TipReceived')
          break;
        case 'posts_purchased':
          this.getPagedData('PostPurchased')
          break;
        case 'timeline_followed':
          this.getPagedData('TimelineFollowed')
          break;
        case 'timeline_subscribed':
          this.getPagedData('TimelineSubscribed')
          break;
        case 'all':
        default:
          this.getPagedData()
      }
      console.log('debugMe', { filter, newTabIndex, prevTabIndex, bvEvent, })
    },
     */

    /*
    getPagedData(type=null) {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
      }
      if (type) {
        params.type = type // PostTipped, etc
      }
      axios.get( route('notifications.index'), { params } ).then( response => {
        this.notifications = response.data.data
        this.meta = response.data.meta
      })
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },
     */

  },

  created() { 
    this.getMe()
    //this.getPagedData()
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
