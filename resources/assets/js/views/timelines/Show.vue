<template>
  <div v-if="!isLoading" class="container-xl" id="view-show_timeline">

    <section class="row">
      <article class="col-sm-12">
        <Banner :session_user="session_user" :timeline="timeline" :follower="timeline.user" :key="timeline.id"/>
      </article>
    </section>

    <section class="row">
      <aside v-if="!isGridLayout" class="col-md-5 col-lg-4">
        <FollowCtrl :session_user="session_user" :timeline="timeline" />
        <PreviewUpgrade :session_user="session_user" :timeline="timeline" :viewMorePhotos="setCurrentFeedType" :key="timeline.id" />
      </aside>
      <main :class="mainClass">
        <CreatePost v-if="isOwner || canCreatePostAsStaff" :session_user="session_user" :timeline="timeline" class="mt-3" />
        <PostFeed
          :session_user="session_user"
          :timeline="timeline"
          :is_homefeed="false"
          :currentFeedType="currentFeedType"
          :setCurrentFeedType="setCurrentFeedType"
        />
      </main>
    </section>

  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
import PostFeed from '@components/timelines/PostFeed.vue'
import StoryBar from '@components/timelines/StoryBar.vue'
import Banner from '@components/timelines/Banner.vue'
import CreatePost from '@components/common/CreatePost.vue';
import FollowCtrl from '@components/common/FollowCtrl.vue'
import PreviewUpgrade from '@components/common/PreviewUpgrade.vue'

export default {
  components: {
    PostFeed,
    StoryBar,
    Banner,
    FollowCtrl,
    PreviewUpgrade,
    CreatePost,
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
    },

    isOwner() {
      return this.session_user.id === this.timeline.user.id
    },

    canCreatePostAsStaff() {
      const index = this.session_user.companies.findIndex(company => company.id == this.timeline.id);
      return index > -1 && this.session_user.companies[index].permissions.findIndex(permission => permission.name   == 'Post.create') > -1
    }
  },

  data: () => ({
    timeline: null,
    isGridLayout: false, // %FIXME: can this be set in created() so we have 1 source of truth ? (see PostFeed)
    currentFeedType: null,
  }),

  created() {
    this.load()

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

  methods: {
    async load() {
      try {
        const response = await this.axios.get(this.$apiRoute('timelines.show', { timeline: this.slug }))
        this.timeline = response.data.data
        if (this.timeline && this.timeline.id) {
          this.$store.dispatch('getPreviewposts', { timelineId: this.timeline.id, limit: 6 })
        }
      } catch (error) {
        this.$log.error(error)
      }
    },

    setCurrentFeedType(feedType) {
      this.currentFeedType = feedType
    }
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
