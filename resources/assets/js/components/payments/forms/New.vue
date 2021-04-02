<template>
  <div>
    <keep-alive>
      <component
        :is="(systems[selected]) ? systems[selected].component : null"
        :value="value"
        :price="price"
        :currency="currency"
      />
    </keep-alive>
  </div>
</template>

<script>
/**
 * Form for new payment method
 */
import SegpayNew from './SegpayNew'

export default {
  name: 'New',

  components: {
    SegpayNew,
  },

  props: {
    selected: { type: String, default: 'segpay' },
    price: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
    value: { type: Object, default: () => ({})},
  },

  data: () => ({
    systems: {
      'segpay': {
        component: SegpayNew,
      }
    }
  }),
}
</script>

<style lang="scss" scoped>

</style>