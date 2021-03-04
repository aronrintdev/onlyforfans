<template>
  <div v-if="!isLoading">
    <b-card title="Total Earnings" class="mb-3">
      <b-card-text>
        <div>Subscriptions: {{ earnings.sums.timelines | niceCurrency }}</div>
        <div>Posts: {{ earnings.sums.posts | niceCurrency }}</div>
      </b-card-text>
    </b-card>

    <b-card title="Transactions">
      <b-card-text>
        <b-table hover 
          id="fanledgers-table"
          :items="fanledgers.data"
          :fields="fields"
          :current-page="currentPage"
        ></b-table>
        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          aria-controls="fanledgers-table"
          v-on:page-click="pageClickHandler"
        ></b-pagination>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import Vue from 'vue' // needed to use niceCurrency filter in table formatter below
import Vuex from 'vuex'
import moment from 'moment'

export default {

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    ...Vuex.mapState(['fanledgers']),
    ...Vuex.mapState(['earnings']),
    //...Vuex.mapState(['is_loading']),

    totalRows() {
      return this.fanledgers.meta ? this.fanledgers.meta.total : 1;
    },

    isLoading() {
      return !this.is_fanledgers_loading && !this.is_earnings_loading;
    },
  },

  watch: {
    fanledgers(value) {
      if (value && value.length > 0) {
        this.is_fanledgers_loading = false
      }
    },
    earnings(value) {
      if (value) {
        this.earnings_loading = false
      }
    },
  },

  data: () => ({

    is_fanledgers_loading: true,
    is_earnings_loading: true,

    perPage: 10,
    currentPage: 1,
    //totalRows: 100,
    nextPage: null,
    lastPage: null,
    isLastPage: null,

    fields: [
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
        //key: 'purchaser_id',
        key: 'purchaser.username',
        label: 'Purchaser',
      },
    ],

  }),


  created() {
    this.getEarnings({ 
      user_id: this.session_user.id,
    })
    this.getFanledgers({ 
      seller_id: this.session_user.id,
      page: 1,
      take: this.perPage,
    })
  },

  methods: {
    ...Vuex.mapActions({
      getEarnings: "getEarnings",
      getFanledgers: "getFanledgers",
    }),

    pageClickHandler(e, page) {
      this.getFanledgers({ 
        seller_id: this.session_user.id,
        page: page,
        take: this.perPage,
      })
    },

    onReset(e) {
      e.preventDefault()
    },
    /*
    perPage() {
      return this.fanledgers.meta.per_page
    },
    currentPage() {
      return this.fanledgers.meta.current_page
    },
    nextPage() {
      return this.fanledgers.meta.current_page + 1
    },
    lastPage() {
      return this.fanledgers.meta.last_page
    },
    isLastPage() {
      return this.fanledgers.meta.current_page === this.feeddata.meta.last_page
    },
     */
  },

}
</script>

<style scoped>
</style>
