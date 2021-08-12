<template>
  <div class="onlineStatus">
    <slot :loading="loading" :status="status" :textVariant="textVariant" :message="message" :lastSeen="lastSeen">
      <div v-if="indicatorVisible" class="status-indicator" :class="{'online-status': status === 'online'}" :style="{ height: s, width: s }" v-b-tooltip.hover.bottom :title="message" />
      <span v-if="!loading && textInvisible" class="status" :class="[`status-holder-${user.id}`, textVariant]">
      {{ message }}
      </span>
    </slot>
  </div>
</template>

<script>
import _ from 'lodash'
import { DateTime } from 'luxon'
import { faUserAltSlash, faUserAstronaut } from '@fortawesome/free-solid-svg-icons'
export default {
  name: 'OnlineStatus',
  props: {
    statusTextVariant: { type: Object, default: () => ({
      online: 'success',
      away: 'warning',
      doNotDisturb: 'danger',
    })},
    user: { type: Object, default: () => ({ id: '' }) },
    indicatorVisible: { default: true, type: Boolean },
    textInvisible: { default: true, type: Boolean },
    size: { type: String, default: 'sm' },
  },
  data: () => ({
    channel: null,
    momentsAgoThreshold: 60,
    offlineDelay: 10 * 1000, // 10 seconds, amount of reconnect leeway
    pendingOffline: false, // In offline gray area
    refreshSpeed: 30 * 1000, // 30 seconds
    refreshLock: false,
    lastSeen: '',
    loading: false,
    /**
     * offline | online | away | doNotDisturb
     */
    status: 'offline',
  }),
  computed: {
    s() {
      switch (this.size) {
        case 'sm': return '10px'
        case 'lg': return '16px'
      }
    },
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
      this.channel = this.$echo.join(`user.status.${this.user.id}`)
        .here(users => {
          this.$log.debug(`user.status.${this.user.id}.here`, { users })
          this.checkStatus(users)
          this.loading = false
        })
        .joining(user => {
          this.$log.debug(`user.status.${this.user.id}.joining`,{ user })
          if (user.id == this.user.id) {
            this.pendingOffline = false
            this.status = user.status || 'online'
          }
        })
        .leaving(user => {
          this.$log.debug(`user.status.${this.user.id}.leaving`, { user })
          if (user.id == this.user.id) {
            this._pendingOffline()
          }
        })
        .listen('statusUpdate', $e => {
          this.$log.debug(`user.status.${this.user.id}.listen('statusUpdate')`, { $e })
          this.status = $e.status
        })
      if (this.channel.subscription && this.channel.subscription.members) {
        // members structure from echo/pusher:
        // this.channel.subscription.members == {
        //  count,   // Number of connected user
        //  me,      // my id object
        //  members, // connected users objects keyed by id
        //  myID,    // my id
        // }
        this.checkStatus(this.channel.subscription.members.members)
        this.loading = false
      }
    },

    checkStatus(members) {
      //this.$log.debug('OnlineStatus checkStatus', { members })
      const user = _.find(members, u => ( u.id == this.user.id ))
      if (user) {
        this.pendingOffline = false
        this.status = user.status || 'online'
      } else {
        setTimeout(this.updateLastSeen, this.refreshSpeed)
      }
    },

    _pendingOffline() {
      this.pendingOffline = true
      setTimeout(() => {
        if (this.pendingOffline) {
          this.status = 'offline'
          this.lastSeen = DateTime.local().toString()
          setTimeout(this.updateLastSeen, this.refreshSpeed)
        }
      }, this.offlineDelay)
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
  created() {
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

<style scoped>
.onlineStatus {
  display: flex;
}
.status-indicator {
  margin-top: 6px;
  margin-right: 5px;
  border: solid 1px #fff;
  border-radius: 100%;
  background: gray;
}
.status-indicator .online-status {
  background: green;
}
</style>
