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

    <Modals />

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
import Modals from '@components/Modals'

export default {
  components: {
    PostFeed,
    StoryBar,
    Banner,
    FollowCtrl,
    PreviewUpgrade,
    Modals,
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
    timeline: {},
    isGridLayout: false, // %FIXME: can this be set in created() so we have 1 source of truth ? (see PostFeed)
  }),

  created() {
    eventBus.$on('update-timelines', (timelineId) => {
      if (timelineId === this.timeline.id) {
        this.load()
      }
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
