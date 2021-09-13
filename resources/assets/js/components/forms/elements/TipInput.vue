<template>
  <PriceSelector
    ref="input"
    :label="$t('label')"
    v-model="amount"
    :currency="currency"
    allowZero
    :default="500"
    autofocus
    :limitWidth="false"
    @isValid="value => $emit('isValid', value)"
  />
</template>

<script>
/**
 * resources/assets/js/components/forms/elements/TipInput.vue
 */
import PriceSelector from '@components/common/PriceSelector'

export default {
  name: 'TipInput',

  components: {
    PriceSelector,
  },

  props: {
    value: { type: Object, default: () => ({ amount: 0, currency: 'USD' }) },
  },


  data: () => ({
    amount: 0,
    currency: 'USD',
  }),

  methods: {
    update(value) {
      this.amount = value.amount
      this.currency = value.currency
    },
    input() {
      this.$emit('input', { amount: this.amount, currency: this.currency })
    }
  },

  watch: {
    amount() {
      this.input()
    },
    currency() {
      this.input()
    },
    value(value) {
      this.update(value)
    },
  },

  mounted() {
    this.update(this.value)
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "label": "Tip Amount"
  }
}
</i18n>
