<template>
    <div class="container" id="view-home_timeline">

      <section class="row" v-if="!isLoading">
        <article class="col-sm-12">
          <StoryBar :session_user="session_user"></StoryBar>
        </article>
      </section>

      <section class="row" v-if="!isLoading">

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
    ...Vuex.mapGetters([
      'session_user', 
      'timeline',
    ]),
    isLoading() {
      return !this.timeline || !this.session_user
    },
  },

  data: () => ({
  }),

  mounted() { },

  watch: {
  },

}
</script>

<style scoped>
</style>
