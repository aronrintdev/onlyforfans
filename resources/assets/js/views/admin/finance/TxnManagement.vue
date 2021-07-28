<template>
  <div>
    <h1>Transaction Management</h1>
    <!--
    <b-row>
      <b-col lg="4">
        <Balance />
      </b-col>
      <b-col lg="4">
        <Statistics />
      </b-col>
    </b-row>
    -->
    <section v-if="summary">
      <div class="mt-3 px-5">
        <b-card-group deck class="mb-3">
          <b-card border-variant="success" header-text-variant="success" header="Sales" align="center">
            <b-card-text>
              {{ summary.sums.credits.sale.total | niceCurrency }}
              ( {{ summary.sums.credits.sale.count }} )
            </b-card-text>
          </b-card>
          <b-card border-variant="success" header-text-variant="success" header="Subscriptions" align="center">
            <b-card-text>
              {{ summary.sums.credits.subscription.total | niceCurrency }}
              ( {{ summary.sums.credits.subscription.count }} )
            </b-card-text>
          </b-card>
          <b-card border-variant="success" header-text-variant="success" header="Tips" align="center">
            <b-card-text>
              {{ summary.sums.credits.tip.total | niceCurrency }}
              ( {{ summary.sums.credits.tip.count }} )
            </b-card-text>
          </b-card>
        </b-card-group>
        <b-card-group deck class="mb-3">
          <b-card header="Fees" border-variant="danger" header-text-variant="danger" align="center">
            <b-card-text>
              {{ summary.sums.debits.fee.total | niceCurrency }}
              ( {{ summary.sums.debits.fee.count }} )
            </b-card-text>
          </b-card>
          <b-card header="Refunds" border-variant="danger" header-text-variant="danger" align="center">
            <b-card-text>
              {{ summary.sums.debits.refund.total | niceCurrency }}
              ( {{ summary.sums.debits.refund.count }} )
            </b-card-text>
          </b-card>
          <b-card header="Chargebacks" border-variant="danger" header-text-variant="danger" align="center">
            <b-card-text>
              {{ summary.sums.debits.chargeback.total | niceCurrency }}
              ( {{ summary.sums.debits.chargeback.count }} )
            </b-card-text>
          </b-card>
        </b-card-group>
      </div>
    </section>

    <section class="crate-filters mb-3 d-flex">
      <!-- filters -->
      <div class="box-filter p-3">
        <h6>Txn Type</h6>
        <b-button v-for="(f,idx) in txnFilters.txn_type" :key="idx" @click="toggleFilter('txn_type', f)" :variant="f.is_active ? 'primary' : 'outline-primary'" class="mr-3">{{ f.label }}</b-button>
      </div>
      <div class="box-filter p-3 ml-5">
        <h6>Resource Type</h6>
        <b-button v-for="(f,idx) in txnFilters.resource_type" :key="idx" @click="toggleFilter('resource_type', f)" :variant="f.is_active ? 'primary' : 'outline-primary'" class="mr-3">{{ f.label }}</b-button>
      </div>
    </section>

    <b-card>
      <b-card-title :title="`Transactions (${tobj.totalRows})`" />
      <b-pagination
        v-model="tobj.currentPage"
        :total-rows="tobj.totalRows"
        :per-page="tobj.perPage"
        v-on:page-click="pageClickHandler"
        aria-controls="txns-table"
      ></b-pagination>

      <b-table hover 
        id="txns-table"
        :items="tobj.data"
        :fields="fields"
        :current-page="tobj.currentPage"
        small
      >
        <template #cell(id)="data">
          <span class="">{{ data.item.id | niceGuid }}</span>
        </template>
        <template #cell(account_id)="data">
          <span class="">{{ data.item.account_id | niceGuid }}</span>
        </template>
        <template #cell(debit_amount)="data">
          <span class="">{{ data.item.debit_amount | niceCurrency }}</span> <!-- assumes US %FIXME -->
        </template>
        <template #cell(credit_amount)="data">
          <span class="">{{ data.item.credit_amount | niceCurrency }}</span> <!-- assumes US %FIXME -->
        </template>
        <template #cell(resource_id)="data">
          <span class="">{{ data.item.resource_id | niceGuid }}</span>
        </template>
        <template #cell(settled_at)="data">
          <span class="">{{ data.item.settled_at | niceDate }}</span>
        </template>
        <template #cell(created_at)="data">
          <span class="">{{ data.item.created_at | niceDate }}</span>
        </template>
      </b-table>
    </b-card>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
//import TransactionsTable from '@components/statements/transactions/Table'

export default {
  name: 'TxnManagement',

  props: {},

  computed: {
  },

  data: () => ({
    //items: [],

    summary: null,

    txnFilters: {
      txn_type: [
        { key: 'payment', label: 'Payment', is_active: false, }, 
        { key: 'sale', label: 'Sale', is_active: false, },
        { key: 'payout', label: 'Payout', is_active: false, },
        { key: 'subscription', label: 'Subscription', is_active: false, },
        { key: 'fee', label: 'Fee', is_active: false, },
        { key: 'chargeback', label: 'Chargeback', is_active: false, },
        { key: 'refund', label: 'Refund', is_active: false, },
      ],
      resource_type: [
        { key: 'timelines', label: 'Timeline', is_active: false, },
        { key: 'posts', label: 'Post', is_active: false, },
        { key: 'none', label: 'None', is_active: false, },
      ],

    },

    tobj: { // table object
      data: [],
      currentPage: 1,
      perPage: 20,
      totalRows: null,
      isBusy: false, // to prevent multiple api calls per page event, etc
    },

    fields: [ // table fields
      { key: 'id', label: 'ID' },
      { key: 'account_id', label: 'Account' },
      { key: 'credit_amount', label: 'Credit' },
      { key: 'debit_amount', label: 'Debit' },
      { key: 'currency', label: 'Currency' },
      { key: 'type', label: 'Type' },
      //{ key: 'description', label: 'Description' },
      { key: 'resource_type', label: 'Resource Type' },
      { key: 'resource_id', label: 'Resource ID' },
      { key: 'settled_at', label: 'Settled' },
      { key: 'purchaser.username', label: 'Purchaser' },
      { key: 'created_at', label: 'Created' },
    ],
  }),

  methods: {
    pageClickHandler(e, page) {
      this.tobj.currentPage = page
      this.getTransactions()
    },

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
      console.log('encodeQueryFilters', { params, })
      return params
    },

    async getTransactions() {
      //let params = `?page=${this.tobj.currentPage}&take=${this.tobj.perPage}`
      let params = {
        page: this.tobj.currentPage,
        take: this.tobj.perPage,
        ...this.encodeQueryFilters(),
      }
      console.log('getTransactions', { params })
      try {
        this.tobj.isBusy = true
        const response = await axios.get( this.$apiRoute('financial.transactions.index'), { params } )
        this.tobj.totalRows = response.data.meta.total // %NOTE: coupled to table
        this.tobj.data = response.data.data
        this.tobj.isBusy = false
        //return response.data.data // %NOTE: coupled to table
      } catch (e) {
        this.tobj.isBusy = false
        //throw e
        return []
      }
    },

    async getSummary() {
      try {
        const response = await axios.get( this.$apiRoute('financial.transactions.summary'))
        this.summary = response.data
      } catch (e) {
        throw e
        return []
      }
    },

    toggleFilter(filterType, fObj) {
      console.log('toggleFilter()', {
        filterType, fObj
      })
      //const tmp = this.selectedFilters.includes(f) ? this.selectedFilters.filter(i => i !== f) : [ ...this.selectedFilters, f ]
      //this.selectedFilters = tmp
      //this.txnFilters[filterType][fObj.key].is_active = !fObj.is_active
      // %TODO: clean up this line
      this.txnFilters[ filterType ][ this.txnFilters[filterType].findIndex(iter => iter.key===fObj.key) ] = { ...fObj, is_active: !fObj.is_active }

      this.tobj.currentPage = 1
      this.getTransactions()
    },

  },

  watch: {
    /*
    'tobj.currentPage': function(newVal, oldVal) {
    console.log('watch - currentPage()', {
    newVal, oldVal,
    })
    if ( newVal !== oldVal ) {
    this.getTransactions()
    }
    },
     */
    /*
    selectedFilters(newVal, oldVal) {
    console.log('watch - txnFilters()')
    if ( newVal !== oldVal ) {
    this.tobj.currentPage = 1
    this.getTransactions()
    }
    },
     */
  },

  created() {
    this.getTransactions()
    this.getSummary()
    //this.$nextTick(() => {
    //this.selectedFilter = 'all'
    //})
  },

  mounted() {
    //this.tobj.isBusy = false
  },

  components: {
    //TransactionsTable,
  },

}
</script>

<style lang="scss" scoped>
::v-deep button.btn-link:focus, 
::v-deep button.btn-link:active {
  outline: none !important;
  box-shadow: none !important;
}
.crate-filters {
  .box-filter {
    border: solid 1px #353535;
  }
}
</style>
