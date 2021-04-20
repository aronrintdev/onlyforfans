<template>
  <b-card class="w-100" style="position: unset;">
    <LoadingOverlay :loading="processing" :text="$t('processing')" />
    <div class="h4 mb-3">
      Confirm payment with:
    </div>
    <b-row>
      <b-col class="mb-3 mb-md-0">
        <SavedPaymentMethod as="div" :value="paymentMethod" />
      </b-col>
      <b-col md="auto" class="ml-auto">
        <b-btn block variant="success" class="d-flex align-items-center" @click="onConfirm">
          <fa-icon icon="check" size="lg" class="mr-2" />
          {{ $t('confirm', { price: priceDisplay }) }}
        </b-btn>
      </b-col>

    </b-row>
  </b-card>
</template>

<script>
/**
 * Confirms payment with a known payment method
 */
import Vuex from 'vuex'
import { eventBus } from '@/app'
import LoadingOverlay from '@components/common/LoadingOverlay'
import SavedPaymentMethod from '@components/payments/SavedPaymentMethod'

export default {
  name: 'PaymentConfirmation',
  components: {
    LoadingOverlay,
    SavedPaymentMethod,
  },
  props: {
    value: { type: Object, default: () => ({}) },
    paymentMethod: { type: Object, default: () => ({}) },
    price: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
    type: { type: String, default: 'purchase' },
    priceDisplay: { type: String, default: () => '$0.00' }
  },

  computed: {
    ...Vuex.mapState(['session_user']),

    purchasesChannel() {
      return `user.${this.session_user.id}.purchases`
    },
  },

  data: () => ({
    processing: false,
  }),

  methods: {
    init() {
      this.$root.$on('bv::modal::hide', (bvEvent, modalId) => {
        if (modalId === 'modal-purchase-post' && this.processing) {
          bvEvent.preventDefault()
        }
      })

      this.$echo.private(this.purchasesChannel).listen('ItemPurchased', e => {
        if (e.item_id === this.value.id) {
          this.processing = false
          this.$nextTick(() => {
            this.$bvModal.hide('modal-purchase-post')
          })
        }
      })
    },

    onConfirm() {
      this.processing = true
      this.axios.post(this.$apiRoute('payments.purchase'), {
        item: this.value.id,
        type: this.type,
        price: this.price,
        currency: this.currency,
        method: this.paymentMethod.id,
      }).then(response => {}
      ).catch(error => {
        eventBus.$emit('error', { error, message: "An error has occurred", })
        this.processing = false
      })
    },
  },

  mounted() {
    this.init()
  },

}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "processing": "Processing",
    "confirm": "Confirm Payment Of {price}"
  }
}
</i18n>