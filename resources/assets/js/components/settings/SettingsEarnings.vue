<template>
  <div v-if="!isLoading">
    <b-card title="Balances" class="mb-1">
      <hr />
      <b-card-text>
        <b-row>
          <b-col>
            <h6>Credits</h6>
            <ul>
              <li>Subscriptions: {{ credits.subscription.total | niceCurrency }}</li>
              <li>Posts: {{ credits.sale.total | niceCurrency }}</li>
              <li>Tips: {{ credits.tip.total | niceCurrency }}</li>
            </ul>
          </b-col>
          <b-col>
            <h6>Debits</h6>
            <ul>
              <li>Fees: {{ debits.fee.total | niceCurrency }}</li>
              <li>Chargebacks: {{ debits.chargeback.total + debits.chargeback_partial.total | niceCurrency }}</li>
              <li>Refunds: {{ debits.refund.total | niceCurrency }}</li>
            </ul>
          </b-col>
        </b-row>
      </b-card-text>
    </b-card>

    <b-card title="Transactions">
      <TransactionsTable />
    </b-card>

  </div>
</template>

<script>
import Vuex from 'vuex'
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
    ...Vuex.mapState('earnings', [ 'sums' ]),
    ...Vuex.mapGetters('earnings', [ 'credits', 'debits']),

    isLoading() {
      return !this.credits || !this.debits
    },
  },

  data: () => ({}),

  methods: {
    ...Vuex.mapActions('earnings', [ 'updateSums' ]),

    onReset(e) {
      e.preventDefault()
    },
  },

  watch: {},

  created() {
    this.updateSums()
  }

}
</script>

<style scoped>
</style>
