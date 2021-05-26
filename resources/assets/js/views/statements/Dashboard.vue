<template>
  <div>
    <h2>{{ $t('title') }}</h2>
    <b-row>
      <b-col lg="4">
        <Balance />
      </b-col>
      <b-col lg="4">
        <Statistics />
      </b-col>
    </b-row>
    <b-card title="Transactions">
      <TransactionsTable />
    </b-card>
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

export default {
  name: "StatementsDashboard",

  components: {
    Balance,
    Statistics,
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
