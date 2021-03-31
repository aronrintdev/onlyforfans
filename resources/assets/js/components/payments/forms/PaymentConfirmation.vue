<template>
  <b-card class="w-100">
    <b-skeleton-wrapper :loading="loading">
      <template #loading>
        <div class="w-100 d-flex justify-content-center align-items-center">
          <fa-icon icon="spinner" spin size="3x" />
        </div>
      </template>
      <div class="h4 mb-3">
        Confirm payment with Method:
      </div>
      <b-row>
        <b-col class="mb-3 mb-md-0">
          <SavedPaymentMethod as="div" :value="paymentMethod" />
        </b-col>
        <b-col md="auto" class="ml-auto">
          <b-btn block variant="success" class="d-flex align-items-center" @click="onConfirm">
            <fa-icon icon="check" size="lg" class="mr-2" />
            Confirm payment of {{ priceDisplay }}
          </b-btn>
        </b-col>

      </b-row>
    </b-skeleton-wrapper>
  </b-card>
</template>

<script>
/**
 * Confirms payment with a known payment method
 */
import SavedPaymentMethod from '@components/payments/SavedPaymentMethod'
export default {
  name: 'PaymentConfirmation',
  components: {
    SavedPaymentMethod
  },
  props: {
    paymentMethod: { type: Object, default: () => ({}) },
    priceDisplay: { type: String, default: () => '$ 0.00' }
  },

  data: () => ({
    loading: false,
  }),

  methods: {
    onConfirm() {
      this.loading = true
    }
  },

}
</script>

<style lang="scss" scoped>

</style>