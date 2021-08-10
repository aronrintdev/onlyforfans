<template>
  <div class="w-100">
    <h2>{{ $t('title') }}</h2>
    <b-row>
      <b-col lg="4">
        <TopEarners class="mb-3" />
        <Balance class="mb-3" />
        <Statistics class="mb-3" />
      </b-col>
      <b-col lg="8">
        <b-card class="w-100" title="Transactions">
          <TransactionsTable class="w-100" />
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>

<script>
/**
 * Creator Statements Dashboard
 */
import Vuex from 'vuex'
import Balance from '@components/statements/Balance'
import Statistics from '@components/statements/Statistics'
import TransactionsTable from '@components/statements/transactions/Table'

import TopEarners from './components/TopEarners'

export default {
  name: "StatementsDashboard",

  components: {
    Balance,
    Statistics,
    TopEarners,
    TransactionsTable,
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Statements"
  }
}
</i18n>
