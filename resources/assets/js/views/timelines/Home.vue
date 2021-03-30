<template>
  <div v-if="!isLoading">

    <div class="container" id="view-home_timeline">

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
          <SuggestedFeed :session_user="session_user" :timeline="timeline" class="mt-3" />
        </aside>
      </section>

    </div>

    <!-- %FIXME: DRY vs Show -->
    <b-modal id="modal-tip" size="sm" title="Send a Tip" hide-footer body-class="p-0">
      <SendTip :session_user="session_user" :timeline="timeline" />
    </b-modal>

    <b-modal id="modal-purchase_post" size="sm" title="Purchase Post" hide-footer body-class="p-0">
      <PurchasePost :session_user="session_user" :post="selectedPost" />
    </b-modal>

    <b-modal id="modal-follow" title="Follow" hide-footer body-class="p-0">
      <FollowTimeline :session_user="session_user" :timeline="selectedTimeline" :subscribe_only="subscribeOnly" />
    </b-modal>

    <b-modal size="xl" id="modal-post" title="Post" hide-footer body-class="p-0">
      <PostDisplay :session_user="session_user" :post="selectedPost" :is_feed="false" />
    </b-modal>

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
import FollowTimeline from '@components/modals/FollowTimeline.vue'
import PurchasePost from '@components/modals/PurchasePost.vue'
import SendTip from '@components/modals/SendTip.vue'
import PostDisplay from '@components/posts/Display'

export default {
  components: {
    PostFeed,
    StoryBar,
    CreatePost,
    MiniMyStatsWidget,
    SuggestedFeed,
    FollowTimeline,
    PurchasePost,
    SendTip,
    PostDisplay,
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
    selectedPost: null,
    subscribeOnly: true, // for modal
    selectedTimeline: null,
  }),

  created() {

    // %FIXME: DRY
    eventBus.$on('open-modal', ({ key, data }) => {
      console.log('views/timelines/Show.on(open-modal)', { key, data });
      switch(key) {
        case 'render-purchase-post':
          this.selectedPost = data.post
          this.$bvModal.show('modal-purchase_post')
          break
        case 'render-follow':
          this.selectedTimeline = data.timeline
          this.subscribeOnly = false
          this.$bvModal.show('modal-follow')
          break
        case 'render-subscribe':
          this.selectedTimeline = data.timeline
          this.subscribeOnly = true
          this.$bvModal.show('modal-follow')
          break
        case 'render-tip':
          this.$bvModal.show('modal-tip')
          break
        case 'show-post':
          this.selectedPost = data.post
          this.$bvModal.show('modal-post')
          break
      }
    })

    eventBus.$on('update-timeline', () => {
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
