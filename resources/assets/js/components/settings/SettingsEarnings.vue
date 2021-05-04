<template>
  <div v-if="!isLoading">
    <b-card title="Balances" class="mb-1">
      <hr />
      <b-card-text>
        <b-row>
          <b-col>
            <h6>Credits</h6>
            <ul>
              <li>Subscriptions: {{ earnings.sums.subscriptions | niceCurrency }}</li>
              <li>Posts: {{ earnings.sums.posts | niceCurrency }}</li>
              <li>Tips: {{ earnings.sums.tips | niceCurrency }}</li>
            </ul>
          </b-col>
          <b-col>
            <h6>Debits</h6>
            <ul>
              <li>Subscriptions: {{ debits.sums.subscriptions | niceCurrency }}</li>
              <li>Posts: {{ debits.sums.posts | niceCurrency }}</li>
              <li>Tips: {{ debits.sums.tips | niceCurrency }}</li>
            </ul>
          </b-col>
        </b-row>
      </b-card-text>
    </b-card>

    <b-card title="Transactions">
      <hr />
      <TransactionsTable />

      <b-tabs card>

        <b-tab title="Credits" active>
          <b-card-text>
            <b-table hover 
              id="ledgercredits-table"
              :items="ledgercredits.data"
              :fields="creditFields"
              :current-page="currentPageCredit"
            ></b-table>
            <b-pagination
              v-model="currentPageCredit"
              :total-rows="totalRowsCredits"
              :per-page="perPage"
              aria-controls="ledgercredits-table"
              v-on:page-click="pageClickHandlerCredit"
            ></b-pagination>
          </b-card-text>
        </b-tab>

        <b-tab title="Debits">
          <b-card-text>
            <b-table hover 
              id="ledgerdebits-table"
              :items="ledgerdebits.data"
              :fields="debitFields"
              :current-page="currentPageDebit"
            ></b-table>
            <b-pagination
              v-model="currentPageDebit"
              :total-rows="totalRowsDebits"
              :per-page="perPage"
              aria-controls="ledgerdebits-table"
              v-on:page-click="pageClickHandlerDebit"
            ></b-pagination>
          </b-card-text>
        </b-tab>

      </b-tabs>
    </b-card>

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import moment from 'moment'
import TransactionsTable from './earnings/TransactionsTable'

export default {
  name: 'SettingsEarnings',

  components: {
    TransactionsTable,
  },

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    //...Vuex.mapState(['fanledgers']),
    ...Vuex.mapState([
      'ledgercredits',
      'ledgerdebits',
      'earnings',
      'debits',
    ]),

    totalRowsCredits() {
      return this.ledgercredits.meta ? this.ledgercredits.meta.total : 1
    },
    totalRowsDebits() {
      return this.ledgerdebits.meta ? this.ledgerdebits.meta.total : 1
    },

    isLoading() {
      return !this.ledgercredits || !this.ledgerdebits || !this.earnings || !this.debits
    },
  },

  watch: {
  },

  data: () => ({

    perPage: 10,
    currentPageCredit: 1,
    currentPageDebit: 1,

    creditFields: [
      {
        key: 'id',
        label: 'ID',
        formatter: (value, key, item) => {
          return Vue.options.filters.niceGuid(value)
        }
      },
      {
        key: 'created_at',
        label: 'Date',
        formatter: (value, key, item) => {
          return moment(value).format('MMMM Do, YYYY')
        }
      },
      {
        key: 'base_unit_cost_in_cents',
        label: 'Gross',
        formatter: (value, key, item) => {
          return Vue.options.filters.niceCurrency(value)
        }
      },
      {
        key: 'fltype',
        label: 'Txn Type',
      },
      {
        key: 'purchaseable_type',
        label: 'Item Type',
      },
      {
        key: 'cattrs.notes',
        label: 'Description',
      },
      {
        key: 'purchaser.username',
        label: 'Purchaser',
      },
    ],

    debitFields: [
      {
        key: 'id',
        label: 'ID',
        formatter: (value, key, item) => {
          return Vue.options.filters.niceGuid(value)
        }
      },
      {
        key: 'created_at',
        label: 'Date',
        formatter: (value, key, item) => {
          return moment(value).format('MMMM Do, YYYY')
        }
      },
      {
        key: 'base_unit_cost_in_cents',
        label: 'Gross',
        formatter: (value, key, item) => {
          return Vue.options.filters.niceCurrency(value)
        }
      },
      {
        key: 'fltype',
        label: 'Txn Type',
      },
      {
        key: 'purchaseable_type',
        label: 'Item Type',
      },
      {
        key: 'cattrs.notes',
        label: 'Description',
      },
      {
        key: 'seller.username',
        label: 'Seller',
      },
    ],

  }),

  created() {
    this.getEarnings({ 
      user_id: this.session_user.id,
    })
    this.getDebits({ 
      user_id: this.session_user.id,
    })
    this.getLedgercredits({ 
      seller_id: this.session_user.id,
      page: 1,
      take: this.perPage,
    })
    this.getLedgerdebits({ 
      purchaser_id: this.session_user.id,
      page: 1,
      take: this.perPage,
    })
    /*
    this.getFanledgers({ 
      seller_id: this.session_user.id,
      page: 1,
      take: this.perPage,
    })
     */
  },

  methods: {
    ...Vuex.mapActions({
      getEarnings: "getEarnings",
      getDebits: "getDebits",
      getLedgercredits: "getLedgercredits",
      getLedgerdebits: "getLedgerdebits",
    }),

    pageClickHandlerCredit(e, page) {
      console.log('pageClickHandlerCredit', page)
      this.getLedgercredits({ 
        seller_id: this.session_user.id,
        page: page,
        take: this.perPage,
      })
    },

    pageClickHandlerDebit(e, page) {
      console.log('pageClickHandlerDebit', page)
      this.getLedgerdebits({ 
        purchaser_id: this.session_user.id,
        page: page,
        take: this.perPage,
      })
    },

    onReset(e) {
      e.preventDefault()
    },
  },

}
</script>

<style scoped>
</style>
