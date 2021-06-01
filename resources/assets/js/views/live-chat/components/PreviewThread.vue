<template>
  <div>

    <section class="d-flex align-items-center">
      <b-avatar :src="participant.avatar.filepath" size="3rem" :alt="participant.name" />
      <div class="participant-info pl-2">
        <p class="my-0">
          <span><strong>{{ participant.username }}</strong></span>
          <span class="msg-count"><em><small>({{ chatthread.msg_count }})</small></em></span>
        </p>
        <div class="wrap-msg-snippet">
          <span class="msg-snippet mb-0 OFF-text-truncate">{{ chatthread.chatmessages[0].mcontent || '' }}</span>
        </div>
      </div>
      <div class="pl-2 tag-ctrl ml-auto">
        <fa-icon :icon="['fas', 'ellipsis-h']" class="clickable fa-sm" />
        <div><small>10d</small></div>
      </div>
    </section>

  </div>
</template>

<script>
//import Vuex from 'vuex'
import moment from 'moment'

export default {

  props: {
    session_user: null,
    participant: null,
    chatthread: null,
  },

  computed: {

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

  components: { },

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

<style lang="scss">
</style>
