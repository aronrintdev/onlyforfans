<script>
/**
 * Responsible for listening on event channels and popping toast to the user on Events.
 */
import Vuex from 'vuex'
import { eventBus } from '@/app'

export default {
  name: 'Toaster',

  computed: {
    ...Vuex.mapState([ 'session_user' ]),
  },

  data: () => ({
    myItemsPurchasedBatch: {},
    cb: {
      'session_user': []
    },
  }),

  methods: {
    init() {
      this.errors()
      this.warnings()
      this.purchases()
      this.events()
    },

    errors() {
      eventBus.$on('error', ({ error, message }) => {
        this.$log.error(error)
        this.popToast(message, {
          variant: 'danger',
          title: this.$t('errorTitle'),
          toaster: 'b-toaster-top-center',
        })
      })
    },

    warnings() {
      eventBus.$on('popWarning', ({ message, title }) => {
        this.popToast(message, {
          variant: 'warning',
          title: title || this.$t('warningTitle'),
          toaster: 'b-toaster-top-center',
        })
      })
    },

    async purchases() {
      await this.waitFor('session_user')
      const channel = `user.${this.session_user.id}.purchases`
      this.$echo.private(channel)
        .listen('ItemPurchased', (e) => {
          this.$log.debug('Toast Event', { channel, event: 'ItemPurchased', e })
          this.popToast(`Post successfully purchased!`, {
            toaster: 'b-toaster-top-center',
            title: 'Success!',
            variant: 'success'
          })
        })
        .listen('ItemSubscribed', (e) => {
          this.$log.debug('Toast Event', { channel, event: 'ItemSubscribed', e })
          this.popToast(`Subscription was successful!`, {
            toaster: 'b-toaster-top-center',
            title: 'Success!',
            variant: 'success'
          })
        })
        .listen('ItemTipped', (e) => {
          this.$log.debug('Toast Event', { channel, event: 'ItemTipped', e })
          this.popToast(`Tip was successful!`, {
            toaster: 'b-toaster-top-center',
            title: 'Success!',
            variant: 'success'
          })
        })
        .listen('PurchasedFailed', (e) => {
          this.$log.debug('Toast Event', { channel, event: 'PurchasedFailed', e })
          this.popToast(`There was an issue while attempting to complete your purchase.`, {
            toaster: 'b-toaster-top-center',
            title: 'Payment Failure!',
            variant: 'danger'
          })
        })
        .listen('SubscriptionFailed', (e) => {
          this.$log.debug('Toast Event', { channel, event: 'SubscriptionFailed', e })
          this.popToast(`There was an issue while attempting to complete your subscription.`, {
            toaster: 'b-toaster-top-center',
            title: 'Payment Failure!',
            variant: 'danger'
          })
        })
        .listen('TipFailed', (e) => {
          this.$log.debug('Toast Event', { channel, event: 'TipFailed', e })
          this.popToast(`There was an issue while attempting to complete your tip.`, {
            toaster: 'b-toaster-top-center',
            title: 'Payment Failure!',
            variant: 'danger'
          })
        })
    },

    async events() {
      await this.waitFor('session_user')
      const channel = `user.${this.session_user.id}.events`
      this.$echo.private(channel)
        .listen('ItemPurchased', (e) => {
          this.myItemsPurchasedBatch[e.item_type]
            ? this.myItemsPurchasedBatch[e.item_type]++
            : 1
        })
    },

    waitFor(property) {
      return new Promise((resolve, reject) => {
        if (this[property]) {
          resolve()
        } else {
          this.cb[property].push(() => { resolve() })
        }
      })
    },

    popToast(message, options) {
      this.$root.$bvToast.toast(message, options)
    }
  },

  watch: {
    session_user(value) {
      if (value) {
        while(this.cb.session_user.length > 0) {
          this.cb.session_user.pop()()
        }
      }
    },
  },

  render() {
    return this.$slots.default
  },

  mounted() {
    this.$log.debug('Toaster Mounted')
    this.init()
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "errorTitle": "An Error Has Occurred",
    "warningTitle": "Warning"
  }
}
</i18n>
