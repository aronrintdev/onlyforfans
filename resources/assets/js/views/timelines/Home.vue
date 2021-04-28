<template>
  <div v-if="!isLoading">

    <div class="container-xl" id="view-home_timeline">

      <section class="row">
        <article class="col-sm-12">
          <StoryBar :session_user="session_user"></StoryBar>
        </article>
      </section>

      <section class="row">
        <main :class="mainClass">
          <CreatePost :session_user="session_user" :timeline="timeline" />
          <PostFeed :session_user="session_user" :timeline="timeline" :is_homefeed="true" />
        </main>
        <aside v-if="!isGridLayout" class="col-md-5 col-lg-4">
          <MiniMyStatsWidget :session_user="session_user" :timeline="timeline" />
          <!--
          <SuggestedFeed :session_user="session_user" :timeline="timeline" class="mt-3" />
          -->
          <SuggestedFeed class="mt-3" />
        </aside>
      </section>

    </div>

    <Modals />

  </div>
</template>

<script>
import Vuex from 'vuex';
import { eventBus } from '@/app'
import PostFeed from '@components/timelines/PostFeed.vue';
import StoryBar from '@components/timelines/StoryBar.vue';
import CreatePost from '@components/timelines/CreatePost.vue';
import MiniMyStatsWidget from '@components/user/MiniMyStatsWidget.vue';
import SuggestedFeed from '@components/common/SuggestedFeed.vue';
import Modals from '@components/Modals'

export default {
  components: {
    PostFeed,
    StoryBar,
    CreatePost,
    MiniMyStatsWidget,
    SuggestedFeed,
    Modals,
  },

  computed: {
    ...Vuex.mapGetters([
      'session_user',
      'timeline',
    ]),

    mainClass() {
      return {
        'col-md-7': !this.isGridLayout,
        'col-lg-8': !this.isGridLayout,
        'col-md-12': this.isGridLayout, // full-width
      }
    },

    isLoading() {
      return !this.timeline || !this.session_user
    },
  },

  data: () => ({
    isGridLayout: false, // %FIXME: can this be set in created() so we have 1 source of truth ? (see PostFeed)
  }),

  created() {
    eventBus.$on('update-originator', () => {
      this.load()
    })

    eventBus.$on('set-feed-layout',  isGridLayout  => {
      this.isGridLayout = isGridLayout
    })

  },

  mounted() { },
  watch: { },

}
</script>

<style scoped>
</style>
