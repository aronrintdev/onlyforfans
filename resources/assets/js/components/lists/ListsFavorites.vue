<template>
  <div v-if="!isLoading">

    <b-card title="Favorites">
      <b-card-text>
        <div>
          <b-tabs content-class="mt-3">
            <b-tab title="All" active>
              <!-- table -->
            </b-tab>
            <b-tab title="Posts">
              <!-- layout like followers -->
            </b-tab>
            <b-tab title="Creators">
            </b-tab>
            <b-tab title="Photos">
            </b-tab>
            <b-tab title="Videos">
            </b-tab>
          </b-tabs>
        </div>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
//import Vuex from 'vuex';
import { eventBus } from '@/app'
//import { DateTime } from 'luxon'
import moment from 'moment'

export default {

  props: {
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.session_user
    },
  },

  data: () => ({
    moment: moment,
    following: null,
    meta: null,
    perPage: 10,
    currentPage: 1,

  }),

  methods: {
    getPagedData(type=null) {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
      }
      if (this.filter && this.filter!=='none') {
        params.type = this.filterToType // PostTipped, etc
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
  },

  mounted() {
  },

  created() {
  },

  components: {
  },
}
</script>

<style scoped>
</style>

