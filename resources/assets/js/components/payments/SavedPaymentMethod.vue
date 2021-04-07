<template>
  <component :is="as" button class="payment-method-selection cursor-pointer d-flex" :variant="selected ? 'success' : 'default'" @click="select">
    <fa-icon :icon="icon" fixed-width :size="iconSize" />
    <span class="nickname mx-3" v-text="value.nickname" />
    <span v-if="value.last_4" v-text="$t('last4', { last_4: value.last_4 })" />

    <fa-icon v-if="selected" :icon="selectedIcon" fixed-width :size="iconSize" class="text-success ml-auto" />
  </component>
</template>

<script>

export default {
  props: {
    as: { type: [ String, Symbol ], default: 'b-list-group-item' },
    fallbackIcon: { type: String, default: 'circle', },
    iconSize: { type: String, default: '2x' },
    selected: { type: Boolean, default: false },
    selectedIcon: { type: [String, Array], default: 'check'},
    value: { type: Object, default: () => ({}), },
  },
  computed: {
    icon() {
      if (this.value.type === 'card') {
        switch(this.value.brand) {
          case 'visa': case 'stripe': case 'paypal':
          case 'mastercard': case 'jcb': case 'discover':
          case 'diners-club': case 'apple-pay': case 'amex':
          case 'amazon-pay':
            return [
              'fab',
              `cc-${this.value.brand}`
            ]
          default:
            return 'credit-card'
        }
      }
      return this.value.icon || this.fallbackIcon
    }
  },

  methods: {
    select() {
      this.$emit('selected')
    }
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "last4": "ending in {last_4}"
  }
}
</i18n>
