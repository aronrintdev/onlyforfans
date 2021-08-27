<template>
  <b-table
    id="payout-transactions-table"
    responsive
    :fields="fields"
    :items="items"
    show-empty
    :empty-text="$t('emptyMessage')"
  >
    <template #empty="scope">
      <div class="d-flex justify-content-center mt-5">
        {{ scope.emptyText }}
      </div>
    </template>
  </b-table>
</template>

<script>
/**
 * resources/assets/js/components/statements/Payouts/Table.vue
 */
import Vue from 'vue'
import Vuex from 'vuex'

import { DateTime } from 'luxon'

export default {
  name: 'Table',

  components: {},

  props: {
    items: { type: Array, default: () => ([]) },
  },

  computed: {
    fields() {
      return [
        {
          key: 'created_at',
          label: this.$t('label.date'),
          formatter: (value, key, item) => {
            return DateTime.fromISO(value).toLocaleString(DateTime.DATETIME_MED);
          }
        },
        {
          key: 'debit_amount',
          label: this.$t('label.amount'),
          formatter: (value, key, item) => {
            return Vue.options.filters.niceCurrency(value)
          }
        },
        {
          key: 'status',
          label: this.$t('label.status'),
        },
        {
          key: 'details',
          label: this.$t('label.details'),
        },
      ]
    },
  },

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "emptyMessage": "No payout requests have been made yet.",
    "label": {
      "date": "Date",
      "amount": "Amount",
      "status": "Status",
      "details": "Details",
    },
  }
}
</i18n>
