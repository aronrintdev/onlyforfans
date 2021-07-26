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
    <b-card title="Transactions">
      <TransactionsTable />
    </b-card>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import TransactionsTable from '@components/statements/transactions/Table'
//import TransactionsTable from '@components/statements/transactions/Test'

export default {
  name: 'TxnManagement',

  props: {},

  computed: {
  },

  data: () => ({
  }),

  methods: {
  },

  watchers: {},

  created() {
  },

  components: {
    TransactionsTable,
  },

}
</script>

<style lang="scss" scoped>
</style>
