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

      <b-row class="justify-content-end mt-0">
        <b-col md="6" class="text-right">

          <b-dropdown no-caret right ref="feedCtrls" variant="transparent" id="feed-ctrl-dropdown" class="tag-ctrl">
            <template #button-content>
              <b-icon icon="filter" scale="1.8" variant="primary"></b-icon>
            </template>
            <b-dropdown-form>
              <b-form-group label="Subscription">
                <b-form-radio v-model="accessLevel" size="sm" name="access-level" value="all">All</b-form-radio>
                <b-form-radio v-model="accessLevel" size="sm" name="access-level" value="premium">Paid</b-form-radio>
                <b-form-radio v-model="accessLevel" size="sm" name="access-level" value="default">Free</b-form-radio>
              </b-form-group>
              <b-dropdown-divider></b-dropdown-divider>
              <b-form-group label="Online Status">
                <b-form-radio v-model="onlineStatus" size="sm" name="online-status" value="all">All</b-form-radio>
                <b-form-radio v-model="onlineStatus" size="sm" name="online-status" value="online">Online</b-form-radio>
                <b-form-radio v-model="onlineStatus" size="sm" name="online-status" value="offline">Offline</b-form-radio>
              </b-form-group>
            </b-dropdown-form>
          </b-dropdown>

          <b-dropdown no-caret right ref="feedCtrls" variant="transparent" id="feed-ctrl-dropdown" class="tag-ctrl">
            <template #button-content>
              <b-icon icon="arrow-down-up" scale="1.3" variant="primary"></b-icon>
            </template>
            <b-dropdown-form>
              <b-form-group label="">
                <b-form-radio v-model="sortBy" size="sm" name="sort-by" value="activity">Last Activity</b-form-radio>
                <b-form-radio v-model="sortBy" size="sm" name="sort-by" value="name">Name</b-form-radio>
                <b-form-radio v-model="sortBy" size="sm" name="sort-by" value="start_date">Started</b-form-radio>
              </b-form-group>
              <b-dropdown-divider></b-dropdown-divider>
              <b-form-group label="">
                <b-form-radio v-model="sortDir" size="sm" name="sort-dir" value="asc">Ascending</b-form-radio>
                <b-form-radio v-model="sortDir" size="sm" name="sort-dir" value="desc">Descending</b-form-radio>
              </b-form-group>
            </b-dropdown-form>
          </b-dropdown>

        </b-col>
      </b-row>

      <b-row class="mt-2">
        <b-col lg="4" v-for="(s,idx) in shareables" :key="s.id" >
          <b-card no-body class="background mb-5">
            <b-card-img :src="s.sharee.cover.filepath" alt="s.sharee.username" top></b-card-img>

            <b-card-body class="py-1">

              <div class="avatar-img">
                <router-link :to="{ name: 'timeline.show', params: { slug: s.sharee.username } }">
                  <b-img thumbnail rounded="circle" class="w-100 h-100" :src="s.sharee.avatar.filepath" :alt="s.sharee.username" :title="s.sharee.name" />
                </router-link>
              </div>

              <div class="sharee-id">
                <b-card-title class="mb-1">
                  <router-link :to="{ name: 'timeline.show', params: { slug: s.sharee.username } }">{{ s.sharee.name }}</router-link>
                </b-card-title>
                <b-card-sub-title class="mb-1">
                  <router-link :to="{ name: 'timeline.show', params: { slug: s.sharee.username } }">@{{ s.sharee.username }}</router-link>
                </b-card-sub-title>
              </div>

              <b-card-text class="mb-2"><fa-icon fixed-width icon="star" style="color:#007bff" /> Add to favorites</b-card-text>

              <b-button variant="outline-primary">Message</b-button>
              <b-button variant="outline-danger">Block</b-button>
              <b-button variant="outline-warning">Discount</b-button>
              <div>
                <small v-if="s.access_level==='premium'" class="text-muted">subscribed since {{ moment(s.updated_at).format('MMM DD, YYYY') }}</small>
                <small v-else class="text-muted">following for free since {{ moment(s.updated_at).format('MMM DD, YYYY') }}</small>
              </div>
              <!--
              <pre>{{ JSON.stringify(s, null, "\t") }}</pre>
              -->

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
    perPage: 10,
    currentPage: 1,

    sortBy: null,
    sortDir:  'asc',
    accessLevel: 'all',
    onlineStatus: 'all',
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
      axios.get( route('shareables.indexFollowers'), { params } ).then( response => {
        this.shareables = response.data.data
        this.meta = response.data.meta
      })
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },
  },

  mounted() { },

  created() {
    this.getPagedData()
  },

  components: {
  },
}
</script>

<style lang="scss" scoped>
</style>

