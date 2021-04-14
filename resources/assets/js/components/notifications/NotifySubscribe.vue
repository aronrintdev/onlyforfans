<template>
  <div v-if="!isLoading">

    <b-card>
      <h4 class="card-title">Subscribed ({{ totalRows }})</h4>
      <hr />
      <b-card-text>
        <ul class="list-unstyled" id="subscribed-list">
          <b-media v-for="(s,idx) in subscribed" tag="li" class="mb-0">
            <template #aside>
              <b-img width="48" height="48" rounded="circle" :src="s.sharee.avatar.filepath" :alt="s.sharee.slug" :title="s.sharee.name" />
            </template>
            <h6 class="mt-0 mb-1">{{ s.sharee.name }}  <small class="text-muted">@{{ s.sharee.username}}</small></h6>
            <p class="mb-0">subscribed to your profile</p>
            <small>{{ moment(s.created_at).format('MMM DD, YYYY') }}</small>
            <hr class="mt-2 mb-3" />
          </b-media>
        </ul>
      </b-card-text>
    </b-card>

    <b-pagination
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="subscribed-list"
      v-on:page-click="pageClickHandler"
      class="mt-3"
    ></b-pagination>

  </div>
</template>

<script>
//import Vuex from 'vuex';
//import { DateTime } from 'luxon'
import moment from 'moment'

export default {

  props: {
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.subscribed
    },

    totalRows() {
      return this.meta ? this.meta.total : 1
    },
  },

  data: () => ({
    subscribed: null,
    meta: null,
    moment: moment,

    perPage: 10,
    currentPage: 1,
  }),

  methods: {

    getPagedData() {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
        shareable_type: 'timelines', 
        access_level: 'premium', // ie, only subscribers, not followers
      }
      axios.get( route('shareables.index'), { params: { page: this.currentPage, take: this.perPage } } ).then( response => {
        this.subscribed = response.data.data
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
</style>
