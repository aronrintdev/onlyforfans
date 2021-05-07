<template>
  <div>
    <b-table hover 
      id="ledgercredits-table"
      :items="[]"
      :fields="creditFields"
      :current-page="currentPageCredit"
    />
  </div>
</template>

<script>
export default {
  name: 'TransactionsTable',

  computed: {
    fields() {
      return [{
          key: 'id',
          label: 'ID',
          formatter: (value, key, item) => {
            return Vue.options.filters.niceGuid(value)
          }
        }, {
          key: 'created_at',
          label: 'Date',
          formatter: (value, key, item) => {
            return moment(value).format('MMMM Do, YYYY')
          }
        }, {
          key: 'base_unit_cost_in_cents',
          label: 'Gross',
          formatter: (value, key, item) => {
            return Vue.options.filters.niceCurrency(value)
          }
        }, {
          key: 'fltype',
          label: 'Txn Type',
        }, {
          key: 'purchaseable_type',
          label: 'Item Type',
        }, {
          key: 'cattrs.notes',
          label: 'Description',
        }, {
          key: 'purchaser.username',
          label: 'Purchaser',
        },
      ]
    },
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
