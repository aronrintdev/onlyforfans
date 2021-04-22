<template>
  <div>
    <b-table hover 
      id="favorites-posts"
      :items="favorites"
      :fields="favoriteFields"
      :current-page="currentPage"
    >
      <template #cell(favoriteable_id)="row">
        <router-link :to="{ name: 'posts.show', params: { slug: row.value } }">Details</router-link>
      </template>
    </b-table>
    <b-pagination
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="favorites-posts"
      v-on:page-click="pageClickHandler"
    ></b-pagination>
  </div>
</template>

<script>
//import Vuex from 'vuex'
import moment from 'moment'

export default {

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

    favoriteFields: [
      {
        key: 'created_at',
        label: 'Date',
        formatter: (value, key, item) => {
          return moment(value).format('MMMM Do, YYYY')
        }
      },
      {
        key: 'favoritable_type',
        label: 'Type',
      },
      {
        key: 'creator.name',
        label: 'Creator',
      },
      {
        key: 'favoritable_id',
        label: '',
      },
    ],
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

  components: { },
}
</script>

<style lang="scss" scoped>
</style>
