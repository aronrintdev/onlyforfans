<template>
  <b-alert show variant="secondary" class="tag d-flex justify-content-start align-items-center">
    <div>
      <fa-icon :icon="['fas', 'calendar-alt']" size="lg" class="mx-2" fixed-width />
      <span >{{ $t('message') }}</span>
      <strong>{{ moment(value).local().format('MMM DD, h:mm a') }}</strong>
    </div>
    <b-btn variant="link" class="p-0 ml-2" @click="$emit('clear')">
      <fa-icon :icon="['fas', 'times']" class="icon cursor-pointer fa-lg" fixed-width />
    </b-btn>
  </b-alert>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/NewMessageForm/ScheduledAtDisplay.vue
 */
import moment from 'moment'

export default {
  name: 'ScheduledAtDisplay',

  components: {},

  props: {
    value: { type: [String, Object, Number], default: '' },
  },

  computed: {},

  data: () => ({
    moment: moment,
  }),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.tag {
  min-height: 2.25rem;
  padding: var(--spacer-1, 0.25rem);
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "message": "Scheduled for"
  }
}
</i18n>
