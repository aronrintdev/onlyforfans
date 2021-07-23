<template>
  <b-alert show variant="success">
    <b-media>
      <template #aside>
        <div class="d-flex flex-column text-center">
          <fa-icon :icon="icon" size="3x" class="mx-auto mb-3" />
          <div class="h2">{{ value.amount | niceCurrency }}</div>
        </div>
      </template>
      <div class="h4" v-text="$t(`title.${userIs}`, tInfo)" />
      <div v-if="value.item_type === 'posts'" class="d-flex">
        <b-btn @click="postShow = true" class="ml-auto">Show Post</b-btn>
        <DisplayPost v-model="postShow" :postId="value.item.id" />
      </div>
      <div v-if="value.message">
        <div class="text-muted" v-text="$t(`message.${userIs}`, tInfo)" />
        <hr class="my-1" />
        <VueMarkdown :source="value.message || ''" />
      </div>
    </b-media>
  </b-alert>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/Attachment/Tip.vue
 */
import Vuex from 'vuex'

/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'
import DisplayPost from '@components/modals/DisplayPost'

export default {
  name: 'Tip',

  components: {
    DisplayPost,
    VueMarkdown,
  },

  props: {
    value: { type: Object, default: () => ({
      item: {},
      receiver: {},
      sender: {},
    }) },
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    icon() {
      return 'envelope-open-dollar'
      // return 'dollar-sign'
    },

    item() {
      return this.value.item
    },

    receiver() {
      return this.value.receiver
    },

    sender() {
      return this.value.sender
    },

    /**
     * information object for localization
     */
    tInfo() {
      return {
        sender: this.sender.name || this.sender.username,
        receiver: this.receiver.name || this.receiver.username,
        amount: this.$options.filters.niceCurrency(this.value.amount),
      }
    },

    userIs() {
      if (this.receiver.id === this.session_user.id) {
        return 'receiver'
      }
      if (this.sender.id === this.session_user.id) {
        return 'sender'
      }
      return 'other'
    },
  },

  data: () => ({
    postShow: false,
  }),

  methods: {},

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": {
      "other": "{sender} has sent {receiver} a tip!",
      "receiver": "{sender} has sent you a tip!",
      "sender": "You have sent {receiver} a tip!"
    },
    "message": {
      "other": "Message from {sender}:",
      "receiver": "Message from {sender}:",
      "sender": "Your message to {receiver}:"
    }
  }
}
</i18n>
