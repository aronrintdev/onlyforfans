<template>
  <b-dropdown id="ctrls-participants" variant="link" size="sm" class="ml-auto" right no-caret>
    <template #button-content>
      <fa-icon :icon="['fas', 'ellipsis-h']" class="fa-sm" />
    </template>

    <b-dropdown-item @click="$emit('tip')">
      <fa-icon icon="dollar-sign" fixed-width />
      {{ $t('tip', { name: participant.username }) }}
    </b-dropdown-item>

    <b-dropdown-item @click="$emit('addNotes')">
      <fa-icon icon="pencil" fixed-width />
      {{ $t('addNotes') }}
    </b-dropdown-item>

    <b-dropdown-item @click="$emit('rename')">
      <fa-icon icon="edit" fixed-width />
      {{ $t('rename', { name: participant.username }) }}
    </b-dropdown-item>
    <b-dropdown-item @click="$emit('deleteThread')">
      <fa-icon icon="trash-alt" fixed-width />
      {{ $t('deleteThread') }}
    </b-dropdown-item>

    <b-dropdown-divider />

    <b-dropdown-item @click="$emit('toggleFavorite')">
      <fa-icon :icon="favorited ? ['fas', 'star'] : ['far', 'star']" fixed-width />
      {{ favorited ? $t('unfavorite') : $t('favorite') }}
    </b-dropdown-item>
    <b-dropdown-item @click="$emit('toggleMute')">
      <fa-icon :icon="muted ? ['far', 'bell-slash'] : ['far', 'bell']" fixed-width />
      {{ muted ? $t('notificationsUnMute') : $t('notificationsMute') }}
    </b-dropdown-item>

    <b-dropdown-divider />

    <b-dropdown-item @click="$emit('restrict')">
      <fa-icon icon="times-circle" fixed-width />
      {{ $t('restrict', { name: participant.username }) }}
    </b-dropdown-item>
    <b-dropdown-item disabled @click="$emit('block')">
      <fa-icon icon="ban" fixed-width />
      {{ $t('block', { name: participant.username }) }}
    </b-dropdown-item>
    <b-dropdown-item disabled @click="$emit('report')">
      <fa-icon icon="flag" fixed-width />
      {{ $t('report', { name: participant.username }) }}
    </b-dropdown-item>
  </b-dropdown>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/ShowThread/OptionsDropdown.vue
 */
import Vuex from 'vuex'

export default {
  name: 'OptionsDropdown',

  components: {},

  props: {
    favorited: { type: Boolean, default: false },
    muted: { type: Boolean, default: false },
    participant: { type: Object, default: () => ({}) },
  },

  computed: {},

  data: () => ({}),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
::v-deep .dropdown-item {
  padding-left: 1rem;
  .fa-fw {
    margin-right: 0.5rem;
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "tip": "Send @{name} a tip",
    "addNotes": "Add Notes",
    "deleteThread": "Delete conversation",
    "rename": "Rename alias for @{name}",
    "favorite": "Favorite Chat Thread",
    "unfavorite": "Unfavorite Chat Thread",
    "notificationsMute": "Mute Notifications",
    "notificationsUnMute": "Unmute Notifications",
    "restrict": "Restrict @{name}",
    "block": "Block @{name}",
    "report": "Report @{name}",
  }
}
</i18n>
