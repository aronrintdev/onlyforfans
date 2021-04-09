<template>
  <div v-if="!isLoading">

    <div class="container" id="view-show_timeline">

      <section class="row">
        <article class="col-sm-12">
          <Banner :session_user="session_user" :timeline="timeline" :follower="timeline.user" />
        </article>
      </section>

      <section class="row">
        <aside v-if="!isGridLayout" class="col-md-5 col-lg-4">
          <FollowCtrl :session_user="session_user" :timeline="timeline" />
          <PreviewUpgrade :session_user="session_user" :timeline="timeline" />
        </aside>
        <main :class="mainClass">
          <PostFeed :session_user="session_user" :timeline="timeline" :is_homefeed="false" />
        </main>
      </section>

    </div>

    <!-- %FIXME: DRY vs Home -->
    <b-modal id="modal-tip" size="sm" title="Send a Tip" hide-footer body-class="p-0">
      <SendTip :session_user="session_user" :timeline="timeline" :payload="modalPayload" />
    </b-modal>

    <b-modal id="modal-purchase_post" size="lg" title="Purchase Post" hide-footer body-class="p-0">
      <PurchasePost :session_user="session_user" :post_id="selectedResourceId" />
    </b-modal>

    <b-modal id="modal-follow" title="Follow" hide-footer body-class="p-0">
      <FollowTimeline :session_user="session_user" :timeline="timeline" :subscribe_only="subscribeOnly" />
    </b-modal>

    <b-modal size="xl" id="modal-post" title="Post" hide-footer body-class="p-0">
      <PostDisplay :session_user="session_user" :post="selectedResource" :is_feed="false" />
    </b-modal>

    <b-modal size="xl" id="modal-photo" title="Photo" hide-footer body-class="p-0">
      <ImageDisplay :session_user="session_user" :mediafile="selectedResource" :is_feed="false" />
    </b-modal>

  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import PostFeed from '@components/timelines/PostFeed.vue'
import StoryBar from '@components/timelines/StoryBar.vue'
import Banner from '@components/timelines/Banner.vue'
import FollowCtrl from '@components/common/FollowCtrl.vue'
import PreviewUpgrade from '@components/common/PreviewUpgrade.vue'
import FollowTimeline from '@components/modals/FollowTimeline.vue'
import PurchasePost from '@components/modals/PurchasePost.vue'
import SendTip from '@components/modals/SendTip.vue'
import PostDisplay from '@components/posts/Display'
import ImageDisplay from '@components/timelines/elements/ImageDisplay'

export default {
  components: {
    PostFeed,
    StoryBar,
    Banner,
    FollowCtrl,
    PreviewUpgrade,
    FollowTimeline,
    PurchasePost,
    SendTip,
    PostDisplay,
    ImageDisplay,
  },

  props: {
    slug: null,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    mainClass() {
      return {
        'col-md-7': !this.isGridLayout,
        'col-lg-8': !this.isGridLayout,
        'col-md-12': this.isGridLayout, // full-width
      }
    },

    isLoading() {
      return !this.slug || !this.timeline
    }
  },

  data: () => ({
    timeline: null,
    isGridLayout: false, // %FIXME: can this be set in created() so we have 1 source of truth ? (see PostFeed)
    subscribeOnly: true, // for modal
    selectedResource: null,
    selectedResourceId: null, // %FIXME: hacky
    modalPayload: null, // eventually replace all 'selected...' with this %PSG 20210409
  }),

  created() {

    eventBus.$on('open-modal', ({ key, data }) => {
      console.log('views/timelines/Show.on(open-modal)', { key, data });
      switch(key) {
        case 'render-purchase-post':
          this.selectedResource = data.post
          this.selectedResourceId = data.post.id
          this.$bvModal.show('modal-purchase_post')
          break
        case 'render-follow':
          this.subscribeOnly = false
          this.$bvModal.show('modal-follow')
          break
        case 'render-subscribe':
          this.subscribeOnly = true
          this.$bvModal.show('modal-follow')
          break
        case 'render-tip':
          this.modalPayload = data
          this.$bvModal.show('modal-tip')
          break
        case 'show-post':
          this.selectedResource = data.post
          this.$bvModal.show('modal-post')
          break
        case 'show-photo':
          this.selectedResource = data.mediafile
          this.$bvModal.show('modal-photo')
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

  mounted() {
    if (this.slug) {
      this.load()
    }
  },

  methods: {
    async load() {
      try { 
        const response = await this.axios.get(this.$apiRoute('timelines.show', { timeline: this.slug }))
        this.timeline = response.data.data
      } catch (error) {
        this.$log.error(error)
      }
    },

  },

  watch: {
    slug(value) {
      this.load()
    }
  },
}
</script>

<style scoped>
</style>
