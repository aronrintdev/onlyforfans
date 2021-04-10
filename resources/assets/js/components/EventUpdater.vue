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
    channelDefs: [
      {
        id: 'purchases',
        nameParams: [ 'session_user' ],
        name: ([ user ]) => ( `user.${user.id}.purchases` ),
        events: [
          {
            name: 'ItemPurchased',
            handler: (e) => {
              if (e.purchaser_id === this.session_user.id) {
                eventBus.$emit(`update-${e.item_type}`, e.item_id)
              }
            },
          }
        ],
      },
    ],
    channels: {},
    // Call backs if waiting on property to load
    cb: {
      'session_user': [],
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
      this.$log.debug(`EventUpdater Channel ${channel.id} was added on channel ${name}`)
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
