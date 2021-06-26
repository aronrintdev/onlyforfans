<template>
  <div v-if="!isLoading" class="w-100">
    <Player 
      @next-story-timeline="nextStoryTimeline"
      @prev-story-timeline="prevStoryTimeline"
      :storyteller="storyteller" 
      :username="session_user.username" 
      :stories="stories" />
  </div>
</template>

<script>
/**
 * Stories Player View
 */
import Player from '@components/stories/Player'
import Vuex from 'vuex'

export default {
  name: 'StoriesPlayer',

  props: {
    slug: { type: String, default: '' },
  },

  data: () => ({
    timelineIndex: 0,
    timelines: [],
  }),

  computed: {
    //...Vuex.mapState(['stories', 'session_user']),
    ...Vuex.mapState(['session_user']),

    isLoading() {
      return !this.session_user || !this.stories
    },

    stories() {
      console.log(`stories.computed`)
      return this.timelines[this.timelineIndex]?.stories || []
    },
    storyteller() {
      console.log(`storyteller.computed`)
      return this.timelines[this.timelineIndex]?.name || null
    },
  },

  methods: {
    nextStoryTimeline() {
      console.log(`nextStoryTimeline() - ${this.timelineIndex} `)
      this.timelineIndex += 1
    },
    prevStoryTimeline() {
      console.log(`prevStoryTimeline() - ${this.timelineIndex} `)
      this.timelineIndex -= 1
    },
  },

  watch: {
  },

  created() {
    if ( this.$route.query.timeline && this.$route.query.timeline === 'me' ) {
      const response = axios.get( this.$apiRoute('timelines.myStories')).then ( response => {
        this.timelines = response.data.data
      })
    } else {
      const response = axios.get( this.$apiRoute('timelines.myFollowedStories')).then ( response => {
        this.timelines = response.data.data
      })
    }
  },

  components: {
    Player,
  },

}
</script>

<style lang="scss" scoped>

</style>
