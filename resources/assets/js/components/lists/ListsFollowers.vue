<template>
  <div v-if="!isLoading" class="list-component tag-followers">
    <b-card>

      <b-row>
        <b-col>
          <h2 class="card-title mt-3 mb-1"><span class="tag-title">Followers</span> ({{ totalRows }})</h2>
          <small class="text-muted">Fans who are following or subscribed to my feed</small>
        </b-col>
      </b-row>

      <hr />

      <CtrlBar @apply-filters="applyFilters($event)" />

      <b-row class="mt-2">
        <b-col lg="4" v-for="(s,idx) in shareables" :key="s.id" > 
          <!-- %NOTE: we're using WidgetUser here because users, not the timelines are following me -->
          <WidgetUser :session_user="session_user" :user="s.sharee" :slug="s.sharee_slug" :access_level="s.access_level" :created_at="s.created_at" />
        </b-col>
      </b-row>

      <b-pagination
        v-model="currentPage"
        :total-rows="totalRows"
        :per-page="perPage"
        aria-controls="followers-list"
        v-on:page-click="pageClickHandler"
        class="mt-3"
      ></b-pagination>

    </b-card >
  </div>
</template>

<script>
//import Vuex from 'vuex';
import { eventBus } from '@/app'
  //import { DateTime } from 'luxon'
import moment from 'moment'
import CtrlBar from '@components/lists/CtrlBar'
import WidgetUser from '@components/lists/WidgetUser'

export default {

  props: {
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.shareables || !this.meta
    },
    totalRows() {
      return this.meta ? this.meta.total : 1
    },
  },

  data: () => ({
    moment: moment,
    shareables: null,
    meta: null,
    perPage: 9,
    currentPage: 1,

    sort: {
      by: null,
      dir: 'asc',
    },

    filters: {
      accessLevel: 'all', // %TODO: change default ('all') to null
      onlineStatus: 'all',
    },

  }),

  methods: {
    getPagedData(type=null) {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
      }

      // Apply filters
      if (this.filters.accessLevel && this.filters.accessLevel !== 'all') {
        params.accessLevel = this.filters.accessLevel
      }
      if (this.filters.onlineStatus && this.filters.onlineStatus !== 'all') {
        params.onlineStatus = this.filters.onlineStatus
      }

      // Apply sort
      if (this.sort.by) {
        params.sortBy = this.sort.by
      }
      if (this.sort.dir) {
        params.sortDir = this.sort.dir
      }

      axios.get( route('shareables.indexFollowers'), { params } ).then( response => {
        this.shareables = response.data.data
        this.meta = response.data.meta
      })
    },

    // may adjust filters, but always reloads from page 1
    reloadFromFirstPage() {
      this.doReset()
      this.getPagedData()
    },

    applyFilters({ filters, sort }) {
      console.log('ListsFollowers', { 
        filters,
        sort,
      })
      this.filters = filters
      this.sort = sort
      this.reloadFromFirstPage()
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },

    doReset() {
      this.currentPage = 1
    },

    /*
    // shareable in this context is the [shareables] record
    // shareable.sharee in this context is related user record
    doBlock(shareable) {
      console.log('doBlock() TODO'); // %TODO
    },

    doRestrict(shareable) {
      console.log('doRestrict() TODO'); // %TODO
    },

    doReport(shareable) {
      console.log('doReport() TODO'); // %TODO
    },

    getTimelineUrl(user) {
      return route('spa.index', user.username)
    }
     */
  },

  mounted() { },

  created() {
    this.getPagedData()
  },

  components: {
    CtrlBar,
    WidgetUser,
  },
}
</script>

<style lang="scss" scoped>
</style>

