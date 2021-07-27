<template>
  <div v-if="!isLoading" class="list-component tab-posts tag-grid-layout">
    <b-card>

      <!-- <b-row>
        <b-col>
          <h2 class="card-title mb-1"><span class="tag-title">Posts</span> ({{ totalRows }})</h2>
          <small class="text-muted">Favorite posts</small>
        </b-col>
      </b-row>

      <hr /> -->

      <b-row class="mt-3">
        <b-col lg="4" v-for="(f,idx) in favorites" :key="f.id" > 
          <WidgetPost :session_user="session_user" :post="f.favoritable" :use_mid="false" :is_feed="false" />
        </b-col>
      </b-row>

      <b-pagination
        v-model="currentPage"
        :total-rows="totalRows"
        :per-page="perPage"
        aria-controls="favorites-posts"
        v-on:page-click="pageClickHandler"
        class="mt-3"
      ></b-pagination>

    </b-card >

  </div>
</template>

<script>
//import Vuex from 'vuex'
import moment from 'moment'
import WidgetPost from '@components/posts/Display'

export default {

  props: {
    session_user: null,
    setTabInfo: { type: Function },
  },

  computed: {
    isLoading() {
      return !this.favorites || !this.meta
    },

    totalRows() {
      return this.meta ? this.meta.total : 1
    },
  },

  data: () => ({
    moment: moment,
    favorites: null,
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
      // filters
      params.favoritable_type = 'posts'
      axios.get( route('favorites.index'), { params } ).then( response => {
        this.favorites = response.data.data
        this.meta = response.data.meta
        this.setTabInfo('posts', this.meta.total)
      })
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },

  },

  created() {
    this.getPagedData()
  },

  mounted() { },

  components: { 
    WidgetPost
  },
}
</script>

<style lang="scss" scoped>
</style>
