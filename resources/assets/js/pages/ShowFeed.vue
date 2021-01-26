<template>
  <div>

    <div class="container" id="view-show_timeline">

      <section class="row">
        <article class="col-sm-12">
          <session-banner :session_user="session_user" :timeline="timeline"></session-banner>
        </article>
      </section>

      <section class="row">

        <aside class="col-md-5 col-lg-4">
          <follow-ctrl :session_user="session_user" :timeline="timeline"></follow-ctrl>
        </aside>

        <main class="col-md-7 col-lg-8">
          <post-feed :session_user="session_user" :timeline="timeline"></post-feed>
        </main>

      </section>

    </div>

    <b-modal id="modal-send_tip" title="Send a Tip" hide-footer body-class="p-0">
      <send-tip :session_user="session_user" :timeline="timeline"></send-tip>
    </b-modal>

    <b-modal id="modal-follow" title="Follow" ok-only>
      <p class="my-4">Follow Modal</p>
    </b-modal>

    <b-toast id="example-toast" title="BootstrapVue" static no-auto-hide>
      Hello, world! This is a toast message.
    </b-toast>

  </div>
</template>

<script>
import Vuex from 'vuex';
import PostFeed from '../components/timelines/PostFeed.vue';
import StoryBar from '../components/timelines/StoryBar.vue';
import SessionBanner from '../components/common/SessionBanner.vue'; // %FIXME: rename, not session
import FollowCtrl from '../components/common/FollowCtrl.vue';
import SendTip from '../components/modals/SendTip.vue';

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
  },

  data: () => ({
  }),

  created() {
  },

  methods: {
  },

  watch: {
  },

  components: {
    PostFeed,
    StoryBar,
    SessionBanner,
    FollowCtrl,
    SendTip,
  },
}
</script>

<style scoped>
</style>
