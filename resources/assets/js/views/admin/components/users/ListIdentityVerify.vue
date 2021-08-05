<template>
  <div>
    <AdminTable 
      :fields="fields" 
      :tblFilters="tblFilters" 
      indexRouteName="verifyrequests.index" 
      @table-event=handleTableEvent 
      :encodedQueryFilters="encodedQueryFilters"
    />

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import AdminTable from '@views/admin/components/common/AdminTable'

export default {
  name: 'ListIdentityVerify',

  props: {},

  computed: {
  },

  data: () => ({
    fields: [
      { key: 'guid', label: 'GUID', formatter: (v, k, i) => Vue.options.filters.niceGuid(v) },
      { key: 'requester_username', label: 'Requested By', sortable: true, },
      { key: 'vservice', label: 'Service', sortable: true, },
      { key: 'service_guid', label: 'Service ID', formatter: (v, k, i) => Vue.options.filters.niceGuid(v) },
      { key: 'vstatus', label: 'Status', sortable: true, },
      { key: 'last_checked_at', label: 'Last Checked', sortable: true, formatter: (v, k, i) => Vue.options.filters.niceDate(v, true) },
    ],

    isShowModalVisible: false,
    modalSelection: null,

    tblFilters: {
      booleans: [
        //{ key: 'is_verified', label: 'Verified', is_active: false, }, 
      ],
      start_date: null,
      end_date: null,
    },

    encodedQueryFilters: {},
  }),

  methods: {
    handleTableEvent(payload) {
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
        //is_flagged: 0,
        //resource_type: [],
      }
      for ( let s of filters.booleans ) {
        switch (s.key) {
          //case 'is_flagged':
          //Vue.set(this.encodedQueryFilters, 'is_flagged', s.is_active?1:0)
          //break
        }
      }
    },
  },

  watchers: {},

  created() { },

  components: {
    AdminTable,
  },
}
</script>

<style lang="scss" scoped>
</style>
