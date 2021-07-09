<template>
  <div  v-if="!isLoading" class="container-xl" id="view-home_timeline">
    <b-tabs
      content-class="mt-3"
      active-nav-item-class="active-navitem"
      nav-class="navitem"
      @input="changeActiveTab"
    >
      <b-tab title="Home" active>
        <section class="row mb-2">
          <article class="col-sm-12">
            <StoryBar :session_user="session_user"></StoryBar>
          </article>
        </section>

        <section class="row" v-if="activeTab === 0">
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
      </b-tab>
      <b-tab :title="`Queue (${queue_metadata.total? queue_metadata.total : 0})`">
        <section class="row" v-if="activeTab === 1">
          <main :class="mainClass">
            <CreatePost :session_user="session_user" :timeline="timeline" />
            <PostFeed
              :key="activeTab"
              :session_user="session_user"
              :timeline="timeline"
              :is_homefeed="true"
              :is_schedulefeed="true"
            />
          </main>
          <aside v-if="!isGridLayout" class="col-md-5 col-lg-4">
            <MiniMyStatsWidget :session_user="session_user" :timeline="timeline" />
            <SuggestedFeed class="mt-3" />
          </aside>
        </section>
      </b-tab>
    </b-tabs>
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
      'queue_metadata'
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
    activeTab: 0,
  }),

  created() {
    // To get queue posts meta data
    this.$store.dispatch('getQueueMetadata')

    eventBus.$on('update-timelines', (timelineId) => {
      if (timelineId === this.timeline.id) {
        this.load()
      }
    })

    eventBus.$on('set-feed-layout',  isGridLayout  => {
      this.isGridLayout = isGridLayout
    })

  },

  mounted() { },
  watch: { },
  methods: {
    changeActiveTab(event) {
      this.activeTab = event;
    }
  }
}
</script>

<style>
  .navitem.nav-tabs a.nav-link {
    font-size: 19px;
    font-weight: 500;
    text-transform: uppercase;
    margin-right: 16px;
    padding: 4px 5px 10px;
    background: transparent;
    color: rgba(138,150,163,.7);
  }
  .navitem.nav-tabs a.active-navitem {
    font-size: 19px;
    font-weight: 500;
    text-transform: uppercase;
    margin-right: 16px;
    color: black;
    padding: 4px 5px 10px;
    background: transparent;
    border: none;
    border-bottom: solid 2px #000;
  }
</style>
