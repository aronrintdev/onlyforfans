<template>
  <fa-icon :icon="icon" v-bind="$attrs" />
</template>

<script>
export default {
  name: 'CardBrandIcon',
  props: {
    cardNumber: { type: String, default: '' },
  },

  computed: {
    icon() {
      if (this.brand !== '') {
        return ['fab', `cc-${this.brand}`]
      }
      return 'credit-card'
    }
  },

  data: () => ({
    brand: '',

    lookupTable: {
      'amex': /^3[47]/,
      'diners-club': /^3(?:0[0-5]|[68])/,
      'jcb': /^(?:2131|1800|35)/,
      'visa': /^4/,
      'mastercard': /^5/,
      'discover': /^6/,
    }
  }),

  watch: {
    cardNumber(value) {
      for (var key in this.lookupTable) {
        if (this.lookupTable[key].test(value)) {
          this.brand = key
          return
        }
      }
      this.brand = ''
    },
  },

}
</script>

<style lang="scss" scoped>

</style>