<template>
  <b-list-group-item
    :to="to"
    :active="active"
  >

    <section class="d-flex align-items-center">
      <b-avatar :src="participant.avatar.filepath" size="3rem" :alt="participant.name" />
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
        <div class="wrap-msg-snippet">

          <VueMarkdown
            class="msg-snippet mb-0"
            inline :breaks="false"
            :source="chatthread.chatmessages[0].mcontent || ''"
          />
        </div>
        <small>
          <timeago :converterOptions="{addSuffix: false}" :datetime="chatthread.updated_at" :auto-update="60" />
        </small>
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

export default {

  props: {
    active: { type: Boolean, default: false },
    to: null,
    participant: null,
    chatthread: null,
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    isLoading() {
      return !this.session_user || !this.participant || !this.chatthread
    },

  },

  data: () => ({

    moment: moment,

  }), // data

  created() { 
  },

  mounted() { 
  },

  methods: {
  }, // methods

  watch: {
  }, // watch

  components: {
    VueMarkdown,
  },

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
