<script>
/**
 * Listens for websocket events and issues event bus events
 */
import { eventBus } from '@/app'
import Vuex from 'vuex'
export default {
  name: 'EventUpdater',

  computed: {
    ...Vuex.mapState([ 'session_user' ]),
  },

  data: () => ({
    // Call backs if waiting on property to load
    cb: {
      'session_user': [],
    },
  }),

  methods: {
    ...Vuex.mapActions([
      'payments/updateSavedPaymentMethods',
    ]),

    init() {
      this.purchases()
      this.events()
    },

    async events() {
      await this.waitFor('session_user')
      const channel = `user.${this.session_user.id}.events`
      this.$echo.private(channel)
        .listen('PaymentMethodAdded', e => {
          this.$log.debug('Event', { channel, event: 'PaymentMethodAdded', e })
          this['payments/updateSavedPaymentMethods']()
        })
    },

    async purchases() {
      await this.waitFor('session_user')
      const channel = `user.${this.session_user.id}.purchases`
      this.$echo.private(channel)
        .listen('ItemPurchased', (e) => {
          this.$log.debug('Event', { channel, event: 'ItemPurchased', e })
          eventBus.$emit(`update-${e.item_type}`, e.item_id)
        })
        .listen('ItemSubscribed', (e) => {
          this.$log.debug('Event', { channel, event: 'ItemSubscribed', e })
          eventBus.$emit(`update-${e.item_type}`, e.item_id)
        })
        .listen('ItemTipped', (e) => {
          this.$log.debug('Event', { channel, event: 'ItemTipped', e })
          eventBus.$emit(`update-${e.item_type}`, e.item_id)
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
  },

  watch: {
    session_user(value) {
      if (value) {
        while(this.cb.session_user.length > 0) {
          this.cb.session_user.pop()()
        }
      }
    }
  },

  render() {
    return this.$slots.default
  },

  mounted() {
    this.init()
  },
}
</script>
