<template>
  <span v-if="!loading" class="status" :class="[`status-holder-${user.id}`, textVariant]">
    {{ message }}
  </span>
</template>

<script>
import _ from 'lodash'
import { DateTime } from 'luxon'
export default {
  name: 'online-status',
  props: {
    statusTextVariant: { type: Object, default: () => ({
      online: 'success',
      away: 'warning',
      doNotDisturb: 'danger',
    })},
    user: { type: Object, default: () => ({ id: '' }) },
  },
  data: () => ({
    momentsAgoThreshold: 60,
    refreshSpeed: 30 * 1000, // 30 seconds
    refreshLock: false,
    lastSeen: '',
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
      if (this.status === 'offline' && this.lastSeen && this.lastSeen !== '') {
        const lastSeen = DateTime.fromISO(this.lastSeen)
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
      this.$whenAvailable('Echo')
        .then(echo => {
          echo.join(`user.status.${this.user.id}`)
            .here(users => {
              this.$log.debug(`user.status.${this.user.id}.here`, { users })
              if (_.findIndex(users, u => ( u.id == this.user.id )) != -1) {
                this.status = 'online'
              } else {
                setTimeout(this.updateLastSeen, this.refreshSpeed)
              }
              this.loading = false
            })
            .joining(user => {
              this.$log.debug(`user.status.${this.user.id}.joining`,{ user })
              if (user.id == this.user.id) {
                this.status = 'online'
              }
            })
            .leaving(user => {
              this.$log.debug(`user.status.${this.user.id}.leaving`, { users })
              if (user.id == this.user.id) {
                this.status = 'offline'
                this.lastSeen = DateTime.local().toString()
                setTimeout(this.updateLastSeen, this.refreshSpeed)
              }
            })
            .listen('statusUpdate', $e => {
              this.$log.debug(`user.status.${this.user.id}.listen('statusUpdate')`, { $e })
              this.status = $e.status
            })
        })
        .catch(error => {
          this.$log.error(error)
        })
    },
    updateLastSeen(start = true) {
      // Preventing duplicates of this function from running
      if ((start && this.refreshLock) || (!start && !this.refreshLock)) {
        return
      }
      this.refreshLock = true
      if (this.status !== 'offline') {
        this.refreshLock = false
        return
      }
      this.$forceCompute('message')
      setTimeout(() => this.updateLastSeen(false), this.refreshSpeed)
    },
  },
  mounted() {
    if (this.user.last_logged && this.user.last_logged !== '') {
      this.lastSeen = DateTime.fromISO(this.user.last_logged, { zone: 'utc' }).setZone('local').toString()
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
