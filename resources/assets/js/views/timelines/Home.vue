<template>
    <div class="container" id="view-home_timeline">

      <section class="row" v-if="state !== 'loading'">
        <article class="col-sm-12">
          <StoryBar :session_user="session_user" :timeline="timeline"></StoryBar>
        </article>
      </section>

      <section class="row" v-if="state !== 'loading'">

        <main class="col-md-7 col-lg-8">
          <CreatePost :session_user="session_user" :timeline="timeline" />
          <PostFeed :session_user="session_user" :timeline="timeline" :is_homefeed="true" />
        </main>

        <aside class="col-md-5 col-lg-4">
          <MiniMyStatsWidget :session_user="session_user" :timeline="timeline" />
          <SuggestedFeed :session_user="session_user" :timeline="timeline" class="mt-3" />
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
import MiniMyStatsWidget from '@components/user/MiniMyStatsWidget.vue';
import SuggestedFeed from '@components/common/SuggestedFeed.vue';

export default {
  components: {
    PostFeed,
    StoryBar,
    CreatePost,
    MiniMyStatsWidget,
    SuggestedFeed,
  },

  computed: {
    ...Vuex.mapGetters(['session_user', 'timeline']),
  },

  data: () => ({
    state: 'loading', // loading | loaded
  }),

  watch: {
    timeline(value) {
      if (value && this.session_user) {
        this.state = 'loaded'
      }
    },
    session_user(value) {
      if (value && this.timeline) {
        this.state = 'loaded'
      }
    }
  },

  mounted() {
    if (this.session_user && this.timeline) {
      this.state = 'loaded'
    }
  },

}
</script>

<style scoped>
</style>
