<template>
  <div>
    <h1>Financial Management</h1>
    <b-tabs>
      <b-tab title="Transactions" lazy active>
        <ListTransactions />
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

import ListTransactions from './ListTransactions'

export default {

  props: {},

  computed: { },

  data: () => ({ }),

  methods: { },

  watchers: {},

  created() {},

  components: {
    ListTransactions,
  },

  name: 'FinancialManagement',
}
</script>

<style lang="scss" scoped>
</style>
