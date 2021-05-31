<template>
  <div>

    <section class="d-flex align-items-center">
      <b-avatar :src="user.avatar.filepath" :alt="user.name" />
      <div class="pl-2">
        <h5 class="my-0">{{ user.username }}</h5>
        <p class="msg-snippet mb-0 text-truncate">{{ chatthread.chatmessages[0].mcontent || '' }}</p>
        <div class="msg-count">({{ chatthread.msg_count }})</div>
      </div>
      <div class="pl-2 tag-ctrl ml-auto">
        <fa-icon :icon="['fas', 'ellipsis-h']" class="clickable OFF-fa-lg" />
        <div>10d</div>
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
  width: 12rem;
  font-size: 14px;
}
.msg-count {
  font-size: 12px;
  text-style: italic;
}
</style>

<style lang="scss">
</style>
