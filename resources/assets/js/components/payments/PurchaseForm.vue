<template>
  <div>
    <PaymentsDisabled v-if="paymentsDisabled" />

    <b-skeleton-wrapper v-if="!paymentsDisabled" :loading="loading">
      <template #loading>
        <div class="w-100 my-5 d-flex align-items-center justify-content-center">
          <fa-icon icon="spinner" size="3x" spin />
        </div>
      </template>
      <LoadingOverlay :loading="processing" :text="$t('processing')" />

      <SavedPaymentMethodList
        ref="paymentMethods"
        class="mb-1"
        :selected="selectedPaymentMethod"
        collapse
        :showChangeButton="(this.selectedPaymentMethod.id === 'new')"
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
          :item-type="itemType"
          :price="typeof price === 'object' ? parseInt(price.amount) : price"
          :currency="typeof price === 'object' ? price.currency : currency"
          :price-display="displayPrice"
          @processing="onProcessing"
          @stopProcessing="onStopProcessing"
          @success="onSuccess"
          @changePaymentMethod="changePaymentMethod"
        />
      </transition>
    </b-skeleton-wrapper>

    <transition name="quick-fade">
      <ConfirmationCheckAnime v-if="showCompleted" />
    </transition>
    <!-- <PayWithForm class="mt-3" /> -->
  </div>
</template>

<script>
/**
 * Base purchase form, for when something is being purchased
 */
import { eventBus } from '@/eventBus'
import _ from 'lodash'
import Vuex from 'vuex'
import FormNew from './forms/New'
import PaymentConfirmation from './forms/PaymentConfirmation'
// import PayWithForm from './PayWithForm'
import SavedPaymentMethodList from './SavedPaymentMethodsList'
import LoadingOverlay from '@components/common/LoadingOverlay'
import SubscriptionIFrame from './forms/SegpaySubscriptionIFrame'
import LongRunningTransactionToast from './LongRunningTransactionToast'

import PaymentsDisabled from './PaymentsDisabled'
import ConfirmationCheckAnime from '@components/common/flair/ConfirmationCheckAnime'

export default {
  name: 'PurchaseForm',
  components: {
    ConfirmationCheckAnime,
    FormNew,
    LongRunningTransactionToast,
    PaymentConfirmation,
    PaymentsDisabled,
    // PayWithForm,
    SavedPaymentMethodList,
    LoadingOverlay,
  },

  props: {
    /** Item being purchased */
    value: { type: Object, default: () => ({}) },
    /** What type of item is being purchased, ( post, timeline ) */
    itemType: { type: String, default: ''},
    /** Price as integer */
    price: { type: [Number, Object], default: 0 },
    /** Price currency */
    currency: { type: String, default: 'USD' },
    /** Localized String of how to display currency to user */
    displayPrice: { type: String, default: '$0.00' },
    /** What type of payment this is, `tip | purchase | subscription` */
    type: { type: String, default: 'purchase'},
    /** Extra items to send to server such as notes for a tipped item */
    extra: { type: Object, default: () => ({})},

    /** For tips sent from the messages won't emit success signal until message is received from server */
    wantsMessage: { type: Boolean, default: false },
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
    maxProcessingWaitTime: 20 * 1000, // 20s
    waiting: null,
    paymentsDisabled: false,

    animationLength: 2000,
    showCompleted: false,
  }),

  methods: {
    ...Vuex.mapActions('payments', [ 'getSavedPaymentMethods' ]),

    changePaymentMethod() {
      this.$refs['paymentMethods'].change()
    },

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

    onProcessingTimeout() {
      this.processing = false

      // Close Modal
      this.$bvModal.hide('modal-purchase-post')
      this.$bvModal.hide('modal-tip')
      this.$bvModal.hide('modal-follow')

      const h = this.$createElement

      const vNodesMsg = h(
        'b-media',
        { props: { verticalAlign: 'center' } },
        [
          h('template', { slot: 'aside' }, [
            h('fa-icon', { props: { icon: 'exclamation-triangle', size: '2x' } }),
          ]),
          this.$t('overTimeToast.message'),
        ]
      )

      this.$root.$bvToast.toast([ vNodesMsg ], {
        title: this.$t('overTimeToast.title'),
        toaster: 'b-toaster-top-center',
        solid: true,
        variant: 'warning',
      })
    },

    onProcessing() {
      this.processing = true
      this.waiting = setTimeout(this.onProcessingTimeout, this.maxProcessingWaitTime)
    },

    onStopProcessing() {
      this.processing = false
      clearTimeout(this.waiting)
    },

    /** Success event from the loaded form */
    onSuccess(payload) {
      if (!this.wantsMessage || payload.message) {
        this.processing = false
        clearTimeout(this.waiting)
        this.completeAnimate(this.type, payload)
      }
    },

    completeAnimate(type, payload) {
      this.showCompleted = true
      this.$emit('completed', payload)
      setTimeout(() => {
        switch(type) {
          case 'purchase':
            this.$bvModal.hide('modal-purchase-post')
            break
          case 'tip':
            this.$bvModal.hide('modal-tip')
            break
          case 'subscribed':
            this.$bvModal.hide('modal-follow')
            break
        }
        this.showCompleted = false
      }, this.animationLength)
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
            this.completeAnimate('purchase')
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
            this.completeAnimate('tip', e)
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
            this.completeAnimate('subscribed')
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
    try {
      if ( window.paymentsDisabled || paymentsDisabled ) {
        this.paymentsDisabled = true
      }
    } catch (e) {}
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "processing": "Processing your transaction, this may take a moment",
    "overTimeToast": {
      "message": "This transaction has been processing for a long time, we will update you when it is complete",
      "title": "Long Running Transaction"
    }
  }
}
</i18n>
