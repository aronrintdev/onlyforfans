<template>
  <span class="status" :class="[`status-holder-${userId}`, textVariant]">
    {{ message }}
  </span>
</template>

<script>
import _ from 'lodash'
import { DateTime } from 'luxon'
export default {
  name: 'online-status',
  props: {
    userId: { type: string },
  },
  data: () => ({
    status: 'offline',
    lastSeen: '',
  }),
  computed: {
    textVariant() {
      switch(this.status) {
        case 'online': return 'text-success'
        case 'away': return 'text-warning'
        case 'doNotDisturb': return 'text-danger'
        case 'offline': default: return ''
      }
    },
    message() {
      // TODO: Add Localization
      switch(this.status) {
        case 'online': return 'Online Now'
        case 'away': return 'Away'
        case 'doNotDisturb': return 'Do Not Disturb'
        case 'offline':
          return this.lastSeen ?
            `Last seen ${ DateTime(this.lastSeen).toRelative({ base: DateTime }) } ago` :
            'Offline'
      }
    },
  },
  methods: {
    listen() {
      window.Echo.join(`user.status.${this.userId}`)
        .here((users) => {
          if (_.findIndex(user, ['id', this.userId]) != -1) {
            this.status = 'online'
          }
        })
        .joining((user) => {
          if (user.id === this.userId) {
            this.status = 'online'
          }
        })
        .leaving((user) => {
          if (user.id === this.userId) {
            this.status = 'offline'
            this.lastSeen = DateTime()
          }
        })
        .listen('statusUpdate', ($e) => {
          this.status = $e.status
        })
    }
  },
  mounted() {
    this.listen()
  }
}
</script>

<style lang="scss" scoped>

</style>
