<template>
  <div class="w-100">
    <!-- Mobile View -->
    <WithSidebar v-if="mobile" :focusMain="$route.name !== 'statements.dashboard'" @back="$router.push({name: 'statements.dashboard'})">
      <template #sidebar>
        <TopEarners class="mb-3" />
        <Balance class="mb-3" />

        <NavList :items="navItems" />
      </template>

      <template #mobileTitle>
        <div class="h2 ml-2 py-2">
          <fa-icon icon="receipt" fixed-width class="mr-2" />
          {{ $t('title.sidebar') }}
        </div>
      </template>

      <template #mobileMainNavTopTitle>
        <span class="h5">
          {{ $t(`title.${$route.name}`) }}
        </span>
      </template>

      <div class="w-100">
        <router-view />
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

        <NavList :items="navItems" />
      </template>

      <b-card class="w-100 h-100">
        <transition name="quick-fade" mode="out-in">
          <router-view />
        </transition>
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
import NavList from '@components/common/navigation/NavList'

import TopEarners from './components/TopEarners'

import WithSidebar from '@views/layouts/WithSidebar'

export default {
  name: "StatementsDashboard",

  components: {
    Balance,
    NavList,
    TopEarners,
    WithSidebar,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),

    navItems() {
      return [
        {
          key: 'stats',
          label: this.$t('navigation.stats'),
          to: { name: 'statements.statistics' },
        },
        {
          key: 'transactions',
          label: this.$t('navigation.transaction'),
          to: { name: 'statements.transactions' },
        },
        {
          key: 'payouts',
          label: this.$t('navigation.payouts'),
          to: { name: 'statements.payouts' },
        },
        {
          key: 'chargebacks',
          label: this.$t('navigation.chargebacks'),
          to: { name: 'statements.chargebacks' },
        },
      ]
    },
  },

  data: () => ({}),

  watch: {
    mobile(value) {
      if (!value) {
        this.$router.push({ name: 'statements.statistics' })
      }
    },
  },

  mounted() {
    if (!this.mobile && this.$route.name === 'statements.dashboard') {
      this.$router.push({ name: 'statements.statistics' })
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
      "statements": {
        "chargebacks": "Chargebacks",
        "dashboard": "Statements",
        "payouts": "Payouts",
        "statistics": "Statistics",
        "transactions": "Transactions"
      },
    },
    "navigation": {
      "chargebacks": "Chargebacks",
      "payouts": "Payouts",
      "stats": "Statistics",
      "transaction": "Transactions"
    }
  }
}
</i18n>
