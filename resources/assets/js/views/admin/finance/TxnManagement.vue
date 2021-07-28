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

    <section>
      <!-- Filters Dropdown -->
      <b-dropdown ref="filterControls" class="filter-controls" variant="link" size="sm" no-caret >
        <template #button-content shadow-none>
          <b-badge show class="alert-primary" :style="{ fontSize: '100%' }">
            <span class="mr-2">Filters</span> <fa-icon icon="filter" />
          </b-badge>
        </template>

        <b-dropdown-header>Txn Type </b-dropdown-header>

        <b-dropdown-item v-for="f in typeFilterOptions" :key="f.key" :active="f.key===selectedFilter" @click="toggleFilter(f.key)" >
          <!-- <fa-icon icon="thumbtack" class="mx-2" size="lg" fixed-width /> -->
          {{ f.label }}
        </b-dropdown-item>

        <b-dropdown-divider />

        <b-dropdown-header>Resource Type </b-dropdown-header>

        <b-dropdown-item v-for="f in resourceTypeFilterOptions" :key="f.key" :active="f.key===selectedFilter" @click="toggleFilter(f.key)" >
          <!-- <fa-icon icon="thumbtack" class="mx-2" size="lg" fixed-width /> -->
          {{ f.label }}
        </b-dropdown-item>

      </b-dropdown>

      <div>
        Active
        <ul>
          <li v-for="(f,idx) in selectedFilters" :key="idx">{{ f }}</li>
        </ul>
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
    typeFilterOptions() {
      return this.txnFilters.filter( f => f.field==='type' )
    },
    resourceTypeFilterOptions() {
      return this.txnFilters.filter( f => f.field ==='resource_type' )
    },
    encodedQueryFilters() {
      let params = {
        type: [],
        resource_type: [],
      }
      for ( let s of this.selectedFilters ) {
        const o = this.txnFilters.find( i => i.key === s )
        switch (o.field) {
          case 'resource_type':
            params.resource_type.push(s)
            break
          case 'type':
            params.['type'].push(s)
            break
        }
      }
      console.log('encodeQueryFilters', { params, })
      return params
    },
  },

  data: () => ({
    //items: [],

    summary: null,

    selectedFilter: null,
    selectedFilters: [], // %FIXME: needs to distinguish field

    txnFilters: [
          { field: 'type', key: 'payment', label: 'Payment', is_active: false, }, // txn type
          { field: 'type', key: 'sale', label: 'Sale', is_active: false, },
          { field: 'type', key: 'subscription', label: 'Subscription', is_active: false, },
          { field: 'type', key: 'fee', label: 'Fee', is_active: false, },
          { field: 'type', key: 'chargeback', label: 'Chargeback', is_active: false, },
          { field: 'type', key: 'refund', label: 'Refund', is_active: false, },

          { field: 'resource_type', key: 'timelines', label: 'Timeline', is_active: false, },
          { field: 'resource_type', key: 'post', label: 'Post', is_active: false, },
          { field: 'resource_type', key: 'none', label: 'None', is_active: false, },
    ],

    tobj: {
      data: [],
      currentPage: 1,
      perPage: 20,
      totalRows: null,
      isBusy: false, // to prevent multiple api calls per page event, etc
    },

    fields: [
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
    async getTransactions() {
      //let params = `?page=${this.tobj.currentPage}&take=${this.tobj.perPage}`
      let params = {
        page: this.tobj.currentPage,
        take: this.tobj.perPage,
        ...this.encodedQueryFilters,
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

    toggleFilter(f) {
      const tmp = this.selectedFilters.includes(f) ? this.selectedFilters.filter(i => i !== f) : [ ...this.selectedFilters, f ]
      this.selectedFilters = tmp
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
    selectedFilters(newVal, oldVal) {
      console.log('watch - txnFilters()')
      if ( newVal !== oldVal ) {
        this.tobj.currentPage = 1
        this.getTransactions()
      }
    },
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
</style>
