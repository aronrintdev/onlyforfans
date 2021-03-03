<template>
  <div v-if="!is_loading">

    <b-pagination
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="fanledgers-table"
    ></b-pagination>

    <p class="mt-3">Current Page: {{ currentPage }}</p>

    <b-card title="Earnings">
      <b-card-text>
        <b-table hover 
          id="fanledgers-table"
          :items="myProvider"
          :fields="fields"
          :current-page="currentPage"
        ></b-table>
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
    //...Vuex.mapState(['earnings']),
    ...Vuex.mapState(['is_loading']),

    totalRows() {
      return this.fanledgers.meta ? this.fanledgers.meta.total : 1;
    },
  },

  data: () => ({

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
          //return new Date().getFullYear() - item.age
        }
      },
      {
        key: 'base_unit_cost_in_cents',
        label: 'Gross',
        formatter: (value, key, item) => {
          //return this.$options.filters.niceCurrency(value)
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
        key: 'seller_id',
        label: 'Seller',
      },
    ],

  }),


  created() {
    this.getFanledgers({ 
      seller_id: this.session_user.id,
      page: 1,
      limit: this.perPage,
    })
    //this.$store.dispatch('getEarnings', this.session_user.id)
  },

  methods: {
    ...Vuex.mapActions({
      getFanledgers: "getFanledgers"
    }),

    /*
    pageClickHandler(e, page) {
      console.log('pageClickHandler.A', page)
      this.getFanledgers({ 
        seller_id: this.session_user.id,
        page: page,
      })
    },
     */

    myProvider(ctx, callback) {
      this.getFanledgers({ 
        seller_id: this.session_user.id,
        page: ctx.currentPage,
        limit: ctx.perPage,
      }).then( () => {
        callback(this.fanledgers.data)
      })
      return null
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
