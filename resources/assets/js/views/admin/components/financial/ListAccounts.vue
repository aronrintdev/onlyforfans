<template>
  <div>

    <section class="mt-3">
      <b-pagination
        v-model="currentPage"
        :total-rows="rows"
        :per-page="perPage"
        aria-controls="accounts-table"
      ></b-pagination>

      <b-table hover 
        id="accounts-table"
        :items="getData"
        :per-page="perPage"
        :current-page="currentPage"
        :fields="fields"
        small
      >
        <template #cell(id)="data">
          <span class="">{{ data.item.id | niceGuid }}</span>
        </template>
        <template #cell(owner_id)="data">
          <span class="">{{ data.item.owner_id | niceGuid }}</span>
        </template>
        <template #cell(balance)="data">
          <span class="">{{ data.item.balance.amount | niceCurrency }}</span> <!-- assumes US %FIXME -->
        </template>
        <template #cell(pending)="data">
          <span class="">{{ data.item.pending.amount | niceCurrency }}</span> <!-- assumes US %FIXME -->
        </template>
        <template #cell(resource_id)="data">
          <span class="">{{ data.item.resource_id | niceGuid }}</span>
        </template>
        <template #cell(verified)="data">
          <span class="">{{ data.item.verified | niceBool }}</span>
        </template>
        <template #cell(can_make_transactions)="data">
          <span class="">{{ data.item.can_make_transactions | niceBool }}</span>
        </template>
        <template #cell(created_at)="data">
          <span class="">{{ data.item.created_at | niceDate(true) }}</span>
        </template>
        <template #cell(balance_last_updated_at)="data">
          <span class="">{{ data.item.balance_last_updated_at | niceDate(true) }}</span>
        </template>
        <template #cell(pending_last_updated_at)="data">
          <span class="">{{ data.item.pending_last_updated_at | niceDate(true) }}</span>
        </template>
      </b-table>

    </section>

  </div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'

export default {

  props: {},

  computed: {
  },

  data: () => ({
    currentPage: 1,
    rows: null,
    perPage: 20,
    fields: [
      { key: 'id', label: 'ID' },
      { key: 'system', label: 'System' },
      { key: 'owner_type', label: 'Owner Type' },
      { key: 'owner_id', label: 'Owner ID' },
      { key: 'name', label: 'Name' },
      { key: 'type', label: 'Type' },
      { key: 'currency', label: 'Currency' },
      { key: 'balance', label: 'Balance' },
      { key: 'balance_last_updated_at', label: 'Balance Last Update' },
      { key: 'pending', label: 'Pending Amt' },
      { key: 'pending_last_updated_at', label: 'Pending Last Update' },
      { key: 'resource_type', label: 'Resource Type' },
      { key: 'resource_id', label: 'Resource ID' },
      { key: 'verified', label: 'Verified?' },
      { key: 'can_make_transactions', label: 'Can Transact?' },
      { key: 'created_at', label: 'Created' },
    ],
  }),

  methods: {
    async getData(ctx) {
      const params = `?page=${ctx.currentPage}&take=${ctx.perPage}`
      try {
        const response = await axios.get( this.$apiRoute('financial.accounts.index')+params )
        this.rows = response.data.meta.total
        return response.data.data
      } catch (e) {
        throw e
        return []
      }
    }
  },

  created() { },

  components: {
  },

  name: 'ListAccounts',

}
</script>

<style lang="scss" scoped>
</style>
