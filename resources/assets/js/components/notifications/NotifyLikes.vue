<template>
  <div v-if="!isLoading">

    <b-card>
      <h4 class="card-title">Likes Received ({{ totalRows }})</h4>
      <hr />
      <b-card-text>
        <ul class="list-unstyled" id="likeables-list">
          <b-media v-for="(l,idx) in likeables" tag="li" class="mb-0">
            <template #aside>
              <b-img width="48" height="48" rounded="circle" :src="l.liker.avatar.filepath" :alt="l.liker.slug" :title="l.liker.name" />
            </template>
            <h6 class="mt-0 mb-1">{{ l.liker.name }}  <small class="text-muted">@{{ l.liker.username}}</small></h6>
            <p class="mb-0">liked your 
              <router-link :to="{ name: 'posts.show', params: { slug: l.likeable.slug } }">{{ toSingular(l.likeable_type) }}</router-link>
            </p>
            <small>{{ moment(l.created_at).format('MMM DD, YYYY') }}</small>
            <hr class="mt-2 mb-3" />
          </b-media>
        </ul>
      </b-card-text>
    </b-card>

    <b-pagination
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="likeables-list"
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
      return !this.session_user || !this.likeables
    },

    totalRows() {
      return this.meta ? this.meta.total : 1
    },
  },

  data: () => ({
    likeables: null,
    meta: null,
    moment: moment,

    perPage: 10,
    currentPage: 1,
  }),

  methods: {
    toSingular(str) {
      switch (str) {
        case 'posts':
        case 'comments':
        case 'mediafiles':
        default:
          return str.slice(0, -1)
      }
    },

    getPagedData() {
      axios.get( route('likeables.index'), { params: { page: this.currentPage, take: this.perPage } } ).then( response => {
        this.likeables = response.data.data
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
