<template>
  <b-card :title="$t('PaymentMethods')">
    
  </b-card>
</template>

<script>
/**
 * Payment methods settings
 */
import Vuex from 'vuex'

export default {
  name: 'SettingPayments',

  computed: {
    ...Vuex.mapState('payments', [ 'savedPaymentMethods' ]),
  },

  data: () => ({
    loading: true
  }),

  methods: {
    ...Vuex.mapActions('payments', [ 'updateSavedPaymentMethods' ]),

    load() {
      this.loading = true
      this.updateSavedPaymentMethods()
        .then(() => { this.loading = false })
    },
  },

  mounted() {

  },

}
</script>

<i18n lang="json5">
{
  "en": {
    "PaymentMethods": "Payment Methods"
  }
}
</i18n>