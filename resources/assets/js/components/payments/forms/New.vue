<template>
  <div>
    <keep-alive>
      <component
        :is="(systems[selected]) ? systems[selected].component : null"
        :value="value"
        :price="price"
        :currency="currency"
        :type="type"
        :campaign="campaign"
        :extra="extra"
        @processing="$emit('processing')"
        @stopProcessing="$emit('stopProcessing')"
        @success="results => $emit('success', results)"
        @error="results => $emit('error', results)"
      />
    </keep-alive>
  </div>
</template>

<script>
/**
 * Form for new payment method
 */
import SegpayNew from './SegpayNew'
import SegpayNewIFrame from './SegpayNewIFrame'

export default {
  name: 'New',

  components: {
    SegpayNew,
    SegpayNewIFrame,
  },

  props: {
    selected: { type: String, default: 'segpay' },
    type: { type: String, default: 'purchase'},
    price: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
    value: { type: Object, default: () => ({})},
    campaign: { type: Object, default: () => ({}) },
    extra: { type: Object, default: () => ({})},
  },

  data: () => ({
    systems: {
      'segpay': {
        component: SegpayNew,
        // component: SegpayNewIFrame,
      }
    }
  }),
}
</script>

<style lang="scss" scoped>

</style>