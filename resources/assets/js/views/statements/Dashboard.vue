<template>
  <div>
    <h2>{{ $t('title') }}</h2>

    <Balances />

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
import Balances from '@components/statements/Balances'
import TransactionsTable from '@components/statements/transactions/Table'

export default {
  name: "StatementsDashboard",

  components: {
    Balances,
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
