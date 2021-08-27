<template>
  <b-card no-body>
    <b-list-group flush>
      <b-list-group-item>
        <div class="d-flex">
          <span v-text="$t('available')" />
          <span class="ml-auto font-weight-bold">{{ available | niceCurrency }}</span>
        </div>
      </b-list-group-item>
      <b-list-group-item>
        <div class="d-flex">
          <span v-text="$t('pending')" />
          <span class="ml-auto">{{ pending | niceCurrency }}</span>
        </div>
      </b-list-group-item>
    </b-list-group>
    <div class="d-flex pt-3 px-3">
      <div class="text-muted ml-auto font-size-small">
        {{ $t('minMessage', { amount: minimumDisplay }) }}
      </div>
    </div>
    <div class="d-flex p-3">
      <RequestWithdrawal
        class="ml-auto"
        :disabled="!balances.balance || balances.balance.amount <= minimum"
        :balance="balances.balance"
        :minimum="minimum"
        @completed="refresh"
      />
    </div>
  </b-card>
</template>

<script>
/**
 * resources/assets/js/components/statements/Balance.vue
 *
 * Displays Available and Pending Balance
 */
import Vuex from 'vuex'
import RequestWithdrawal from './RequestWithdrawal'
import gsap from 'gsap'

export default {
  name: 'Balance',

  components: {
    RequestWithdrawal,
  },

  props: {},

  computed: {
    ...Vuex.mapState('statements', [ 'balances' ]),

    minimumDisplay() {
      return this.$options.filters.niceCurrency(this.minimum, this.available.currency)
    },
  },

  data: () => ({
    available: { amount: 0, currency: 'USD' },
    pending: { amount: 0, currency: 'USD' },

    minimum: 2000, // $20

    availableAnime: null,
    pendingAnime: null,
  }),

  methods: {
    ...Vuex.mapActions('statements', [ 'getBalances' ]),
    init() {
      this.refresh()
    },

    refresh() {
      this.getBalances()
    }
  },

  watchers: {},

  created() {
    this.init()
    this.unWatch = this.$store.watch(
      (state, getters) => state.statements.balances,
      (newVal, oldVal) => {
        gsap.to(this.$data.available, {
          duration: 0.5,
          amount: newVal.balance.amount,
        } )
        gsap.to(this.$data.pending, {
          duration: 0.5,
          amount: newVal.pending.amount,
        } )
      },
      { deep: true },
    )
  },
  beforeDestroy() {
    this.unWatch()
  },
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Balances",
    "available": "Available Balance",
    "pending": "Pending Balance",
    "minMessage": "Minimum withdrawal amount: {amount}"
  }
}
</i18n>
