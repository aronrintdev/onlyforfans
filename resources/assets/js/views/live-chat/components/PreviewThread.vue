<template>
  <div>

    <section class="d-flex align-items-center">
      <b-avatar :src="user.avatar.filepath" size="3rem" :alt="user.name" />
      <div class="pl-2">
        <p class="my-0">
          <span><strong>{{ user.username }}</strong></span>
          <span class="msg-count"><em><small>({{ chatthread.msg_count }})</small></em></span>
        </p>
        <p class="msg-snippet mb-0 text-truncate">{{ chatthread.chatmessages[0].mcontent || '' }}</p>
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
    user: null,
    chatthread: null,
  },

  computed: {

    isLoading() {
      return !this.session_user || !this.user || !this.chatthread
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
.msg-snippet {
  width: 15rem;
  font-size: 14px;
}
</style>

<style lang="scss">
</style>
