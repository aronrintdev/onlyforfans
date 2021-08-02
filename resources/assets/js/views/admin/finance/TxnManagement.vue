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
      <div class="box-filter p-3 ml-5">
        <h6>Date Range</h6>
        <div class="d-flex align-items-center">
          <b-form-datepicker v-model="txnFilters.start_date" :date-format-options="{ year:'numeric', month:'numeric', day:'numeric' }" class="mr-2"></b-form-datepicker>
          <div>
            ->
          </div>
          <b-form-datepicker v-model="txnFilters.end_date" :date-format-options="{ year:'numeric', month:'numeric', day:'numeric' }" class="ml-2"></b-form-datepicker>
        </div>
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
        :sort-by="tobj.sortBy"
        :sort-desc="tobj.sortDesc"
        :no-local-sorting="true"
        @sort-changed="sortHandler"
        sort-icon-left
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
import moment from 'moment'
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
        { key: 'tips', label: 'Tip', is_active: false, },
        { key: 'messages', label: 'Messages', is_active: false, },
        { key: 'none', label: 'None', is_active: false, },
      ],
      start_date: null,
      end_date: null,
    },

    tobj: { // table object
      data: [],
      currentPage: 1,
      perPage: 20,
      totalRows: null,
      isBusy: false, // to prevent multiple api calls per page event, etc
      sortBy: 'created_at',
      sortDesc: false,
    },

    fields: [ // table fields
      { key: 'id', label: 'ID', sortable: false, },
      { key: 'account_id', label: 'Account', sortable: false, },
      { key: 'credit_amount', label: 'Credit', sortable: true, },
      { key: 'debit_amount', label: 'Debit', sortable: true, },
      { key: 'currency', label: 'Currency', sortable: true, },
      { key: 'type', label: 'Type', sortable: true, },
      //{ key: 'description', label: 'Description', sortable: true, },
      { key: 'resource_type', label: 'Resource Type', sortable: true, },
      { key: 'resource_id', label: 'Resource ID', sortable: false, },
      { key: 'settled_at', label: 'Settled', sortable: true, },
      { key: 'purchaser.username', label: 'Purchaser', sortable: false, },
      { key: 'created_at', label: 'Created', sortable: true, },
    ],
  }),

  methods: {
    pageClickHandler(e, page) {
      this.tobj.currentPage = page
      this.getTransactions()
    },

    sortHandler(context) {
      console.log('sortHandler', {
        sortBy: context.sortBy,
        sortDesc: context.sortDesc,
      })
      this.tobj.sortBy = context.sortBy
      this.tobj.sortDesc = context.sortDesc
      this.tobj.currentPage = 1
      this.getTransactions()
    },

    async getTransactions() {
      //let params = `?page=${this.tobj.currentPage}&take=${this.tobj.perPage}`
      let params = {
        page: this.tobj.currentPage,
        take: this.tobj.perPage,
        sortBy: this.tobj.sortBy,
        sortDir: this.tobj.sortDesc ? 'desc' : 'asc',
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
      if ( !Array.isArray(this.txnFilters[filterType]) ) {
        return
      }
      // %TODO: clean up this line
      this.txnFilters[ filterType ][ this.txnFilters[filterType].findIndex(iter => iter.key===fObj.key) ] = { ...fObj, is_active: !fObj.is_active }

      this.tobj.currentPage = 1
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
      return params
    },

  },

  watch: {
  },

  created() {
    this.getTransactions()
    this.getSummary()
    //this.$nextTick(() => {
    //this.selectedFilter = 'all'
    //})
    const now = moment()
    this.txnFilters.end_date = moment().format('YYYY-MM-DD')
    this.txnFilters.start_date = moment().subtract(30, 'days').format('YYYY-MM-DD')
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
.crate-filters {
  .box-filter {
    border: solid 1px #353535;
  }
}
</style>
