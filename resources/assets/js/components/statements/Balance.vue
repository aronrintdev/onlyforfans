<template>
  <b-card :title="$t('title')">
    <b-list-group class="mb-3">
      <b-list-group-item>
        <div class="d-flex">
          <span v-text="$t('available')" />
          <span class="ml-auto">{{ balances.available | niceCurrency }}</span>
        </div>
      </b-list-group-item>
      <b-list-group-item>
        <div class="d-flex">
          <span v-text="$t('pending')" />
          <span class="ml-auto">{{ balances.pending | niceCurrency }}</span>
        </div>
      </b-list-group-item>
    </b-list-group>
    <div class="d-flex">
      <b-btn variant="primary" class="ml-auto">
        {{ $t('request') }}
      </b-btn>
    </div>
  </b-card>
</template>

<script>
/**
 * resources/assets/js/components/statements/Balance.vue
 */
import Vuex from 'vuex'

export default {
  name: 'Balance',

  components: {},

  props: {},

  computed: {
    ...Vuex.mapState('statements', [ 'balances' ]),
  },

  data: () => ({}),

  methods: {
    ...Vuex.mapActions('statements', [ 'getBalances' ]),
    init() {
      this.getBalances()
    },
  },

  watchers: {},

  created() {
    this.init()
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
    "request": "Request Withdrawal"
  }
}
</i18n>
