<template>
  <div class="w-100">
    <!-- Mobile View -->
    <WithSidebar v-if="mobile" :focusMain="focus !== 'sidebar'" @back="focus = 'sidebar'">
      <template #sidebar>
        <TopEarners class="mb-3" />
        <Balance class="mb-3" />

        <NavList :selected="focus" :items="navItems" @select="item => focus = item.key" />
      </template>

      <template #mobileTitle>
        <div class="h2 ml-2 py-2">
          <fa-icon icon="receipt" fixed-width class="mr-2" />
          {{ $t('title.sidebar') }}
        </div>
      </template>

      <template #mobileMainNavTopTitle>
        <span class="h5">
          {{ $t(`title.${focus}`) }}
        </span>
      </template>

      <div class="w-100">
        <TransactionsTable v-if="focus === 'transactions'" class="w-100" />
        <Statistics v-if="focus === 'stats'" class="mb-3" />
      </div>

    </WithSidebar>

    <!-- Desktop View -->
    <div v-if="!mobile" class="h2">
      <fa-icon icon="receipt" fixed-width class="mr-2" />
      {{ $t('title.sidebar') }}
    </div>
    <WithSidebar v-if="!mobile">
      <template #sidebar>
        <TopEarners class="mb-3" />
        <Balance class="mb-3" />

        <NavList :selected="focus" :items="navItems" @select="item => focus = item.key" />
      </template>

      <b-card class="w-100 h-100" :class="[ focus ]">
        <TransactionsTable v-if="focus === 'transactions'" class="w-100" />
        <Statistics v-if="focus === 'stats'" class="mb-3 h-100" />
      </b-card>
    </WithSidebar>

  </div>
</template>

<script>
/**
 * Creator Statements Dashboard
 */
import Vuex from 'vuex'
import Balance from '@components/statements/Balance'
import Statistics from '@components/statements/Statistics'
import TransactionsTable from '@components/statements/transactions/Table'
import NavList from '@components/common/navigation/NavList'

import TopEarners from './components/TopEarners'

import WithSidebar from '@views/layouts/WithSidebar'

export default {
  name: "StatementsDashboard",

  components: {
    Balance,
    NavList,
    Statistics,
    TopEarners,
    TransactionsTable,
    WithSidebar,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),

    navItems() {
      return [
        { key: 'stats', label: this.$t('navigation.stats'), },
        { key: 'transactions', label: this.$t('navigation.transaction'),}
      ]
    },
  },

  data: () => ({
    focus: 'sidebar', // sidebar | stats | transactions
  }),

  watch: {
    mobile(value) {
      if (!value) {
        this.focus = 'stats'
      }
    },
  },

  mounted() {
    if (!this.mobile) {
      this.focus = 'stats'
    }
  },

}
</script>

<style lang="scss" scoped>
.stats {
  max-height: calc(100vh - 10rem);
  overflow-y: auto;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": {
      "sidebar": "Statements",
      "stats": "Statistics",
      "transactions": "Transactions"
    },
    "navigation": {
      "stats": "Statistics",
      "transaction": "Transactions"
    }
  }
}
</i18n>
