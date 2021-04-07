<template>
  <div>
    <b-skeleton-wrapper :loading="loading">
      <template #loading>
        <div class="w-100 my-5 d-flex align-items-center justify-content-center">
          <fa-icon icon="spinner" size="3x" spin />
        </div>
      </template>
      <SavedPaymentMethodList
        class="mb-3"
        :selected="selectedPaymentMethod"
        @loadNewForm="loadNewForm"
        @loadPayWithForm="loadPayWithForm"
      />

      <transition name="fade" mode="out-in">
        <component
          :is="loadedForm"
          :payment-method="selectedPaymentMethod"
          :value="value"
          :price="price"
          :currency="currency"
          :price-display="displayPrice"
        />
      </transition>
    </b-skeleton-wrapper>

    <!-- <PayWithForm class="mt-3" /> -->
  </div>
</template>

<script>
/**
 * Base purchase form, for when something is being purchased
 */
import _ from 'lodash'
import Vuex from 'vuex'
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
    /** Item being purchased */
    value: { type: Object, default: () => ({}) },
    /** Price as integer */
    price: { type: Number, default: 0 },
    /** Price currency */
    currency: { type: String, default: 'USD' },
    /** Localized String of how to display currency to user */
    displayPrice: { type: String, default: '$0.00' },
  },

  computed: {
    ...Vuex.mapState('payments', [ 'savedPaymentMethods', 'defaultMethod' ]),
  },

  data: () => ({
    loadedForm: FromNew,
    selectedPaymentMethod: null,
    loading: true,
  }),

  methods: {
    ...Vuex.mapActions('payments', [ 'getSavedPaymentMethods' ]),

    loadPaymentMethods() {
      this.loading = true
      this.getSavedPaymentMethods()
        .then(() => {
          this.loading = false
          if (this.defaultMethod) {
            this.loadPayWithForm(_.find(this.savedPaymentMethods, ['id', this.defaultMethod]))
          }
        })
    },

    loadNewForm() {
      this.loadedForm = FromNew
      this.selectedPaymentMethod = { id: 'new' }
    },

    loadPayWithForm(paymentMethod) {
      this.selectedPaymentMethod = paymentMethod
      this.loadedForm = PaymentConfirmation
    },
  },

  mounted() {
    this.loadPaymentMethods()
  },

}
</script>
