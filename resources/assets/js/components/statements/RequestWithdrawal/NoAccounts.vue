<template>
  <div>
    <transition name="quick-fade" mode="out-in">
      <div v-if="showNew" key="new-form">
        <NewBankAccount />
      </div>

      <div v-else key="warning">
        <WarningAlert :header="$t('header')" :message="$t('message')" />
        <b-btn variant="success" block @click="showNew = true">
          <fa-icon icon="plus" fixed-width class="mr-2" />
          {{ $t('button') }}
        </b-btn>
      </div>
    </transition>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/statements/RequestWithdrawal/NoAccounts.vue
 */
import Vuex from 'vuex'
import WarningAlert from '@components/common/alerts/Warning'
import NewBankAccount from '@components/banking/accounts/New'

export default {
  name: 'NoAccounts',

  components: {
    NewBankAccount,
    WarningAlert,
  },

  props: {},

  computed: {},

  data: () => ({
    showNew: false,
  }),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "button": "Add a Bank Account",
    "header": "No Bank Accounts Attached!",
    "message": "You do not have a bank account attached to your Allfans account. Add a bank account to request your payout."
  }
}
</i18n>
