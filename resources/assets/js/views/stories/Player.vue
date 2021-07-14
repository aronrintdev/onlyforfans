<template>
  <div v-if="!isLoading" class="view-stories-player w-100">
    <PlayerComponent
      @next-story-timeline="nextStoryTimeline"
      @prev-story-timeline="prevStoryTimeline"
      :storyteller="storyteller" 
      :session_user="session_user" 
      :timeline="currentTimeline" 
      :avatar="avatar" 
      :slides="slides" 
      :slideIndexInit="slideIndex" 
    />

    <!--
    <div class="OFF-d-none">
      <p>DEBUG:</p>
      <ul>
        <li>timeline index: {{timelineIndex+1}}/{{timelines.length}}</li>
        <li>current timeline ID: {{currentTimeline.name}} ({{currentTimeline.id}})</li>
      </ul>
    </div>
    -->

  </div>
</template>

<script>
import Vuex from 'vuex'
import PlayerComponent from '@components/stories/Player'

export default {
  props: {
    slug: { type: String, default: '' },
  },

  data: () => ({
    timelineIndex: null, // current timeline being played
    timelines: null, // a timeline (story) consists of many slides
    slideIndex: null, // current slide being played
    slides: null,
  }),

  computed: {
    ...Vuex.mapState(['session_user']),

    isLoading() {
      return !this.session_user || !this.timelines || (this.timelineIndex===null) || !this.slides || (this.slideIndex===null)
    },

    avatar() {
      return this.timelines ? this.timelines[this.timelineIndex].avatar : null
    },
    storyteller() {
      return this.timelines ? this.timelines[this.timelineIndex].name : null
    },
    currentTimeline() {
      return this.timelines ? this.timelines[this.timelineIndex] : null
    },
  },

  methods: {
    nextStoryTimeline() {
      this.timelineIndex = Math.min(this.timelines.length-1, this.timelineIndex+1) // can't next past last timeline
      this.getSlides()
    },
    prevStoryTimeline() {
      this.timelineIndex = Math.max(0, this.timelineIndex-1) // can't prev before first timeline
      this.getSlides()
    },

    getSlides() {
      const params = {
        timeline_id: this.timelines[this.timelineIndex].id,
        viewer_id: this.session_user.id,
      }
      axios.get( this.$apiRoute('stories.getSlides'), { params } ).then ( response => {
        this.slides = response.data.stories // set last (and after timelineIndex is set) to avoid loading issues (%NOTE)
        this.slideIndex = response.data.slideIndex || 0
        //this.$nextTick( () => this.initAnimation() )
      })
    }
  },

  created() {
    // Load all the timelines to be played (without slides)
    axios.get( this.$apiRoute('timelines.myFollowedStories')).then ( response => {
      const timelines = response.data.data
      // if specific timeline is provided via the route, navigate directly to it, otherwise start from index 0
      this.timelineIndex = this.$route.params.timeline_id 
        ? timelines.findIndex( t => t.id === this.$route.params.timeline_id )
        : 0
      this.timelines = timelines // set last (and after timelineIndex is set) to avoid loading issues (%NOTE)
      this.getSlides()
    })

  },

  components: {
    PlayerComponent,
  },

  name: 'StoriesPlayer',
}
</script>

<style lang="scss" scoped>
body {
  .view-stories-player {
    position: absolute;
    top:0;
    left:0;
    z-index:2000;
  }
}
</style>
