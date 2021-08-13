<template>
  <b-card :title="$t('title')" class="mb-1 position-relative" v-if="!isLoading" no-body>
    <RangeSelector class="p-3" @select="onRangeSelect" />

    <!-- From: {{ DateTime().fromISO(totals.from).toLocaleString(DateTime().DATETIME_MED) }} <br/> -->
    <!-- To: {{ DateTime().fromISO(totals.to).toLocaleString(DateTime().DATETIME_MED) }} -->

    <!-- Put graph Here -->
    <StatisticsChart :data="totals" />

    <b-list-group flush>
      <CollapseGroupItem>
        <template #parent>
          <div class="w-100 d-flex">
            <span v-text="$t('credits')" />
            <span class="ml-auto">
              {{ creditTotal | niceCurrency }}
            </span>
          </div>
        </template>

        <b-list-group-item v-for="(item, index) in credits" :key="index">
          <div class="w-100 d-flex">
            <span v-text="$t(index)" />
            <span>
              <b-badge v-if="item.count > 0" variant="primary" class="ml-3" v-text="item.count" />
            </span>
            <span class="ml-auto">
              {{ item.total | niceCurrency }}
            </span>
          </div>
        </b-list-group-item>
      </CollapseGroupItem>

      <CollapseGroupItem>
        <template #parent>
          <div class="w-100 d-flex">
            <span v-text="$t('debits')" />
            <span class="ml-auto">
              - {{ debitTotal | niceCurrency }}
            </span>
          </div>
        </template>

        <b-list-group-item v-for="(item, index) in debits" :key="index">
          <div class="w-100 d-flex">
            <span v-text="$t(index)" />
            <span>
              <b-badge v-if="item.count > 0" variant="primary" class="ml-3" v-text="item.count" />
            </span>
            <span class="ml-auto">
              {{ item.total | niceCurrency }}
            </span>
          </div>
        </b-list-group-item>
      </CollapseGroupItem>
      <b-list-group-item>
        <div class="w-100 d-flex">
          <span class="ml-3" v-text="$t('net')" />
          <span class="ml-auto">
            {{ total | niceCurrency }}
          </span>
        </div>
      </b-list-group-item>

    </b-list-group>

    <LoadingOverlay :loading="loading" />
  </b-card>
</template>

<script>
/**
 * Earnings Statistics
 */
import Vuex from 'vuex'
import CollapseGroupItem from '@components/common/CollapseGroupItem'
import LoadingOverlay from '@components/common/LoadingOverlay'
import RangeSelector from './RangeSelector'
import StatisticsChart from './StatisticsChart.vue'

export default {
  name: 'Statistics',

  components: {
    CollapseGroupItem,
    LoadingOverlay,
    RangeSelector,
    StatisticsChart,
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

  data: () => ({
    loading: false,
  }),

  methods: {
    ...Vuex.mapActions('statements', [ 'updateTotals' ]),

    onRangeSelect(range) {
      this.loading = true
      this.updateTotals(range)
        .finally(() => { this.loading = false })
    },

    onReset(e) {
      e.preventDefault()
    },
  },

  watch: {},

  created() {
    this.updateTotals({ ago: 7, ago_unit: 'day' })
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
