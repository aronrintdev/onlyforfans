<template>
  <b-list-group-item
    v-if="participant"
    :to="to"
    :active="active"
  >

    <section class="d-flex align-items-center">
      <AvatarWithStatus :timeline="participant.timeline" :user="participant" :thumbnail="false" imageOnly />
      <div class="participant-info pl-2">
        <div class="d-flex my-0">
          <span class="msg-username" v-bind:class="{ 'tag-unread': chatthread.unread_count }">
            {{ participant.username }}
          </span>
          <span v-if="chatthread.unread_count" class="msg-count">
            <em>
              <small>({{ chatthread.unread_count }})</small>
            </em>
          </span>
          <span v-if="chatthread.has_subscriber" class="ml-auto">
            <b-badge variant="info">
              Subscriber
            </b-badge>
          </span>
        </div>
        <div class="wrap-msg-snippet d-flex">

          <VueMarkdown
            class="msg-snippet mb-0 flex-grow-1"
            inline
            :breaks="false"
            :html="false"
            :linkify="false"
            :source="lastMessageContent"
          />
          <small class="ml-auto mr-0">
            <timeago v-if="hasMessages" :converterOptions="{addSuffix: false}" :datetime="lastMessageTime" :auto-update="60" />
          </small>
        </div>
      </div>
      <div class="pl-2 tag-ctrl ml-auto">
        <fa-icon :icon="['fas', 'ellipsis-h']" class="clickable fa-sm" />
      </div>
    </section>

  </b-list-group-item>
</template>

<script>
import Vuex from 'vuex'
import moment from 'moment'
/** https://github.com/adapttive/vue-markdown/ */
import VueMarkdown from '@adapttive/vue-markdown'

import AvatarWithStatus from '@components/user/AvatarWithStatus'

export default {
  name: 'Thread',

  components: {
    AvatarWithStatus,
    VueMarkdown,
  },

  props: {
    active: { type: Boolean, default: false },
    to: null,
    participant: null,
    chatthread: null,
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    lastMessageContent() {
      if (this.chatthread && this.chatthread.chatmessages[0]) {
        return this.chatthread.chatmessages[0].mcontent || ''
      }
      return ''
    },

    isLoading() {
      return !this.session_user || !this.participant || !this.chatthread
    },

    hasMessages() {
      if (this.chatthread.chatmessages.length) {
        return true
      } else {
        return false
      }
    },

    lastMessageTime() {
      if (this.chatthread.chatmessages.length) {
        return this.chatthread.chatmessages[0].updated_at
      }
    }

  },

  data: () => ({
    moment: moment,
  }), // data

  created() {},

  mounted() {},

  methods: {
  }, // methods

  watch: {
  }, // watch

}
</script>

<style lang="scss" scoped>
body {
  .btn-link:hover {
    text-decoration: none;
  }
  .btn:focus, .btn.focus {
    box-shadow: none;
  }
  .msg-username.tag-unread {
    font-weight: bold;
  }
}
.participant-info {
  width: calc(100% - 70px);
  overflow: hidden;

  .msg-snippet {
    display: block;
    max-width: 9rem;
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
    margin-right: 10px;
    word-wrap: break-word;
  }
}
</style>
