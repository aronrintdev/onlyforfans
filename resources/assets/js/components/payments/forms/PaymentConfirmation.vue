<template>
  <b-card class="w-100" style="position: unset;">
    <div
      class="h4 mb-3"
      v-text="$t('confirm.header', { type: $t(`types.${this.type}`), })"
    />
    <b-row>
      <b-col class="mb-3 mb-md-0">
        <SavedPaymentMethod as="div" :value="paymentMethod" />
      </b-col>
      <b-col md="auto" class="ml-auto">
        <b-btn block variant="success" class="d-flex align-items-center" @click="onConfirm">
          <fa-icon icon="check" size="lg" class="mr-2" />
          {{ $t('confirm.button', { type: $t(`types.${this.type}`), price: priceDisplay }) }}
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
    itemType: { type: String, default: '' },
    paymentMethod: { type: Object, default: () => ({}) },
    price: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
    type: { type: String, default: 'purchase' }, // purchase | tip | subscribe
    extra: { type: Object, default: () => ({})},
  },

  computed: {
    ...Vuex.mapState(['session_user']),

    priceDisplay() {
      return this.$options.filters.niceCurrency(this.price, this.currency)
    },

    purchasesChannel() {
      return `user.${this.session_user.id}.purchases`
    },
  },

  data: () => ({
    processing: false,
  }),

  methods: {
    init() {
      //
    },

    onConfirm() {
      this.$emit('processing')
      var route = ``
      switch(this.itemType) {
        case 'post': case 'posts':
          route = this.$apiRoute(`posts.${this.type}`, { post: this.value })
          break
        case 'timeline': case 'timelines':
          route = this.$apiRoute(`timelines.${this.type}`, { timeline: this.value })
          break
        default:
          route = this.$apiRoute(`${this.itemType}.${this.type}`, { [this.itemType]: this.value })
          break
      }
      if (route === '') {
        this.$nextTick(() => {
          eventBus.$emit('error', { message: "Invalid Item type", })
          this.$emit('stopProcessing')
        })
        return
      }

      this.axios.put(route, {
        account_id: this.paymentMethod.id,
        amount: this.price,
        currency: this.currency,
        message: this.extra
          ? this.extra.message ? this.extra.message : null
          : null,
      }).then(response => {
        this.$log.debug('PaymentConfirmation onConfirm', { response })
      }).catch(error => {
        eventBus.$emit('error', { error, message: "An error has occurred", })
        this.$emit('stopProcessing')
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
    "types": {
      "purchase": "Purchase",
      "tip": "Tip",
      "subscription": "Subscription"
    },
    "confirm": {
      "header": "Confirm {type} With Payment Method:",
      "button": "Confirm {type} Of {price}"
    }
  }
}
</i18n>