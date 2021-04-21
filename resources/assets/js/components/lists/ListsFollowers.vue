<template>
  <div v-if="!isLoading" class="list-component tag-followers">
    <b-card>

      <b-row>
        <b-col>
          <h2 class="card-title mb-1"><span class="tag-title">Followers</span> ({{ totalRows }})</h2>
          <small class="text-muted">Fans who are following or subscribed to my feed</small>
        </b-col>
      </b-row>

      <hr />

      <CtrlBar @apply-filters="applyFilters($event)" />

      <b-row class="mt-2">
        <b-col lg="4" v-for="(s,idx) in shareables" :key="s.id" >
          <b-card no-body class="background mb-5">
            <b-card-img :src="s.sharee.cover.filepath" alt="s.sharee.username" top></b-card-img>

            <b-card-body class="py-1">

              <div class="last-seen">Last seen TBD</div>
              <div class="banner-ctrl"><fa-icon fixed-width icon="star" style="color:#007bff" /></div>

              <div class="avatar-img">
                <router-link :to="{ name: 'timeline.show', params: { slug: s.sharee.username } }">
                  <b-img thumbnail rounded="circle" class="w-100 h-100" :src="s.sharee.avatar.filepath" :alt="s.sharee.username" :title="s.sharee.name" />
                </router-link>
              </div>

              <div class="sharee-id">
                <b-card-title class="mb-1">
                  <router-link :to="{ name: 'timeline.show', params: { slug: s.sharee.username } }">{{ s.sharee.name }}</router-link>
                  <fa-icon v-if="s.access_level==='premium'" fixed-width :icon="['fas', 'rss-square']" style="color:#138496; font-size: 16px;" />
                </b-card-title>
                <b-card-sub-title class="mb-1">
                  <router-link :to="{ name: 'timeline.show', params: { slug: s.sharee.username } }">@{{ s.sharee.username }}</router-link>
                </b-card-sub-title>
              </div>

              <b-card-text class="mb-2"><fa-icon fixed-width icon="star" style="color:#007bff" /> Add to favorites</b-card-text>

              <b-button variant="outline-primary">Message</b-button>
              <b-button variant="outline-danger">Restrict</b-button>
              <b-button variant="outline-warning">Discount</b-button>
              <div>
                <small v-if="s.access_level==='premium'" class="text-muted">subscribed since {{ moment(s.created_at).format('MMM DD, YYYY') }}</small>
                <small v-else class="text-muted">following for free since {{ moment(s.created_at).format('MMM DD, YYYY') }}</small>
              </div>
              <!-- <pre>{{ JSON.stringify(s, null, "\t") }}</pre> -->

            </b-card-body>

          </b-card>
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
  },

  mounted() { },

  created() {
    this.getPagedData()
  },

  components: {
    CtrlBar,
  },
}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}
</style>

