<template>
  <div>
    <b-skeleton-wrapper :loading="loading">
      <template #loading>
        <div class="w-100 my-5 d-flex align-items-center justify-content-center">
          <fa-icon icon="spinner" size="3x" spin />
        </div>
      </template>
      <LoadingOverlay :loading="processing" :text="$t('processing')" />

      <SavedPaymentMethodList
        class="mb-3"
        :selected="selectedPaymentMethod"
        collapse
        @loadNewForm="loadNewForm"
        @loadPayWithForm="loadPayWithForm"
      />

      <transition name="fade" mode="out-in">
        <component
          :key="selectedPaymentMethod.id || 'new'"
          :is="loadedForm"
          :type="type"
          :extra="extra"
          :payment-method="selectedPaymentMethod"
          :value="value"
          :price="price"
          :currency="currency"
          :price-display="displayPrice"
          @processing="onProcessing"
          @stopProcessing="onStopProcessing"
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
import { eventBus } from '@/app'
import _ from 'lodash'
import Vuex from 'vuex'
import FormNew from './forms/New'
import PaymentConfirmation from './forms/PaymentConfirmation'
import PayWithForm from './PayWithForm'
import SavedPaymentMethodList from './SavedPaymentMethodsList'
import LoadingOverlay from '@components/common/LoadingOverlay'
import SubscriptionIFrame from './forms/SegpaySubscriptionIFrame'

export default {
  name: 'PurchaseForm',
  components: {
    FormNew,
    PaymentConfirmation,
    PayWithForm,
    SavedPaymentMethodList,
    LoadingOverlay,
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
    /** What type of payment this is, `tip | purchase | subscription` */
    type: { type: String, default: 'purchase'},
    /** Extra items to send to server such as notes for a tipped item */
    extra: { type: Object, default: () => ({})},
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),
    ...Vuex.mapState('payments', [ 'savedPaymentMethods', 'defaultMethod' ]),

    purchasesChannel() {
      return `user.${this.session_user.id}.purchases`
    },
  },

  data: () => ({
    subscriptionMustIFrame: true,
    loadedForm: FormNew,
    selectedPaymentMethod: {},
    loading: true,
    processing: false,
    maxProcessingWaitTime: 10 * 1000, // 10s
    waiting: null,
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
      this.loadedForm = FormNew
      this.selectedPaymentMethod = { id: 'new' }
    },

    loadPayWithForm(paymentMethod) {
      this.selectedPaymentMethod = paymentMethod
      if (this.subscriptionMustIFrame && this.type === 'subscription') {
        this.loadedForm = SubscriptionIFrame
      } else {
        this.loadedForm = PaymentConfirmation
      }
    },

    onProcessing() {
      this.processing = true
      this.waiting = setTimeout(() => {
        this.processing = false

        // TODO: Display better error message
        this.$root.$bvToast.toast('Transaction has been processing for a long time', { variant: 'danger' })
      }, this.maxProcessingWaitTime)
    },

    onStopProcessing() {
      this.processing = false
      clearTimeout(this.waiting)
    },

    init() {
      /* Purchase */
      if (this.type === 'purchase') {
        this.$log.debug('Registering purchase listeners')
        this.$root.$on('bv::modal::hide', (bvEvent, modalId) => {
          if (modalId === 'modal-purchase-post' && this.processing) {
            bvEvent.preventDefault()
          }
        })
        this.$echo.private(this.purchasesChannel).listen('ItemPurchased', e => {
          if (e.item_id === this.value.id) {
            this.processing = false
            clearTimeout(this.waiting)
            this.$nextTick(() => {
              this.$bvModal.hide('modal-purchase-post')
            })
          }
        })

      /* Tip */
      } else if (this.type === 'tip') {
        this.$log.debug('Registering tip listeners')
        this.$root.$on('bv::modal::hide', (bvEvent, modalId) => {
          if (modalId === 'modal-tip' && this.processing) {
            bvEvent.preventDefault()
          }
        })
        this.$echo.private(this.purchasesChannel).listen('ItemTipped', e => {
          if (e.item_id === this.value.id) {
            this.processing = false
            clearTimeout(this.waiting)
            this.$nextTick(() => {
              this.$bvModal.hide('modal-tip')
            })
          }
        })

      /* Subscription */
      } else if (this.type === 'subscription') {
        this.$log.debug('Registering subscription listeners')
        this.$root.$on('bv::modal::hide', (bvEvent, modalId) => {
          if (modalId === 'modal-follow' && this.processing) {
            bvEvent.preventDefault()
          }
        })
        this.$echo.private(this.purchasesChannel).listen('ItemSubscribed', e => {
          this.$log.debug('ItemSubscribed received', { e, this_item_id: this.value.id })
          if (e.item_id === this.value.id) {
            this.processing = false
            clearTimeout(this.waiting)
            this.$nextTick(() => {
              this.$bvModal.hide('modal-follow')
            })
          }
        }).listen('SubscriptionFailed', e => {
          this.$log.debug('SubscriptionFailed received', { e, this_item_id: this.value.id })
          if (e.item_id === this.value.id) {
            this.processing = false
            clearTimeout(this.waiting)
            this.$nextTick(() => {
              this.$bvModal.hide('modal-follow')
            })
          }
        })
      }

    },
  },

  mounted() {
    this.init()
    this.loadPaymentMethods()
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "processing": "Processing",
  }
}
</i18n>
