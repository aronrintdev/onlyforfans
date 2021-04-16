<template>
  <div v-if="!isLoading">

    <h4 class="card-title"><span class="tag-title">{{ title }}</span> ({{ totalRows }})</h4>

    <ul class="list-unstyled">
      <b-media v-for="(n,idx) in notifications" :key="n.id" tag="li" class="mb-0">
        <template #aside>
          <b-img width="48" height="48" rounded="circle" :src="n.data.actor.avatar" :alt="n.data.actor.slug" :title="n.data.actor.name" />
        </template>
        <h6 class="mt-0 mb-1">{{ n.data.actor.name }}  <small class="text-muted">@{{ n.data.actor.username}}</small></h6>
<small><em>{{ n.data.amount }}</em></small>
        <p class="mb-0">
          <template v-if="n.type==='App\\Notifications\\TimelineFollowed'">followed you</template>
          <template v-if="n.type==='App\\Notifications\\TimelineSubscribed'">subcribed for {{ n.data.amount }}</template>
          <template v-if="n.type==='App\\Notifications\\ResourceLiked'"> liked your
            <template v-if="n.data.resource_type==='posts'">
              <router-link :to="{ name: 'posts.show', params: { slug: n.data.resource_slug } }">post</router-link>
            </template>
          </template>
          <template v-if="n.type==='App\\Notifications\\TipReceived'">sent you a tip
            <template v-if="n.data.amount"> for {{ n.data.amount | niceCurrency }}</template>
            <template v-if="n.data.resource_type==='posts'">
              on <router-link :to="{ name: 'posts.show', params: { slug: n.data.resource_slug } }">post</router-link>
            </template>
          </template>
          <template v-if="n.type==='App\\Notifications\\ResourcePurchased'">purchased
            <template v-if="n.data.resource_type==='posts'">
              your <router-link :to="{ name: 'posts.show', params: { slug: n.data.resource_slug } }">post</router-link>
            </template>
            <template v-if="n.data.amount"> for {{ n.data.amount | niceCurrency }}</template>
          </template>
        </p>
        <small>{{ moment(n.created_at).format('MMM DD, YYYY') }}</small>
        <hr class="mt-2 mb-3" />
      </b-media>
    </ul>

    <b-pagination
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="notifications-list"
      v-on:page-click="pageClickHandler"
      class="mt-3"
    ></b-pagination>

  </div>
</template>

<script>
//import Vuex from 'vuex'
//import { DateTime } from 'luxon'
import moment from 'moment'

export default {

  props: {
    session_user: null,
    filter: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.notifications || !this.meta || !this.filter
    },

    totalRows() {
      return this.meta ? this.meta.total : 1
    },

    filterToType() {
      switch (this.filter) {
        case 'purchases':
          return 'ResourcePurchased'
        case 'liked':
          return 'ResourceLiked'
        case 'followers':
          return 'TimelineFollowed'
        case 'subscribers':
          return 'TimelineSubscribed'
        case 'tips':
          return 'TipReceived'
        case 'none':
        default:
          return null
      }
    },
    title() {
      switch (this.filter) {
        case 'liked':
          return 'Likes'
        case 'followers':
          return 'Followers'
        case 'subscribers':
          return 'Subscribers'
        case 'tips':
          return 'Tips'
        case 'none':
        default:
          return 'All'
      }
    },

  },

  data: () => ({
    moment: moment,
    notifications: null,
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

  watch: { },

  mounted() { },

  created() {
    this.getPagedData()
  },

  components: {
  },
}
</script>

<style scoped>
/*
.card-title .tag-title:first-letter {
    text-transform: capitalize;
}
 */
</style>
