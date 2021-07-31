<template>
  <div v-if="!isLoading">

    <b-pagination
      v-model="tobj.currentPage"
      :total-rows="tobj.totalRows"
      :per-page="tobj.perPage"
      v-on:page-click="pageClickHandler"
      aria-controls="admin-index-table"
    ></b-pagination>

    <b-table hover 
      :items="tobj.data"
      :fields="fields"
      :current-page="tobj.currentPage"
      :sort-by="tobj.sortBy"
      :sort-desc="tobj.sortDesc"
      :no-local-sorting="true"
      @sort-changed="sortHandler"
      sort-icon-left
      small
    ></b-table>

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

export default {
  name: 'AdminTable',

  props: {
    fields: { type: Array, default: [] },
    indexRouteName: { type: String, default: null },
  },

  computed: {
    isLoading() {
      return !this.indexRouteName
    },
  },

  data: () => ({
    tobj: { // table object
      data: [],
      currentPage: 1,
      perPage: 20,
      totalRows: 0,
      sortBy: 'created_at',
      sortDesc: false,
    },
  }),

  methods: {

    async getData() {
      let params = {
        page: this.tobj.currentPage,
        take: this.tobj.perPage,
        sortBy: this.tobj.sortBy,
        sortDir: this.tobj.sortDesc ? 'desc' : 'asc',
        //...this.encodeQueryFilters(),
      }
      console.log('getData', { params })
      try {
        const response = await axios.get( this.$apiRoute(this.indexRouteName), { params } )
        this.tobj.totalRows = response.data.meta.total // %NOTE: coupled to table
        this.tobj.data = response.data.data
      } catch (e) {
        throw e
        return []
      }
    },

    pageClickHandler(e, page) {
      this.tobj.currentPage = page
      this.getData()
    },

    sortHandler(context) {
      console.log('sortHandler', {
        sortBy: context.sortBy,
        sortDesc: context.sortDesc,
      })
      this.tobj.sortBy = context.sortBy
      this.tobj.sortDesc = context.sortDesc
      this.tobj.currentPage = 1
      this.getData()
    },

  },

  watchers: {},

  created() { 
    this.getData()
  },

  components: {},
}
</script>

<style lang="scss" scoped>
.clickable {
  cursor: pointer;
}
</style>
