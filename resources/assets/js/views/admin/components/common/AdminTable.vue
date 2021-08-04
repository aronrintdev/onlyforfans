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
    >
      <template #cell(id)="data">
        <span @click="doEmit('render-show', data.item)" class="clickable">{{ data.item.id | niceGuid }}</span>
      </template>
      <template #cell(ctrls)="data">
        <span class="">
          <fa-icon @click="doEmit('render-ellipsis', data.item)" :icon="['fas', 'ellipsis-h']" class="clickable fa-sm" />
        </span>
        <span @click="doEmit('render-flag', data.item)" class="">
          <fa-icon v-if="flagCount(data.item)>0" :icon="['fas', 'flag']" class="clickable fa-sm text-danger" />
          <!--
          <fa-icon v-else :icon="['far', 'flag']" class="clickable fa-sm" />
          -->
        </span>
      </template>
    </b-table>

    <b-pagination
      v-model="tobj.currentPage"
      :total-rows="tobj.totalRows"
      :per-page="tobj.perPage"
      v-on:page-click="pageClickHandler"
      aria-controls="admin-index-table"
    ></b-pagination>

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

export default {
  name: 'AdminTable',

  props: {
    fields: { type: Array, default: [] },
    tblFilters: null,
    indexRouteName: { type: String, default: null },
    encodedQueryFilters: null,
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
      perPage: 10,
      totalRows: 0,
      sortBy: 'created_at',
      sortDesc: false,
    },
  }),

  methods: {

    doEmit(action, data) {
      this.$emit('table-event', { action, data } )
    },

    flagCount(item) {
      return item.stats?.flagCount || 0 
    },

    /*
    async renderFlag(s) {
      this.modalSelection = s // %TODO : clear on modal hide event
      this.isFlagModalVisible = true
    },
     */

    async getData() {
      let params = {
        page: this.tobj.currentPage,
        take: this.tobj.perPage,
        sortBy: this.tobj.sortBy,
        sortDir: this.tobj.sortDesc ? 'desc' : 'asc',
        ...this.encodedQueryFilters,
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

  watch: {
    encodedQueryFilters: {
      handler: function(v) {
        this.getData()
      },
      deep: true,
    }
  },

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
