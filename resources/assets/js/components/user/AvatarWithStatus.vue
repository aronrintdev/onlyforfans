<template>
  <OnlineStatus :user="user" v-slot="slotProps">
    <b-media>
      <template #aside>
        <Avatar :user="user" :noLink="noLink" :size="size" :thumbnail="thumbnail" class="position-relative">
          <template #append>
            <StatusDot
              :variant="dotVariant(slotProps.status)"
              class="online-dot"
              v-b-tooltip:hover
              :title="slotProps.message"
            />
          </template>
        </Avatar>
      </template>
      <div v-if="!imageOnly">
        <div class="h5 mb-0">
          {{ user.username }}
          <div v-if="user.is_verified" class="d-inline" v-b-tooltip:hover :title="$t('tooltip.verified')">
            <fa-icon icon="check-circle" fixed-width class="text-success" />
          </div>
        </div>
        <div :class="`text-${textVariant(slotProps.status)}`" v-text="slotProps.message" />
      </div>
    </b-media>
  </OnlineStatus>
</template>

<script>
/**
 * resources/assets/js/components/user/AvatarWithStatus.vue
 */
import Vuex from 'vuex'

import Avatar from './Avatar'
import OnlineStatus from './OnlineStatus'
import StatusDot from './StatusDot'

export default {
  name: 'AvatarWithStatus',

  components: {
    Avatar,
    OnlineStatus,
    StatusDot,
  },

  props: {
    imageOnly: { type: Boolean, default: false },
    noLink: { type: Boolean, default: false },
    size: { type: String, default: 'sm' },
    user: { type: Object, default: () => ({}) },
    thumbnail: { type: Boolean, default: true },
  },

  computed: {},

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
  top: 0;
  right: 0;
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
