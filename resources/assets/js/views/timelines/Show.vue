<template>
  <div v-if="!isLoading">

    <div class="container" id="view-show_timeline">

      <section class="row">
        <article class="col-sm-12">
          <Banner :session_user="session_user" :timeline="timeline" :follower="timeline.user" />
        </article>
      </section>

      <section class="row">
        <aside class="col-md-5 col-lg-4">
          <FollowCtrl :session_user="session_user" :timeline="timeline" />
          <PreviewUpgrade :session_user="session_user" :timeline="timeline" />
        </aside>
        <main class="col-md-7 col-lg-8">
          <PostFeed :session_user="session_user" :timeline="timeline" :is_homefeed="false" />
        </main>
      </section>

    </div>

    <!-- %FIXME: DRY vs Home -->
    <b-modal id="modal-tip" size="sm" title="Send a Tip" hide-footer body-class="p-0">
      <SendTip :session_user="session_user" :timeline="timeline" />
    </b-modal>

    <b-modal id="modal-purchase_post" size="lg" title="Purchase Post" hide-footer body-class="p-0">
      <PurchasePost :session_user="session_user" :post="selectedPost" />
    </b-modal>

    <b-modal id="modal-follow" title="Follow" hide-footer body-class="p-0">
      <FollowTimeline :session_user="session_user" :timeline="timeline" :subscribe_only="subscribeOnly" />
    </b-modal>

    <b-modal id="modal-post" title="Post" hide-footer body-class="p-0">
      <PostDisplay :session_user="session_user" :post="selectedPost" />
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
  },

  props: {
    slug: null,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.slug || !this.timeline
    }
  },

  data: () => ({
    timeline: null,
    selectedPost: null,
    subscribeOnly: true, // for modal
  }),

  created() {

    eventBus.$on('open-modal', ({ key, data }) => {
      console.log('views/timelines/Show.on(open-modal)', {
        key, data,
      });
      switch(key) {
        case 'render-purchase-post':
          this.selectedPost = data.post
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
          this.$bvModal.show('modal-tip')
          break
        case 'show-post':
          this.selectedPost = data.post
          this.$bvModal.show('modal-post')
          break
      }
    })

    eventBus.$on('update-timeline', () => {
      console.log('views.timelines.Show - eventBus.$on(update-timeline)')
      this.load() 
    })
  },

  mounted() {
    if (this.slug) {
      this.load()
    }
    //if (!this.session_user) { }
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
