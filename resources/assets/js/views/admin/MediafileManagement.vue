<template>
  <div v-if="!isLoading">
    <h1>File/Media Management</h1>

    <b-pagination
      v-model="tobj.currentPage"
      :total-rows="tobj.totalRows"
      :per-page="tobj.perPage"
      v-on:page-click="pageClickHandler"
      aria-controls="mediafiles-table"
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
      <template #cell(id)="data"><span @click="renderShow(data.item)" class="clickable">{{ data.item.id | niceGuid }}</span></template>
      <template #cell(has_blur)="data"><span class="">{{ data.item.has_blur | niceBool }}</span></template>
      <template #cell(has_mid)="data"><span class="">{{ data.item.has_mid | niceBool }}</span></template>
      <template #cell(has_thumb)="data"><span class="">{{ data.item.has_thumb | niceBool }}</span></template>
      <template #cell(owner_id)="data"><span class="">{{ data.item.owner_id | niceGuid }}</span></template>
      <template #cell(created_at)="data"><span class="">{{ data.item.created_at | niceDate }}</span></template>
      <template #cell(ctrls)="data">
        <span @click="renderFlag(data.item)" class="">
          <fa-icon v-if="data.item.flag_count>0" :icon="['fas', 'flag']" class="clickable fa-sm text-danger" />
          <fa-icon v-else :icon="['far', 'flag']" class="clickable fa-sm" />
        </span>
        <span class="">
          <fa-icon :icon="['fas', 'ellipsis-h']" class="clickable fa-sm" />
        </span>
    </template>
    </b-table>

    <!-- Show Modal -->
    <b-modal v-model="isShowModalVisible" id="modal-show-mediafile" size="lg" title="File Details" body-class="OFF-p-0">
      <section v-if="modalSelection" class="OFF-d-flex">
        <div class="box-image-preview text-center">
          <b-img fluid :src="modalSelection.render_urls.full"></b-img>
        </div>
        <div class="box-details">
          <pre>{{ JSON.stringify(modalSelection, null, 2) }}</pre>
        </div>
      </section>
      <template #modal-footer>
        <b-button variant="warning" @click="isShowModalVisible=false">Cancel</b-button>
        <!--
        <b-button variant="primary" @click="storeStory()">Save</b-button>
        -->
      </template>
    </b-modal>

    <!-- Flag Modal -->
    <b-modal v-model="isFlagModalVisible" id="modal-manage-flagged-content" size="lg" title="Flagged Content Management" body-class="OFF-p-0">
      <section v-if="modalSelection" class="OFF-d-flex">
        <div class="box-image-preview text-center">
          <b-img fluid :src="modalSelection.render_urls.full"></b-img>
        </div>
        <div class="box-details">
          <p>Flag Count: {{ 6 || modalSelection.flag_count }}</p>
          <!--
          <p># Pending: 3</p>
          <p># In Review: 2</p>
          <p># Confirmed: 1</p>
          <p># Discarded: 0</p>
          -->
        </div>
      </section>
      <template #modal-footer>
        <b-button variant="danger">Confirm Flags & Remove Content</b-button>
        <b-button variant="success">Discard Flags</b-button>
        <b-button variant="warning" @click="isFlagModalVisible=false">Cancel</b-button>
        <!--
        <b-button variant="primary" @click="storeStory()">Save</b-button>
        -->
      </template>
    </b-modal>

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

export default {
  name: 'MediafileManagement',

  props: {},

  computed: {
    isLoading() {
      return false
    },
  },

  data: () => ({
    fields: [
      { key: 'id', label: 'ID', sortable: false, },
      //{ key: 'slug', label: 'Slug', sortable: false, },
      { key: 'owner_id', label: 'Owner', sortable: false, },
      //{ key: 'filepath', label: 'Path', sortable: false, },
      { key: 'mimetype', label: 'Mime Type', sortable: false, },
      { key: 'orig_ext', label: 'Orig. Ext', sortable: false, },
      { key: 'orig_filename', label: 'Orig. File', sortable: false, },
      { key: 'orig_size', label: 'Orig. Size', sortable: false, },
      //{ key: 'basename', label: 'Base Name', sortable: false, },
      { key: 'has_blur', label: 'Blur?', sortable: false, },
      { key: 'has_mid', label: 'Mid?', sortable: false, },
      { key: 'has_thumb', label: 'Thumb?', sortable: false, },
      { key: 'created_at', label: 'Created', sortable: true, },
      { key: 'ctrls', label: '', sortable: false, },
    ],


    tobj: { // table object
      data: [],
      currentPage: 1,
      perPage: 20,
      totalRows: 0,
      sortBy: 'created_at',
      sortDesc: false,
    },

    isShowModalVisible: false,
    isFlagModalVisible: false,
    modalSelection: null,
  }),

  methods: {

    async renderShow(s) {
      this.modalSelection = s // %TODO : clear on modal hide event
      this.isShowModalVisible = true
    },
    async renderFlag(s) {
      this.modalSelection = s // %TODO : clear on modal hide event
      this.isFlagModalVisible = true
    },

    async getData() {
      let params = {
        page: this.tobj.currentPage,
        take: this.tobj.perPage,
        sortBy: this.tobj.sortBy,
        sortDir: this.tobj.sortDesc ? 'desc' : 'asc',
        //...this.encodeQueryFilters(),
      }
      console.log('getTransactions', { params })
      try {
        const response = await axios.get( this.$apiRoute('diskmediafiles.index'), { params } )
        this.tobj.totalRows = response.data.meta.total // %NOTE: coupled to table
        this.tobj.data = response.data.data
      } catch (e) {
        //throw e
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

    /*
    encodeQueryFilters() {
      let params = {
        type: [], // txn_type
        resource_type: [],
      }
      for ( let s of this.txnFilters.txn_type ) {
        if ( s.is_active ) {
          params.type.push(s.key)
        }
      }
      for ( let s of this.txnFilters.resource_type ) {
        if ( s.is_active ) {
          params.resource_type.push(s.key)
        }
      }
      return params
    },
     */
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
