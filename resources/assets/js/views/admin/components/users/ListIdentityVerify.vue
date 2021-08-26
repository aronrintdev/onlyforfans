<template>
  <div>
    <AdminTable 
      :fields="fields" 
      :tblFilters="tblFilters" 
      indexRouteName="verifyrequests.index" 
      @table-event=handleTableEvent 
      :encodedQueryFilters="encodedQueryFilters"
    />

    <!-- Ellipsis Modal -->
    <b-modal v-model="isEllipsisModalVisible" id="modal-ellipsis" size="xl" title="Identity Verification Details" body-class="">
      <section>
        <!-- <pre>{{ JSON.stringify(this.modalSelection, null, 2) }}</pre> -->
        <table v-if="this.modalSelection!==null" class="table">
          <tr>
            <th>Requester</th>
            <td>{{this.modalSelection.requester_username}}</td>
          </tr>
          <tr>
            <th>GUID</th>
            <td>{{this.modalSelection.guid}}</td>
          </tr>
          <tr>
            <th>Service GUID</th>
            <td>{{this.modalSelection.service_guid}}</td>
          </tr>
          <tr>
            <th>Status</th>
            <td><span :class="isVerified ? 'text-success': ''">{{this.modalSelection.vstatus}}</span></td>
          </tr>
          <tr>
            <th>Last Checked</th>
            <td>{{this.modalSelection.last_checked_at}}</td>
          </tr>
          <tr>
            <th>Initial Request</th>
            <td><pre>{{ this.parseCattrs('req_request') }}</pre></td>
          </tr>
          <tr>
            <th>Initial Response</th>
            <td><pre>{{ this.parseCattrs('req_response') }}</pre></td>
          </tr>
          <tr>
            <th>Status Responses</th>
            <td><pre>{{ this.parseCattrs('status_response') }}</pre></td>
          </tr>
        </table>
      </section>
      <template #modal-footer>
        <section class="d-flex align-items-center">
          <p v-if="renderError" class="mb-0 text-danger">There was a problem with your request</p>
          <b-button @click="isEllipsisModalVisible=false" class="ml-3" variant="secondary">Cancel</b-button>
          <b-button @click="checkStatus()" class="ml-3" variant="success" :disabled="isProcessing">
            <span v-if="!isProcessing">Check Status</span>
            <span v-else>Checking... <fa-icon class="input-spinner" icon="spinner" spin /></span>
          </b-button>
        </section>
      </template>
    </b-modal>

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
    isVerified() {
      return ( (this.modalSelection?.vstatus || null) === 'verified' ) 
    },
  },

  data: () => ({
    fields: [
      { key: 'guid', label: 'GUID', formatter: (v, k, i) => Vue.options.filters.niceGuid(v) },
      { key: 'requester_username', label: 'Requested By', sortable: true, },
      { key: 'vservice', label: 'Service', sortable: true, },
      { key: 'service_guid', label: 'Service ID', formatter: (v, k, i) => Vue.options.filters.niceGuid(v) },
      { key: 'vstatus', label: 'Status', sortable: true, },
      { key: 'last_checked_at', label: 'Last Checked', sortable: true, formatter: (v, k, i) => Vue.options.filters.niceDate(v, true) },
      { key: 'ctrls', label: '', sortable: false, },
    ],

    isShowModalVisible: false,
    isEllipsisModalVisible: false,
    modalSelection: null,
    isProcessing: false,
    renderError: false,

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
        case 'render-ellipsis':
          this.renderModal('ellipsis', payload.data)
          break
      }
    },

    async checkStatus() {
      this.renderError = false
      this.isProcessing = true
      try { 
        const response = await axios.get( this.$apiRoute('verifyrequests.checkStatus', this.modalSelection.id) )
        //console.log('response', { response } )
        this.modalSelection = response.data.data
      } catch (err) {
        this.renderError = true
      }
      this.isProcessing = false
    },

    async renderModal(modal, s) {
      this.modalSelection = s
      switch (modal) {
        case 'show':
          this.isShowModalVisible = true
          break
        case 'ellipsis':
          const response = await axios.get( this.$apiRoute('verifyrequests.show', this.modalSelection.id) )
          this.modalSelection = response.data.data
          this.isEllipsisModalVisible = true
          break
      }
    },

    async hideModal(modal) {
      this.renderError = false
      this.modalSelection = null
      switch (modal) {
        case 'show':
          this.isShowModalVisible = false
          break
        case 'ellipsis':
          this.isEllipsisModalVisible = false
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

    parseCattrs(ctype) {
      let tmp = null
      switch(ctype) {
        case 'req_request':
          tmp = this.modalSelection?.cattrs?.vrequest_raw_request || null
          break
        case 'req_response':
          tmp = this.modalSelection?.cattrs?.vrequest_raw_response || null
          break
        case 'status_response':
          tmp = this.modalSelection?.cattrs?.check_verify_status_response || null
          break
      }
      if ( tmp!==null ) {
        if ( Array.isArray(tmp) ) {
          tmp = tmp.map( t => {
            delete t.deviceId
            delete t.latitude
            delete t.liveness
            delete t.longitude
            delete t.scanImage
            delete t.userAgent
            delete t.barcodeMap
            delete t.selfieImage
            delete t.documentType
            delete t.validatedTime
            delete t.dobScore
            delete t.nameScore
            delete t.riskFactor
            delete t.countryCode
            delete t.dateOfBirth
            delete t.faceMatches
            delete t.documentScore
            delete t.forgeryResult
            delete t.forgeryStatus
            return t
          })
        } else {
          delete tmp.qrCode
          delete tmp.barcodeType
          delete tmp.callbackURL
          delete tmp.redirectURL
          delete tmp.skipBarcode
          delete tmp.documentType
          delete tmp.skipLiveness
        }
        return JSON.stringify(tmp, null, 2)
      } else {
        return null
      }
    },

  },

  created() { },

  components: {
    AdminTable,
  },
}
</script>

<style lang="scss" scoped>
</style>
