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
      <b-pagination
        v-model="currentPage"
        :total-rows="rows"
        :per-page="perPage"
        aria-controls="txns-table"
      ></b-pagination>

      <b-table hover 
        id="txns-table"
        :items="getData"
        :per-page="perPage"
        :current-page="currentPage"
        :fields="fields"
        small
      >
        <template #cell(id)="data">
          <span class="">{{ data.item.id | niceGuid }}</span>
        </template>
        <template #cell(account_id)="data">
          <span class="">{{ data.item.account_id | niceGuid }}</span>
        </template>
        <template #cell(debit_amount)="data">
          <span class="">{{ data.item.debit_amount | niceCurrency }}</span> <!-- assumes US %FIXME -->
        </template>
        <template #cell(credit_amount)="data">
          <span class="">{{ data.item.credit_amount | niceCurrency }}</span> <!-- assumes US %FIXME -->
        </template>
        <template #cell(resource_id)="data">
          <span class="">{{ data.item.resource_id | niceGuid }}</span>
        </template>
        <template #cell(settled_at)="data">
          <span class="">{{ data.item.settled_at | niceDate }}</span>
        </template>
        <template #cell(created_at)="data">
          <span class="">{{ data.item.created_at | niceDate }}</span>
        </template>
      </b-table>
    </b-card>
  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
//import TransactionsTable from '@components/statements/transactions/Table'

export default {
  name: 'TxnManagement',

  props: {},

  computed: {
  },

  data: () => ({
    //items: [],

    currentPage: 1,
    rows: null,
    perPage: 20,
    fields: [
      { key: 'id', label: 'ID' },
      { key: 'account_id', label: 'Account' },
      { key: 'credit_amount', label: 'Credit' },
      { key: 'debit_amount', label: 'Debit' },
      { key: 'currency', label: 'Currency' },
      { key: 'type', label: 'Type' },
      //{ key: 'description', label: 'Description' },
      { key: 'resource_type', label: 'Resource Type' },
      { key: 'resource_id', label: 'Resource ID' },
      { key: 'settled_at', label: 'Settled' },
      { key: 'purchaser.username', label: 'Purchaser' },
      { key: 'created_at', label: 'Created' },
    ],
  }),

  methods: {
    async getData(ctx) {
      const params = `?page=${ctx.currentPage}&take=${ctx.perPage}`
      try {
        const response = await axios.get( this.$apiRoute('financial.transactions.index')+params )
        this.rows = response.data.meta.total
        return response.data.data
      } catch (e) {
        throw e
        return []
      }
    }
  },

  watchers: {},

  created() {
  },

  components: {
    //TransactionsTable,
  },

}
</script>

<style lang="scss" scoped>
</style>
