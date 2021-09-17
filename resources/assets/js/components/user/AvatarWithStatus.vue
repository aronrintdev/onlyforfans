<template>
  <OnlineStatus :user="user" :timeline="timeline" v-slot="slotProps" :textVisible="textVisible">
    <b-media :class="centerAvatar ? 'force-center' : ''" class="position-relative">
      <template #aside>
        <Avatar :user="user" :timeline="timeline" :noLink="noLink" :size="size" :thumbnail="thumbnail" class="position-relative">
          <template #append>
            <StatusDot
              v-if="noTooltip"
              :variant="dotVariant(slotProps.status)"
              class="online-dot"
              :title="slotProps.message"
            />
            <StatusDot
              v-else
              :variant="dotVariant(slotProps.status)"
              class="online-dot"
              v-b-tooltip:hover
              :title="slotProps.message"
              :size="size"
            />
          </template>
        </Avatar>
      </template>
      <div v-if="!imageOnly">
        <div class="h5 mb-0" :class="noLink ? 'no-clickable' : 'clickable'" @click="handleClickUsername">
          {{ name }}
          <div v-if="user.is_verified" class="d-inline" v-b-tooltip:hover :title="$t('tooltip.verified')">
            <fa-icon icon="check-circle" fixed-width class="text-primary" />
          </div>
        </div>
        <div v-if="size === 'md' || size === 'lg'" :class="noLink ? 'no-clickable' : 'clickable'" class="text-secondary" @click="handleClickUsername">
          @{{ handle }}
        </div>
        <div v-if="textVisible" :class="`text-${textVariant(slotProps.status)}`" v-text="slotProps.message" />
      </div>
    </b-media>
  </OnlineStatus>
</template>

<script>
/**
 * resources/assets/js/components/user/AvatarWithStatus.vue
 */
import OnlineStatus from '@components/common/OnlineStatus'

import Avatar from './Avatar'
import StatusDot from './StatusDot'

export default {
  name: 'AvatarWithStatus',

  components: {
    Avatar,
    OnlineStatus,
    StatusDot,
  },

  props: {
    centerAvatar: { type: Boolean, default: false },
    imageOnly: { type: Boolean, default: false },
    noLink: { type: Boolean, default: false },
    noTooltip: { type: Boolean, default: false },
    size: { type: String, default: 'sm' },
    user: { type: Object, default: () => ({}) },
    timeline: { type: Object, default: () => ({}) },
    thumbnail: { type: Boolean, default: true },
    textVisible: { type: Boolean, default: true },
  },

  computed: {
    name() {
      if (this.timeline && this.timeline.name) {
        return this.timeline.name
      }
      if (this.user) { // Fallback
        if (this.user.timeline && this.user.timeline.name) {
          return this.user.timeline.name
        }
        return this.user.name || this.user.username
      }
      return ''
    },
    handle() {
      if (this.timeline && this.timeline.slug) {
        return this.timeline.slug
      }
      if (this.user) { // Fallback
        if (this.user.timeline && this.user.timeline.slug) {
          return this.user.timeline.slug
        }
        return this.user.username
      }
      return ''
    },

  },

  data: () => ({}),

  methods: {
    dotVariant(status) {
      switch (status) {
        case 'online': return 'success'
        case 'away': return 'warning'
        case 'doNotDisturb': return 'danger'
        case 'offline': default: return 'muted'
      }
    },
    textVariant(status) {
      switch (status) {
        case 'online': return 'success'
        case 'away': return 'warning'
        case 'doNotDisturb': return 'danger'
        case 'offline': default: return 'muted'
      }
    },
    handleClickUsername() {
      this.$router.push({ name: 'timeline.show', params: { slug: this.user.timeline ? this.user.timeline.slug : this.user.slug } })
    }
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
$spacer: 1rem;
.online-dot {
  position: absolute;
  bottom: 0;
  right: 0;
}
.media.force-center {
  .media-aside {
    margin-right: 0 !important;
  }
}
.clickable {
  cursor: pointer;
}
.no-clickable {
  pointer-events: none;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "tooltip": {
      "verified": "This user is a verified creator"
    }
  }
}
</i18n>
