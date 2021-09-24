<template>
  <div>

    <AdminTable
      ref="table"
      :fields="fields"
      :tblFilters="userFilters"
      indexRouteName="admin.users.index"
      @table-event="handleTableEvent"
      :encodedQueryFilters="encodedQueryFilters"
      :busy="busy"
    >
      <template #cell(timeline)="data">
        <a target="_blank" :href="`/${data.value}`">
          <span v-if="data.value" class="ml-2">@{{ data.value }}</span>
        </a>
      </template>
      <template #cell(email_verified_at)="data">
        <span>
          <BoolBadgeDisplay :value="!!data.value" />
          <span v-if="data.value" class="ml-2"> @ {{ data.value | niceDate }}</span>
        </span>
      </template>
      <template #cell(is_verified)="data">
        <span>
          <BoolBadgeDisplay :value="!!data.value" />
        </span>
      </template>
      <template #cell(payments_disabled)="data">
        <span class="text-nowrap">
          <b-form-checkbox
            button-variant="success"
            :checked="data.value"
            switch
            :disabled="data.item.busy === 'payments_disabled'"
            @input="(value) => transactionEnabledUpdate(value, data)"
          >
            <fa-icon v-if="data.item.busy === 'payments_disabled'" icon="spinner" spin fixed-width />
          </b-form-checkbox>
        </span>
      </template>
    </AdminTable>

    <!-- Ellipsis Modal -->
    <b-modal v-model="isEllipsisModalVisible" id="modal-ellipsis" size="xl" title="User Details" body-class="">
      <section>
        <!-- <pre>{{ JSON.stringify(this.modalSelection, null, 2) }}</pre> -->
        <table v-if="this.modalSelection !== null" class="table">
          <tr>
            <th>Username</th>
            <td>{{this.modalSelection.username}}</td>
          </tr>
          <tr>
            <th>GUID</th>
            <td>{{this.modalSelection.id}}</td>
          </tr>
          <tr v-for="item in this.filteredModalSelection" :key="item.key" >
            <th>{{ item.key }}</th>
            <td>{{ item.value }}</td>
          </tr>
          <tr><th><div class="h3 mb-0">Settings</div></th></tr>
          <tr v-for="value, key in this.modalSelection.settings" :key="`settings-${key}`" >
            <th>{{ key }}</th>
            <td>{{ value }}</td>
          </tr>
          <tr><th span="2"><div class="h4 mb-0">Settings Custom Attributes</div></th></tr>
          <tr v-for="value, key in this.modalSelection.settings.cattrs" :key="`settings-cattrs-${key}`" >
            <th>{{ key }}</th>
            <td>{{ value }}</td>
          </tr>
        </table>
      </section>
      <template #modal-footer>
        <section class="d-flex align-items-center">
          <p v-if="renderError" class="mb-0 text-danger">There was a problem with your request</p>
          <b-button @click="isEllipsisModalVisible=false" class="ml-3" variant="secondary">Cancel</b-button>
          <b-button @click="loginAsUser()" class="ml-3" variant="danger">Login As User</b-button>
        </section>
      </template>
    </b-modal>

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import AdminTable from '@views/admin/components/common/AdminTable'
import BoolBadgeDisplay from '@views/admin/components/common/BoolBadgeDisplay'

export default {
  name: 'ListUsers',

  props: {},

  computed: {
    filteredModalSelection() {
      const modalSelection = typeof this.modalSelection === 'object' ? _.map(this.modalSelection, (value, key) => ({ value, key })) : []
      return _.filter(modalSelection || {}, v => (typeof v.value !== 'object'))
    },
  },

  data: () => ({
    fields: [
      { key: 'id', label: 'ID', formatter: (v, k, i) => Vue.options.filters.niceGuid(v) },
      { key: 'email', label: 'Email', sortable: true, },
      { key: 'username', label: 'Username', sortable: true, },
      { key: 'timeline', label: 'Timeline Slug', sortable: true, formatter: (v, k, i) => v ? v.slug : '' },
      // { key: 'firstname', label: 'First', sortable: true, },
      // { key: 'lastname', label: 'Last', sortable: true, },
      { key: 'email_verified_at', label: 'Email Verified?', sortable: true,
        // formatter: (v, k, i) => {
        //   if ( v === null ) {
        //     return 'N'
        //   } else {
        //     return 'Y @ '+Vue.options.filters.niceDate(v, true)
        //   }
        // }
      },
      { key: 'is_verified', label: 'ID Verified?', sortable: true,
        // formatter: (v, k, i) => Vue.options.filters.niceBool(v)
      },
      {
        key: 'payments_disabled',
        label: 'Transactions Enabled',
        formatter: (v, k, i) => {
          return !v
        },
      },
      { key: 'last_logged', label: 'Last Login', sortable: true, formatter: (v, k, i) => Vue.options.filters.niceDate(v, true) },
      { key: 'created_at', label: 'Joined', sortable: true, formatter: (v, k, i) => Vue.options.filters.niceDate(v, true) },
      { key: 'ctrls', label: '', sortable: false, },
    ],

    isShowModalVisible: false,
    isEllipsisModalVisible: false,
    modalSelection: null,
    isProcessing: false,
    renderError: false,

    userFilters: {
      booleans: [
        { key: 'is_verified', label: 'Verified', is_active: false, }, 
      ],
      start_date: null,
      end_date: null,
    },

    encodedQueryFilters: {},

    busy: false, // Doing an update operation
  }),

  methods: {

    transactionEnabledUpdate(value, slotData) {
      if (value === slotData.value) {
        return
      }
      console.log({value, slotData})
      this.setItemBusy(slotData)
      this.axios.post(this.$apiRoute('admin.user.disable-payment', { user: slotData.item }), { disable: !value })
      .then(response => { this.updateItem(slotData, response.data.data) })
      .catch(error => {
        console.error(error)
        alert('there was an error, see console')}
      )
      .finally(() => {
        this.unsetItemBusy(slotData)
      })
    },

    setItemBusy(slotData) {
      this.$refs['table'].setItemBusy(slotData)
    },

    unsetItemBusy(slotData) {
      this.$refs['table'].unsetItemBusy(slotData)
    },

    updateItem(slotData, newValue) {
      console.log('updateItem', {slotData, newValue})
      this.$refs['table'].updateItem(slotData, newValue)
    },

    async loginAsUser() {
      this.renderError = false
      try {
        await axios.post( this.$apiRoute('users.loginAsUser', this.modalSelection.id) )
        this.hideModal()
        window.location.href = '/'
      } catch (err) {
        this.$log.error(err)
        this.renderError = true
      }
    },

    handleTableEvent(payload) {
      console.log('handleTableEvent()', {
        payload,
      })
      switch (payload.action) {
        case 'render-show':
          this.renderModal('show', payload.data)
          break
        case 'render-ellipsis':
          this.renderModal('ellipsis', payload.data)
          break
      }
    },

    async renderModal(modal, s) {
      this.modalSelection = s
      switch (modal) {
        case 'show':
          this.isShowModalVisible = true
          break
        case 'ellipsis':
          const response = await axios.get( this.$apiRoute('users.show', this.modalSelection.id) )
          this.modalSelection = response.data.data
          this.isEllipsisModalVisible = true
          break
      }
    },

    async hideModal() {
      this.renderError = false
      this.modalSelection = null
      this.isShowModalVisible = false
      this.isEllipsisModalVisible = false
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

  created() { },

  components: {
    AdminTable,
    BoolBadgeDisplay,
  },
}
</script>

<style lang="scss" scoped>
</style>
