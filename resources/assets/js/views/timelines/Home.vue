<template>
    <div class="container" id="view-home_timeline">

      <section class="row" v-if="state !== 'loading'">
        <article class="col-sm-12">
          <story-bar :session_user="session_user" :timeline="timeline"></story-bar>
        </article>
      </section>

      <section class="row" v-if="state !== 'loading'">

        <main class="col-md-7 col-lg-8">
          <create-post :session_user="session_user" :timeline="timeline"></create-post>
          <post-feed :session_user="session_user" :timeline="timeline"></post-feed>
        </main>

        <aside class="col-md-5 col-lg-4">
          <session-widget :session_user="session_user" :timeline="timeline"></session-widget>
          <suggested-feed :session_user="session_user" :timeline="timeline" class="mt-3"></suggested-feed>
        </aside>

      </section>

    </div>
</template>

<script>
/**
 * Timelines Home Page
 */
import Vuex from 'vuex';
import PostFeed from '@components/timelines/PostFeed.vue';
import StoryBar from '@components/timelines/StoryBar.vue';
import CreatePost from '@components/timelines/CreatePost.vue';
import SessionWidget from '@components/common/SessionWidget.vue'; // %FIXME: rename, not session
import SuggestedFeed from '@components/common/SuggestedFeed.vue';

export default {

  // props: {
  //   session_user: null,
  //   timeline: null,
  // },

  computed: {
    ...Vuex.mapGetters(['session_user', 'timeline']),
  },

  data: () => ({
    state: 'loading', // loading | loaded
  }),

  created() {
  },

  mounted() {
    if (!this.session_user || !this.timeline) {
      this.getMe()
    } else {
      this.state = 'loaded'
    }
  },

  methods: {
    ...Vuex.mapActions([ 'getMe' ]),
  },

  watch: {
    timeline(value) {
      if (value) {
        this.state = 'loaded'
      }
    }
  },

  components: {
    PostFeed,
    StoryBar,
    CreatePost,
    SessionWidget,
    SuggestedFeed,
  },
}
</script>

<style scoped>
</style>
