<template>
  <b-card :title="$t('title')">


    <b-row>
      <b-col lg="6">
        <div class="h4" v-text="$t('accounts')" />
        <AccountList />
      </b-col>

      <b-col lg="6">
      </b-col>
    </b-row>
  </b-card>
</template>

<script>
/**
 * Settings Banking Page
 */
import AccountList from '@components/banking/accounts/List'
export default {
  name: 'Banking',

  components: {
    AccountList,
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Banking",
    "accounts": "Payment Accounts"
  }
}
</i18n>
