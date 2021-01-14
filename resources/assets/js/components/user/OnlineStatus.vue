<template>
  <span v-if="!loading" class="status" :class="[`status-holder-${userId}`, textVariant]">
    {{ message }}
  </span>
</template>

<script>
import _ from 'lodash'
import { DateTime } from 'luxon'
export default {
  name: 'online-status',
  props: {
    lastSeen: { type: String, default: ''},
    statusTextVariant: { type: Object, default: () => ({
      online: 'success',
      away: 'warning',
      doNotDisturb: 'danger',
    })},
    userId: { type: [String, Number] },
  },
  data: () => ({
    momentsAgoThreshold: 60,
    refreshSpeed: 30 * 1000, // 30 seconds
    refreshRunning: false,
    _lastSeen: '',
    loading: true,
    /**
     * offline | online | away | doNotDisturb
     */
    status: 'offline',
  }),
  computed: {
    textVariant() {
      if (this.statusTextVariant[this.status]) {
        return `text-${this.statusTextVariant[this.status]}`
      }
      return ''
    },
    message() {
      if (this.status === 'offline' && this._lastSeen && this._lastSeen !== '') {
        const lastSeen = DateTime.fromISO(this._lastSeen)
        if (DateTime.local().diff(lastSeen, 'seconds').toObject().seconds < this.momentsAgoThreshold) {
          return this.$t('lastSeenMomentsAgo')
        }
        return this.$t('lastSeen', { relativeTime: lastSeen.toRelative() })
      }
      return this.$t(this.status);
    },
  },
  methods: {
    listen() {
      this.loading = true
      window.Echo.join(`user.status.${this.userId}`)
        .here((users) => {
          if (_.findIndex(users, u => ( u.id == this.userId )) != -1) {
            this.status = 'online'
          } else {
            setTimeout(this.updateLastSeen, this.refreshSpeed)
          }
          this.loading = false
        })
        .joining((user) => {
          if (user.id == this.userId) {
            this.status = 'online'
          }
        })
        .leaving((user) => {
          if (user.id == this.userId) {
            this.status = 'offline'
            this._lastSeen = DateTime.local().toString()
            setTimeout(this.updateLastSeen, this.refreshSpeed)
          }
        })
        .listen('statusUpdate', ($e) => {
          this.status = $e.status
        })
    },
    updateLastSeen(start = true) {
      // Preventing duplicates of this function from running
      if (start && this.refreshRunning) {
        return
      }
      this.refreshRunning = true
      if (this.status !== 'offline') {
        this.refreshRunning = false
        return
      }
      this.$forceCompute('message')
      setTimeout(() => this.updateLastSeen(false), this.refreshSpeed)
    },
  },
  mounted() {
    if (this.lastSeen && this.lastSeen !== '') {
      this._lastSeen = DateTime.fromISO(this.lastSeen, { zone: 'utc' }).setZone('local').toString()
    }
    this.listen()
  }
}
</script>

<i18n lang="json5">
{
  "en": {
    "online": "Online Now",
    "away": "Away",
    "doNotDisturb": "Do Not Disturb",
    "offline": "Offline",
    "lastSeen": "Last seen {relativeTime}",
    "lastSeenMomentsAgo": "Last seen moments ago"
  }
}
</i18n>
