<template>
  <div>
    <div v-if="campaign.type === 'discount'">
      <p class="text-muted text-center m-0">
        <small>
          Discount applied: Subscribe for {{ discountPrice | niceCurrency}}.
          Renews at {{ price | niceCurrency  }}
        </small>
      </p>
    </div>
    <div v-if="campaign.type === 'trial'">
      <p class="text-muted text-center m-0">
        <small>
          First {{ campaign.trial_days }} days free! Renews at {{ price | niceCurrency }}.
        </small>
      </p>
    </div>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/payments/DiscountDisplay.vue
 */
import Vuex from 'vuex'

export default {
  name: 'DiscountDisplay',

  components: {},

  props: {
    price: { type: Object, default: () => ({ amount: 0, currency: 'USD' }) },
    campaign: { type: Object, default: () => ({}) },
    display: null,
  },

  computed: {
    discountPrice() {
      if (this.display) {
        return this.display
      }
      return {
        amount: Math.round(this.price.amount * ((100 - this.campaign.discount_percent) / 100)),
        currency: this.campaign.currency,
      }
    },
  },

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
