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
    ...Vuex.mapState([
      'earnings',
      'debits',
    ]),

    isLoading() {
      return !this.earnings || !this.debits
    },
  },

  watch: {
  },

  data: () => ({
    //
  }),

  created() {
    this.getEarnings({ 
      user_id: this.session_user.id,
    })
    this.getDebits({ 
      user_id: this.session_user.id,
    })
  },

  methods: {
    ...Vuex.mapActions({
      getEarnings: "getEarnings",
      getDebits: "getDebits",
    }),

    onReset(e) {
      e.preventDefault()
    },
  },

}
</script>

<style scoped>
</style>
