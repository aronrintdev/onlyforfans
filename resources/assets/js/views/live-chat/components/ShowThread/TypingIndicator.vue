<template>
  <div>
    <span v-if="isTyping" class="text-muted">
      <fa-icon icon="ellipsis-h" class="pulse" />
      <span v-text="$t('isTyping', { name })" />
    </span>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/ShowThread/TypingIndicator.vue
 */
import Vuex from 'vuex'
import _ from 'lodash'

export default {
  name: 'TypingIndicator',

  components: {},

  props: {
    threadId: { type: String, default: '' },
  },

  computed: {
    channelName() {
      return `chatthreads.${this.threadId}`
    }
  },

  data: () => ({
    name: '',
    isTyping: false,
    debounceAmount: 1500, // 1.5s
  }),

  methods: {
    init() {
      this.$echo.join(this.channelName)
        .here(users => {
          this.$log.debug(`TypingIndicator joined ${this.channelName}`, { channelName: this.channelName, users })
        })
        .listenForWhisper('typing', e => {
          this.$log.debug('TypingIndicator hears typing')
          this.name = e.name
          this.isTyping = true
          this.endTyping()
        })
        .listenForWhisper('sendMessage', e => {
          this.isTyping = false
        })
    },

    _endTyping() {
      this.isTyping = false
    }

  },

  watchers: {},

  created() {
    this.init()
    this.endTyping = _.debounce(this._endTyping, this.debounceAmount)
  },
}
</script>

<style lang="scss" scoped>
.pulse {
  animation:pulse .8s ease-in-out infinite
}
@keyframes pulse
{
  0%{
    transform:scale(1)
  }
  50% {
    transform:scale(0.8)
  }
  100% {
    transform:scale(1)
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "isTyping": "{name} is typing"
  }
}
</i18n>
