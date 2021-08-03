<template>
  <div>
    <AdminTable :fields="fields" indexRouteName="contentflags.index" @table-event=handleTableEvent />

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
      { key: 'cfstatus', label: 'Status', sortable: true, },
      { key: 'flaggable_type', label: 'Flagged Type', sortable: true, },
      { key: 'flaggable_id', label: 'Flagged ID', sortable: false, },
      { key: 'notes', label: 'Notes', sortable: false, },
      { key: 'created_at', label: 'Created', sortable: true, formatter: v => Vue.options.filters.niceDate(v) },
      { key: 'ctrls', label: '', sortable: false, },
    ],
    isShowModalVisible: false,
    modalSelection: null,
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
  },

  watchers: {},

  created() {},

  components: {
    AdminTable,
  },

  name: 'ListContentflag',
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
