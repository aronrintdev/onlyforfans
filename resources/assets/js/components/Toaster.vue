<script>
/**
 * Responsible for listening on event channels and popping toast to the user on Events.
 */
export default {
  name: 'Toaster',

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
        name: (user) => ( `user.${user.id}.events` ),
        events: [
          {
            name: 'ItemPurchased',
            batch: true,
            handler: (e, dataStore = {}) => {
              dataStore.purchases[e.item_type][e.item_id]++
            },
            batchHandler: (dataStore) => {
              //
            }
          }
        ],
      },
    ],

    active: {},
  }),

  methods: {
    /**
     * Initialize listeners
     */
    init() {},

    listenTo(channel) {},

    popToast(message, options) {
      this.$root.$bvToast.toast(message, options,)
    }
  },

  watch() {},

  mounted() {
    this.$log.debug('Toaster Mounted')
    this.init()
  },
}
</script>

<style lang="scss" scoped>

</style>