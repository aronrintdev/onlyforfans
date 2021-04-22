<template>
  <div v-if="!isLoading" class="list-component tag-following">
    <b-card>

      <b-row>
        <b-col>
          <h2 class="card-title mb-1"><span class="tag-title">Following</span> ({{ totalRows }})</h2>
          <small class="text-muted">Creators who I am following or subscribed to</small>
        </b-col>
      </b-row>

      <hr />

      <CtrlBar @apply-filters="applyFilters($event)" />

      <b-row class="mt-3">
        <b-col lg="4" v-for="(s,idx) in shareables" :key="s.id" > 
          <!-- %NOTE: s is the [shareables] record, s.shareable is the related, 'morhped', 'shareable' object (eg timelines) -->

          <WidgetTimeline :session_user="session_user" :timeline="s.shareable" :access_level="s.access_level" :created_at="s.created_at" />
          <!-- <pre> Access Level: {{ s.access_level }} {{ JSON.stringify(s, null, "\t") }} </pre> -->
        </b-col>
      </b-row>

      <b-pagination
        v-model="currentPage"
        :total-rows="totalRows"
        :per-page="perPage"
        aria-controls="following-list"
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
import WidgetTimeline from '@components/lists/WidgetTimeline'

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

      axios.get( route('shareables.indexFollowing'), { params } ).then( response => {
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
    renderSubscribe(selectedTimeline) {
      this.$log.debug('ListsFollowing.renderSubscribe() - emit', { selectedTimeline, });
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
        data: {
          timeline: selectedTimeline,
        }
      })
    },

    renderCancel(selectedTimeline, accessLevel) {
      // normally these attributes would be passed from server, but in this context we can determine them on client-side...
      selectedTimeline.is_following = true
      selectedTimeline.is_subscribed = (accessLevel==='premium')
      this.$log.debug('ListsFollowing.renderCancel() - emit', { selectedTimeline, });
      eventBus.$emit('open-modal', {
        key: 'render-follow',
        data: {
          timeline: selectedTimeline,
        }
      })
    },

    renderTip(selectedTimeline) { // to a timeline (user)
      eventBus.$emit('open-modal', {
        key: 'render-tip',
        data: { 
          resource: selectedTimeline,
          resource_type: 'timelines', 
        },
      })
    },

    // shareable in this context is the [shareables] record
    // shareable.shareable in this context is related shareable record (eg timeline)
    doReport(shareable) {
      console.log('doReport() TODO'); // %TODO
    },

    getTimelineUrl(timeline) {
      return route('spa.index', timeline.slug)
    }
     */

  },

  mounted() { },

  created() {
    this.getPagedData()

    eventBus.$on('update-originator', () => {
      this.getPagedData()
    })
  },

  components: {
    CtrlBar,
    WidgetTimeline,
  },
}
</script>

<style lang="scss" scoped>
</style>

