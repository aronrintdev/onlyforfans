<template>
  <div>
    <h1>Financial Management</h1>
    <b-tabs>
      <b-tab title="Transactions" lazy active>
        <ListTransactions />
      </b-tab>
      <b-tab title="Accounts" lazy>
        <ListAccounts />
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

import ListAccounts from './ListAccounts'
import ListTransactions from './ListTransactions'

export default {

  props: {},

  computed: { },

  data: () => ({ }),

  methods: { },

  watchers: {},

  created() {},

  components: {
    ListAccounts,
    ListTransactions,
  },

  name: 'FinancialManagement',
}
</script>

<style lang="scss" scoped>
</style>
