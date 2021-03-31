<template>
  <div>
    <SavedPaymentMethodList class="mb-3" @loadNewForm="loadNewForm" @loadPayWithForm="loadPayWithForm" />

    <transition name="component-fade" mode="out-in">
      <component :is="loadedForm" :payment-method="selectedPaymentMethod" />
    </transition>

    <PayWithForm class="mt-3" />
  </div>
</template>

<script>
/**
 * Base purchase form, for when something is being purchased
 */
import FromNew from './forms/New'
import PaymentConfirmation from './forms/PaymentConfirmation'
import PayWithForm from './PayWithForm'
import SavedPaymentMethodList from './SavedPaymentMethodsList'

export default {
  name: 'PurchaseForm',
  components: {
    FromNew,
    PaymentConfirmation,
    PayWithForm,
    SavedPaymentMethodList,
  },

  props: {
    price: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
    displayPrice: { type: String, default: '$ 0.00' },
  },

  data: () => ({
    loadedForm: FromNew,
    selectedPaymentMethod: null,
  }),

  methods: {
    loadNewForm() {
      this.loadedForm = FromNew
    },
    loadPayWithForm(paymentMethod) {
      this.selectedPaymentMethod = paymentMethod
      this.loadedForm = PaymentConfirmation
    },
  }

}
</script>

<style lang="scss" scoped>
.component-fade-enter-active, .component-fade-leave-active {
  transition: opacity .3s ease;
}
.component-fade-enter, .component-fade-leave-to {
  opacity: 0;
}
</style>
