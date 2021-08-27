<template>
  <div class="statistics mb-1 position-relative" v-if="!isLoading">
    <RangeSelector class="p-3" @select="onRangeSelect" />

    <!-- From: {{ DateTime().fromISO(totals.from).toLocaleString(DateTime().DATETIME_MED) }} <br/> -->
    <!-- To: {{ DateTime().fromISO(totals.to).toLocaleString(DateTime().DATETIME_MED) }} -->

    <!-- Put graph Here -->
    <StatisticsChart :data="totals" :active="activeData" />

    <b-list-group flush>
      <CollapseGroupItem>
        <template #parent>
          <div class="w-100 d-flex align-items-center">
            <b-btn
              variant="link"
              class="mr-2"
              :class="{ 'text-muted': !isActive('earnings') }"
              @click.stop="toggleActive('earnings')"
            >
              <fa-icon icon="analytics" fixed-width />
            </b-btn>
            <span v-text="$t('credits')" />
            <span class="ml-auto">
              {{ creditTotal | niceCurrency }}
            </span>
          </div>
        </template>

        <b-list-group-item v-for="(item, key) in credits" :key="key">
          <div class="w-100 d-flex align-items-center">
            <b-btn
              variant="link"
              class="mr-2"
              :class="{ 'text-muted': !isActive(key) }"
              @click.stop="toggleActive(key)"
            >
              <fa-icon icon="analytics" fixed-width />
            </b-btn>
            <span v-text="$t(key)" />
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
          <div class="w-100 d-flex align-items-center">
            <span v-text="$t('debits')" />
            <span class="ml-auto">
              - {{ debitTotal | niceCurrency }}
            </span>
          </div>
        </template>

        <b-list-group-item v-for="(item, index) in debits" :key="index">
          <div class="w-100 d-flex align-items-center">
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
        <div class="w-100 d-flex align-items-center">
          <span class="ml-3" v-text="$t('net')" />
          <span class="ml-auto">
            {{ total | niceCurrency }}
          </span>
        </div>
      </b-list-group-item>

    </b-list-group>

    <LoadingOverlay :loading="loading" />
  </div>
</template>

<script>
/**
 * Earnings Statistics
 */
import _ from 'lodash'
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
    activeData: [
      'earnings',
    ],
  }),

  methods: {
    ...Vuex.mapActions('statements', [ 'updateTotals' ]),

    isActive(key) {
      return _.indexOf(this.activeData, key) !== -1
    },

    toggleActive(key) {
      const index = _.indexOf(this.activeData, key)
      if (index === -1) {
        this.activeData.push(key)
      } else {
        this.$delete(this.activeData, index)
      }
    },

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

<style lang="scss" scoped>
// .statistics {
//   height: 100%;
// }
</style>

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
