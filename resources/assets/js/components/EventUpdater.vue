<script>
/**
 * Listens for websocket events and issues event bus events
 */
import { eventBus } from '@/app'
export default {
  name: 'EventUpdater',

  data: () => ({
    channels: [
      {
        id: 'purchases',
        nameParams: [ 'session_user' ],
        name: (user) => ( `user.${user.id}.purchases` ),
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
  }),

  methods: {
    init() {},
    addChannel() {},
  },

  mounted() {
    this.init()
  },
}
</script>

<style lang="scss" scoped>

</style>