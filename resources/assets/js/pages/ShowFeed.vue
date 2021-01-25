<template>
    <div class="container" id="view-show_timeline">

      <section class="row">
        <article class="col-sm-12">
          <story-bar :session_user="session_user" :timeline="timeline"></story-bar>
        </article>
      </section>

      <section class="row">

        <main class="col-md-7 col-lg-8">
          <create-post v-if="isOwner" :session_user="session_user" :timeline="timeline"></create-post>
          <post-feed :session_user="session_user" :timeline="timeline"></post-feed>
        </main>

        <aside v-if="isOwner" class="col-md-5 col-lg-4">
          <session-widget :session_user="session_user" :timeline="timeline"></session-widget>
          <suggested-feed :session_user="session_user" :timeline="timeline" class="mt-3"></suggested-feed>
        </aside>

      </section>

    </div>
</template>

<script>
import Vuex from 'vuex';
import PostFeed from '../components/timelines/PostFeed.vue';
import StoryBar from '../components/timelines/StoryBar.vue';
import CreatePost from '../components/timelines/CreatePost.vue';
import SessionWidget from '../components/common/SessionWidget.vue'; // %FIXME: rename, not session
import SuggestedFeed from '../components/common/SuggestedFeed.vue';

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    //...Vuex.mapState(['session_user']),
    //...Vuex.mapState(['is_loading']),

    isOwner() {
      return this.session_user.id === this.timeline.user.id;
    },
  },

  data: () => ({
  }),

  created() {
    //this.$store.dispatch('getMe');
  },

  methods: {
  },

  watch: {
  },

  components: {
    PostFeed,
  },
}
</script>

<style scoped>
</style>
