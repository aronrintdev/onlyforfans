<template>
  <b-card :title="$t('title')" class="mb-1" v-if="!isLoading">
    <hr />
    <b-card-text>

      From: {{ DateTime().fromISO(totals.from).toLocaleString(DateTime().DATETIME_MED) }} <br/>
      To: {{ DateTime().fromISO(totals.to).toLocaleString(DateTime().DATETIME_MED) }}

      <b-list-group>
        <CollapseGroupItem>
          <template #parent>
            {{ creditTotal | niceCurrency }}
            {{ $t('credits') }}
          </template>

          <b-list-group-item v-for="(item, index) in credits" :key="index">
            {{ item.total | niceCurrency }}
            {{ $t(index) }}
            <b-badge v-if="item.count > 0" variant="primary" class="ml-3" v-text="item.count" />
          </b-list-group-item>
        </CollapseGroupItem>

        <CollapseGroupItem>
          <template #parent>
            {{ debitTotal | niceCurrency }}
            {{ $t('debits') }}
          </template>

          <b-list-group-item v-for="(item, index) in debits" :key="index">
            {{ item.total | niceCurrency }}
            {{ $t(index) }}
            <b-badge v-if="item.count > 0" variant="primary" class="ml-3" v-text="item.count" />
          </b-list-group-item>
        </CollapseGroupItem>
        <b-list-group-item>
          <span class="ml-3">{{ total | niceCurrency }} {{ $t('net') }}</span>
        </b-list-group-item>

      </b-list-group>
    </b-card-text>
  </b-card>
</template>

<script>
/**
 * Earnings Statistics
 */
import Vuex from 'vuex'
import CollapseGroupItem from '@components/common/CollapseGroupItem'

export default {
  name: 'Statistics',

  components: {
    CollapseGroupItem,
  },

  computed: {
    ...Vuex.mapState('statements', [ 'totals' ]),
    ...Vuex.mapGetters('statements', [
      'credits',
      'debits',
      'creditTotal',
      'debitTotal',
    ]),

    total() {
      return this.creditTotal - this.debitTotal
    },

    isLoading() {
      return !this.credits || !this.debits
    },
  },

  data: () => ({}),

  methods: {
    ...Vuex.mapActions('statements', [ 'updateTotals' ]),

    onReset(e) {
      e.preventDefault()
    },
  },

  watch: {},

  created() {
    this.updateTotals()
  }
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Statistics",
    "credits": "Earnings",
    "subscription": "Subscriptions",
    "sale": "Sales",
    "tip": "Tips",
    "debits": "Expenses",
    "fee": "Fees",
    "chargeback": "Chargebacks",
    "refund": "Refunds",
    "net": "Net Earnings"
  }
}
</i18n>
