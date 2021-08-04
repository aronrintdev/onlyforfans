<template>
  <div>
    <AdminTable 
      :fields="fields" 
      :tblFilters="userFilters" 
      indexRouteName="users.index" 
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
  name: 'UserManagement',

  props: {},

  computed: {
  },

  data: () => ({
    //items: [],
    //currentPage: 1,
    //rows: null,
    //perPage: 50,
    fields: [
      { key: 'id', label: 'ID', formatter: (v, k, i) => Vue.options.filters.niceGuid(v) },
      { key: 'email', label: 'Email', sortable: true, },
      { key: 'username', label: 'Username', sortable: true, },
      { key: 'firstname', label: 'First', sortable: true, },
      { key: 'lastname', label: 'Last', sortable: true, },
      { key: 'email_verified_at', label: 'Email Verified?', sortable: true, formatter: (v, k, i) => {
        if ( v === null ) {
          return 'N'
        } else {
          return 'Y @ '+Vue.options.filters.niceDate(v) 
        }
      }},
      { key: 'is_verified', label: 'ID Verified?', sortable: true, formatter: (v, k, i) => Vue.options.filters.niceBool(v) },
      { key: 'timeline', label: 'Timeline Slug', sortable: true, formatter: (v, k, i) => v.slug },
      { key: 'last_logged', label: 'Last Login', sortable: true, formatter: (v, k, i) => Vue.options.filters.niceDate(v) },
      { key: 'created_at', label: 'Joined', sortable: true, formatter: (v, k, i) => Vue.options.filters.niceDate(v) },
      { key: 'ctrls', label: '', sortable: false, },
    ],

    isShowModalVisible: false,
    modalSelection: null,

    userFilters: {
      booleans: [
        { key: 'is_verified', label: 'Verified', is_active: false, }, 
      ],
      start_date: null,
      end_date: null,
    },

    encodedQueryFilters: {},
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

  watchers: {},

  created() { },

  components: {
    AdminTable,
  },
}
</script>

<style lang="scss" scoped>
</style>
