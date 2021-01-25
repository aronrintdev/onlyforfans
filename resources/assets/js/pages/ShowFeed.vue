<template>
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
</template>

<script>
import Vuex from 'vuex';
import PostFeed from '../components/timelines/PostFeed.vue';
import StoryBar from '../components/timelines/StoryBar.vue';
import SessionBanner from '../components/common/SessionBanner.vue'; // %FIXME: rename, not session
import FollowCtrl from '../components/common/FollowCtrl.vue';

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
  },
}
</script>

<style scoped>
</style>
