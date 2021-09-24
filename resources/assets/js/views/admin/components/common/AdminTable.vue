<template>
  <div v-if="!isLoading" class="my-3">

    <section class="superbox-paging mb-3 d-flex align-items-center OFF-justify-content-between">
      <b-pagination
        v-model="tobj.currentPage"
        :total-rows="tobj.totalRows"
        :per-page="tobj.perPage"
        v-on:page-click="pageClickHandler"
        aria-controls="admin-index-table"
      ></b-pagination>
      <div class="ml-5">({{ tobj.totalRows }})</div>
    </section>

    <b-table hover 
      :items="tobj.data"
      :fields="fields"
      :current-page="tobj.currentPage"
      :sort-by="tobj.sortBy"
      :sort-desc="tobj.sortDesc"
      :no-local-sorting="true"
      :busy="busy || loading"
      :selectable="selectable"
      @sort-changed="sortHandler"
      sort-icon-left
      small
    >
      <template #cell(id)="data">
        <span>
          <span
            :title="data.item.id"
            v-b-tooltip.hover
            class="clickable text-monospace"
            @click="doEmit('render-show', data.item)"
          >
            {{ data.item.id | niceGuid }}
          </span>
          <b-btn
            variant="link"
            class="cursor-pointer"
            title="copy"
            size="sm"
            v-clipboard:copy="data.item.id"
            v-clipboard:success="onCopy"
            v-clipboard:error="onCopyError"
          >
            <fa-icon icon="copy" fixed-width />
          </b-btn>
        </span>

      </template>
      <template #cell(ctrls)="data">
        <span @click="doEmit('render-ellipsis', data.item)">
          <fa-icon :icon="['fas', 'ellipsis-h']" class="clickable fa-sm" />
        </span>
        <span @click="doEmit('render-flag', data.item)" class="">
          <fa-icon v-if="flagCount(data.item)>0" :icon="['fas', 'flag']" class="clickable fa-sm text-danger" />
          <!--
          <fa-icon v-else :icon="['far', 'flag']" class="clickable fa-sm" />
          -->
        </span>
      </template>

      <!-- Passes scoped slots down to b-table -->
      <template v-for="(_, name) in $scopedSlots" :slot="name" slot-scope="slotData"><slot :name="name" v-bind="slotData" /></template>
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
/**
 * resources/assets/js/views/admin/components/common/AdminTable.vue
 */
import Vue from 'vue'
import Vuex from 'vuex'
import _ from 'lodash'

export default {
  name: 'AdminTable',

  props: {
    fields: { type: Array, default: [] },
    tblFilters: null,
    indexRouteName: { type: String, default: null },
    encodedQueryFilters: null,
    busy: { type: Boolean, default: false },
    selectable: { type: Boolean, default: false },
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
      perPage: 50,
      totalRows: 0,
      sortBy: 'created_at',
      sortDesc: false,
    },

    loading: false,
  }),

  methods: {

    onCopy(e) {
      console.log('onCopy', {e})
    },

    onCopyError(e) {
      console.error('onCopyError', {e})
    },

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

    async _getData() {
      let params = {
        page: this.tobj.currentPage,
        take: this.tobj.perPage,
        sortBy: this.tobj.sortBy,
        sortDir: this.tobj.sortDesc ? 'desc' : 'asc',
        ...this.encodedQueryFilters,
      }
      console.log('_getData', { params })
      this.loading = true
      try {
        const response = await axios.get( this.$apiRoute(this.indexRouteName), { params } )
        this.tobj.totalRows = response.data.meta.total // %NOTE: coupled to table
        this.tobj.data = response.data.data
      } catch (e) {
        throw e
        return []
      }
      this.loading = false
    },

    updateItem(slotData, newValue) {
      console.log('updateItem', {slotData, newValue})
      this.$set(this.tobj.data, slotData.index, newValue)
    },

    setItemBusy(slotData) {
      this.$set(this.tobj.data, slotData.index, { ...this.tobj.data[slotData.index], busy: slotData.field.key })
    },

    unsetItemBusy(slotData) {
      this.$set(this.tobj.data, slotData.index, { ...this.tobj.data[slotData.index], busy: null })
    },

    pageClickHandler(e, page) {
      this.tobj.currentPage = page
      this.getData()
    },

    sortHandler(context) {
      //console.log('sortHandler', { sortBy: context.sortBy, sortDesc: context.sortDesc, })
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
    },
  },

  created() { 
    this.getData = _.debounce(this._getData, 500);
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
