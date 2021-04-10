<script>
/**
 * Responsible for listening on event channels and popping toast to the user on Events.
 */
import Vuex from 'vuex'

export default {
  name: 'Toaster',

  computed: {
    ...Vuex.mapState([ 'session_user' ]),
  },

  data: () => ({
    channelDefs: [
      {
        id: 'purchases',
        nameParams: [ 'session_user' ],
        name: ([user]) => ( `user.${user.id}.purchases` ),
        events: [
          {
            name: 'ItemPurchased',
            handler: (e) => {
              this.popToast(`Post successfully purchased!`, {
                toaster: 'b-toaster-top-center',
                title: 'Success!',
              })
            },
          }
        ],
      },
      {
        id: 'events',
        nameParams: [ 'session_user' ],
        name: ([user]) => ( `user.${user.id}.events` ),
        events: [
          {
            name: 'ItemPurchased',
            batch: true,
            handler: (e, dataStore = {}) => {
              if (!dataStore.purchases) {
                dataStore.purchases = {}
              }
              if (!dataStore.purchases[e.item_type]) {
                dataStore.purchases[e.item_type] = 0
              }
              dataStore.purchases[e.item_type]++
            },
            batchHandler: (dataStore) => {
              //
            }
          }
        ],
      },
    ],

    channels: {},
    cb: {
      'session_user': []
    },
  }),

  methods: {
    init() {
      for (var channel of this.channelDefs ) {
        this.addChannel(channel)
      }
    },

    async addChannel(channel) {
      if (channel.nameParams) {
        await Promise.all(channel.nameParams.map(param => ( this.waitFor(param) )))
      }
      var name = channel.name
      if (typeof name === 'function') {
        if (channel.nameParams) {
          name = name(channel.nameParams.map(param => ( this[param] )))
        } else {
          name = name()
        }
      }
      var echoChannel = this.$echo.join(name)
      if ( !this.channels[channel.id] ) {
        this.channels[channel.id] = {}
      }
      for(var event of channel.events) {
        if (event.batch) {
          if (!this.channels[channel.id].events) {
            this.channels[channel.id].events = {}
          }
          if (!this.channels[channel.id].events[event.name]) {
            this.channels[channel.id].events[event.name] = { data: {} }
          }
          echoChannel.listen(event.name, (e) => event.handler(e, this.channels[channel.id].events[event.name].data ))
        } else {
          echoChannel.listen(event.name, event.handler)
        }
      }
      this.channels[channel.id] = {...this.channels[channel.id], echo: echoChannel }
      this.$log.debug(`Toaster Channel ${channel.id} was added on channel ${name}`)
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

<style lang="scss" scoped>

</style>