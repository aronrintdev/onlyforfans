<template>
  <div v-if="!isLoading" class="list-component tag-following">
    <b-card>

      <b-row>
        <b-col>
          <h2 class="card-title mt-3 mb-1"><span class="tag-title">Following</span> ({{ totalRows }})</h2>
          <small class="text-muted">Creators who I am following or subscribed to</small>
        </b-col>
      </b-row>

      <hr />

      <CtrlBar @apply-filters="applyFilters($event)" />

      <b-row class="mt-3">
        <b-col lg="4" v-for="(s,idx) in shareables" :key="s.id" > 
          <!-- %NOTE: s is the [shareables] record, s.shareable is the related, 'morhped', 'shareable' object (eg timelines) -->
          <!-- %NOTE: we're using WidgetTimeline here because you're following a timeline, not a user directly -->
          <WidgetTimeline :session_user="session_user" :timeline="s.shareable" :access_level="s.access_level" :created_at="s.created_at">
            <b-card-text @click="handleFavorite(s.shareable_id)" class="mb-2 clickable">
              <fa-icon v-if="s.is_favorited" fixed-width :icon="['fas', 'star']" style="color:#007bff" />
              <fa-icon v-else fixed-width :icon="['far', 'star']" style="color:#007bff" />
              Add to favorites
            </b-card-text>

            <b-button variant="primary">Message</b-button>
            <b-button @click="renderTip(s.shareable, 'timelines')" variant="primary">Tip</b-button>
            <b-button v-if="s.access_level==='default'" @click="renderSubscribe(s.shareable)" variant="primary">Subscribe</b-button>
            <b-button @click="renderCancel(s.shareable, s.access_level)" variant="primary">Unfollow</b-button>
          </WidgetTimeline>
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
import { eventBus } from '@/eventBus'
//import { DateTime } from 'luxon'
import moment from 'moment'
import mixinModal from '@mixins/modal'
import CtrlBar from '@components/lists/CtrlBar'
import WidgetTimeline from '@components/lists/WidgetTimeline'

export default {

  mixins: [mixinModal],

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
    getPagedData(type = null) {

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
      params.isFollowing = true;

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

    handleFavorite(id) {
      const temp = this.shareables.map(s => {
        if (s.shareable_id === id) {
          this.toggleFavorite(s.is_favorited, id);
          s.is_favorited = s.is_favorited ? 0 : 1;
        }
        return s;
      });

      this.shareables = temp;
    },

    async toggleFavorite(isFavorited, id) {
      let response
      if (isFavorited) { // remove
        response = await axios.post(`/favorites/remove`, {
          favoritable_type: 'timelines',
          favoritable_id: id,
        })
      } else { // add
        response = await axios.post(`/favorites`, {
          favoritable_type: 'timelines',
          favoritable_id: id,
        })
      }
    },

    /*
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

    eventBus.$on('update-timelines', (timelineId) => {
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

