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

      <b-row class="mt-3">
        <b-col lg="4" v-for="(s,idx) in shareables" :key="s.id" >
          <b-card no-body class="background mb-5">
            <b-card-img :src="s.shareable.cover.filepath" alt="s.shareable.slug" top></b-card-img>

            <b-card-body class="py-1">

              <div class="avatar-img">
                <router-link :to="{ name: 'timeline.show', params: { slug: s.shareable.slug } }">
                  <b-img thumbnail rounded="circle" class="w-100 h-100" :src="s.shareable.avatar.filepath" :alt="s.shareable.slug" :title="s.shareable.name" />
                </router-link>
              </div>

              <div class="shareable-id">
                <b-card-title class="mb-1">
                  <router-link :to="{ name: 'timeline.show', params: { slug: s.shareable.slug } }">{{ s.shareable.name }}</router-link>
                </b-card-title>
                <b-card-sub-title class="mb-1">
                  <router-link :to="{ name: 'timeline.show', params: { slug: s.shareable.slug } }">@{{ s.shareable.slug }}</router-link>
                </b-card-sub-title>
              </div>

              <b-card-text class="mb-2"><fa-icon fixed-width :icon="['far', 'star']" style="color:#007bff" /> Add to favorites</b-card-text>

              <b-button variant="outline-primary">Message</b-button>
              <b-button @click="renderTip(s.shareable)" variant="outline-success">Send Tip</b-button>
              <b-button v-if="s.access_level==='default'" @click="renderSubscribe(s.shareable)" variant="outline-info">Premium Access</b-button>
              <b-button variant="outline-warning">Cancel</b-button>
              <div>
                <small v-if="s.access_level==='premium'" class="text-muted">subscribed since {{ moment(s.updated_at).format('MMM DD, YYYY') }}</small>
                <small v-else class="text-muted">following for free since {{ moment(s.updated_at).format('MMM DD, YYYY') }}</small>
              </div>
              <!--
              <pre>
                Access Level: {{ s.access_level }}
                {{ JSON.stringify(s, null, "\t") }}
              </pre>
              -->

            </b-card-body>

          </b-card>
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
      axios.get( route('shareables.indexFollowing'), { params } ).then( response => {
        this.shareables = response.data.data
        this.meta = response.data.meta
      })
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },

    renderSubscribe(selectedTimeline) {
      this.$log.debug('ListsFollowing.renderSubscribe() - emit', {
        selectedTimeline,
      });
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
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

