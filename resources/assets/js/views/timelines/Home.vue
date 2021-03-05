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
      return this.is_session_user_loading && this.is_timeline_loading;
    },
  },

  data: () => ({
    is_session_user_loading: true,
    is_timeline_loading: true,
  }),

  mounted() { },

  watch: {
    timeline(value) {
      //if (value && this.session_user)
      if (value) {
        this.is_timeline_loading = false
      }
    },
    session_user(value) {
      if (value) {
        this.is_session_user_loading = false
      }
    }
  },


}
</script>

<style scoped>
</style>
