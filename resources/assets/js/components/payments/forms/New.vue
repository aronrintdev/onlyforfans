<template>
  <div>
    <keep-alive>
      <component :is="(systems[selected]) ? systems[selected].component : null" />
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