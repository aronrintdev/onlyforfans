<template>
  <div class="w-100">
    <div v-if="!skipping">
      <div
        v-if="false"
        class="h4 mb-3"
        v-text="$t('confirm.header', { type: $t(`types.${this.type}`), })"
      />
      <div class="d-flex flex-column">
        <div>
          <b-btn block variant="primary" size="lg" class="d-flex justify-content-center align-items-center" @click="onConfirm">
            <fa-icon icon="check" size="lg" class="mr-2" />
            {{
              displayPriceOnButton ? $t('confirm.button', { type: $t(`types.${this.type}`), price: priceDisplay })
                : $t('confirm.buttonNoPrice', { type: $t(`types.${this.type}`) })
            }}
          </b-btn>
        </div>
        <DiscountDisplay
          v-if="campaign.id"
          :price="{
            amount: typeof price === 'object' ? parseInt(price.amount) : price,
            currency: typeof price === 'object' ? price.currency : currency,
          }"
          :campaign="campaign"
        />
        <div class="mt-3 mb-3 mb-md-0 mx-auto">
          <SavedPaymentMethod as="div" :value="paymentMethod">
            <b-btn variant="link" @click="$emit('changePaymentMethod')">{{ $t('change') }}</b-btn>
          </SavedPaymentMethod>
        </div>

      </div>
    </div>
    <div v-else class="skipping"></div>
  </div>
</template>

<script>
/**
 * Confirms payment with a known payment method
 */
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
import DiscountDisplay from '@components/payments/DiscountDisplay'
import LoadingOverlay from '@components/common/LoadingOverlay'
import SavedPaymentMethod from '@components/payments/SavedPaymentMethod'

export default {
  name: 'PaymentConfirmation',
  components: {
    DiscountDisplay,
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

    /** Subscription campaign */
    campaign: { type: Object, default: () => ({}) },

    extra: { type: Object, default: () => ({})},
  },

  computed: {
    ...Vuex.mapState(['session_user']),

    displayPriceOnButton() {
      if (this.campaign.id) {
        if (this.campaign.type === 'trial') {
          return false
        }
      }
      return true
    },

    priceDisplay() {
      if (this.campaign.id) {
        const price = typeof this.price === 'object' ? this.price.amount : this.price
        return this.$options.filters.niceCurrency(
          Math.round(price * ((100 - this.campaign.discount_percent) / 100)),
          this.currency
        )
      }
      return this.$options.filters.niceCurrency(this.price, this.currency)
    },

    purchasesChannel() {
      return `user.${this.session_user.id}.purchases`
    },
  },

  data: () => ({
    processing: false,
    skipping: false,
  }),

  methods: {
    init() {
      // Tips skip over confirmation button
      if (this.type === 'tip') {
        this.skipping = true
        this.onConfirm()
      }
    },

    onConfirm() {
      this.$emit('processing')

      var type

      switch(this.type) {
        case 'subscribe': case 'subscription': case 'subscriptions':
          type = 'subscribe'
          break
        case 'purchase': default:
          type = 'purchase'
          break
        case 'tip':
          type = 'tip'
          break
      }

      var route = ``
      try {
        switch(this.itemType) {
          case 'post': case 'posts':
            route = this.$apiRoute(`posts.${type}`, { post: this.value })
            break
          case 'timeline': case 'timelines':
            route = this.$apiRoute(`timelines.${type}`, { timeline: this.value })
            break
          default:
            route = this.$apiRoute(`${this.itemType}.${type}`, { [this.itemType]: this.value })
            break
        }
      } catch (e) {
        eventBus.$emit('error', { error: e, message: "Invalid Item type", })
        this.$emit('stopProcessing')
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
        campaign: this.campaign.id ?? null
      }).then(response => {
        this.$log.debug('PaymentConfirmation onConfirm', { response })
        if (response.data.success) {
          this.$emit('success', response.data)
        } else {
          eventBus.$emit('error', { error, message: "An error has occurred", })
          this.$emit('stopProcessing')
        }
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

<style lang="scss" scoped>
.skipping {
  min-height: 10rem;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "change": "Change",
    "types": {
      "purchase": "Purchase",
      "tip": "Tip",
      "subscription": "Subscription"
    },
    "confirm": {
      "header": "Confirm {type}:",
      "buttonNoPrice": "Confirm {type}",
      "button": "Confirm {type} of {price}"
    }
  }
}
</i18n>
