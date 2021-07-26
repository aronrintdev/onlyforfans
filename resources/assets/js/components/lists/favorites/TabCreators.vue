<template>
  <div v-if="!isLoading" class="list-component tab-timelines tag-grid-layout">
    <b-card>

      <b-row>
        <b-col>
          <h2 class="card-title mb-1"><span class="tag-title">Creators</span> ({{ totalRows }})</h2>
          <small class="text-muted">Favorite creators</small>
        </b-col>
      </b-row>

      <hr />

      <b-row class="mt-3">
        <b-col lg="4" v-for="(f,idx) in favorites" :key="f.id" > 
          <WidgetTimeline :timeline="f.favoritable" :access_level="() => 'todo'" :created_at="f.created_at">
            <b-button variant="primary">Message</b-button>
            <b-button @click="renderTip(f.favoritable, 'timelines')" variant="primary">Tip</b-button>
          </WidgetTimeline>
        </b-col>
      </b-row>

      <b-pagination
        v-model="currentPage"
        :total-rows="totalRows"
        :per-page="perPage"
        aria-controls="favorites-timelines"
        v-on:page-click="pageClickHandler"
        class="mt-3"
      ></b-pagination>

    </b-card >

  </div>

</template>

<script>
//import Vuex from 'vuex'
import moment from 'moment'
import mixinModal from '@mixins/modal'
import WidgetTimeline from '@components/lists/WidgetTimeline'

export default {

  mixins: [mixinModal],

  props: {
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
      params.favoritable_type = 'timelines'
      axios.get( route('favorites.index'), { params } ).then( response => {
        this.favorites = response.data.data
        this.meta = response.data.meta
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
    WidgetTimeline,
  },
}
</script>

<style lang="scss" scoped>
</style>
