<template>
  <div>
    <section class="crate-filters mb-3 d-flex">
      <!-- filters -->
      <div class="box-filter p-3">
        <h6>Boolean</h6>
        <b-button v-for="(f,idx) in postFilters.booleans" :key="idx" @click="toggleFilter('booleans', f)" :variant="f.is_active ? 'primary' : 'outline-primary'" class="mr-3">{{ f.label }}</b-button>
      </div>
      <div class="box-filter p-3">
        <h6>Search</h6>
        <b-form-input v-model="postFilters.query" placeholder="Enter search text"></b-form-input>
      </div>
    </section>

    <AdminTable 
      :fields="fields" 
      :tblFilters="postFilters" 
      indexRouteName="posts.index" 
      @table-event=handleTableEvent 
      :encodedQueryFilters="encodedQueryFilters"
    />

    <!-- Show Modal -->
    <b-modal v-model="isShowModalVisible" id="modal-show-mediafile" size="lg" title="Post Details" body-class="OFF-p-0">
      <section v-if="modalSelection" class="OFF-d-flex">
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
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import AdminTable from '@views/admin/components/common/AdminTable'

export default {
  props: {},

  computed: { },

  data: () => ({
    fields: [
      { key: 'id', label: 'ID', formatter: (v, k, i) => Vue.options.filters.niceGuid(v) },
      { key: 'type', label: 'Type', sortable: true, },
      { key: 'price', label: 'Price', sortable: true, formatter: v => Vue.options.filters.niceCurrency(v) },
      { key: 'description', label: 'Content', tdClass: 'tag-desc', },
      { key: 'created_at', label: 'Created', sortable: true, formatter: v => Vue.options.filters.niceDate(v, true) },
      { key: 'ctrls', label: '', sortable: false, },
    ],
    isShowModalVisible: false,
    modalSelection: null,

    postFilters: {
      booleans: [
        { key: 'is_flagged', label: 'Reported', is_active: false, }, 
      ],
      query: '',
      start_date: null,
      end_date: null,
    },

    encodedQueryFilters: {
      //is_flagged: false,
    },
  }),

  methods: { 

    handleTableEvent(payload) {
      console.log('handleTableEvent()', {
        payload,
      })
      switch (payload.action) {
        case 'render-show':
          this.renderModal('show', payload.data)
          break
      }
    },

    async renderModal(modal, s) {
      this.modalSelection = s
      switch (modal) {
        case 'show':
          this.isShowModalVisible = true
          break
      }
    },

    async hideModal(modal) {
      this.modalSelection = null
      switch (modal) {
        case 'show':
          this.isShowModalVisible = false
          break
      }
    },

    toggleFilter(filterGroup, fObj) {
      console.log('toggleFilter().1', { filterGroup, fObj })
      if ( !Array.isArray(this.postFilters[filterGroup]) ) {
        return
      }
      const _pop = { ...this.postFilters } // make a copy
      const _idx = _pop[filterGroup].findIndex(iter => iter.key===fObj.key)
      if ( _idx >= 0 ) {
        console.log('updated...')
        _pop[ filterGroup ][_idx] = { ...fObj, is_active: !fObj.is_active }
        this.postFilters = _pop
        this.encodeQueryFilters()
      }
    },

    encodeQueryFilters() {
      const filters = this.postFilters
      let params = {
        is_flagged: 0,
        //resource_type: [],
      }
      for ( let s of filters.booleans ) {
        switch (s.key) {
          case 'is_flagged':
            Vue.set(this.encodedQueryFilters, 'is_flagged', s.is_active?1:0)
            break
        }
      }
    },

  },

  watch: {
    'postFilters.query': function (n, o) {
      if ( (typeof n !== 'string') || (n.length < 3) || (n === o) ) {
        return
      }
      this.encodeQueryFilters()
    },
  },

  created() {},

  components: {
    AdminTable,
  },

  name: 'ListPosts',
}
</script>

<style lang="scss" scoped>
::v-deep td.tag-desc {
  max-width: 12rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
