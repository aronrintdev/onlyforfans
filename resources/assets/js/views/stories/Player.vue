<template>
  <div v-if="!isLoading" class="view-stories-player w-100">
    <PlayerComponent
      @next-story-timeline="nextStoryTimeline"
      @prev-story-timeline="prevStoryTimeline"
      :storyteller="storyteller" 
      :session_user="session_user" 
      :timeline="currentTimeline" 
      :avatar="avatar" 
    />

    <div class="OFF-d-none">
      <p>DEBUG:</p>
      <ul>
        <li>timeline index: {{timelineIndex+1}}/{{timelines.length}}</li>
        <li>current timeline ID: {{currentTimeline.name}} ({{currentTimeline.id}})</li>
      </ul>
    </div>

  </div>
</template>

<script>
import Vuex from 'vuex'
import PlayerComponent from '@components/stories/Player'

export default {
  name: 'StoriesPlayer',

  props: {
    slug: { type: String, default: '' },
  },

  data: () => ({
    timelineIndex: 0,
    timelines: null
  }),

  computed: {
    ...Vuex.mapState(['session_user']),

    isLoading() {
      //return !this.session_user || !this.stories || !this.timelines
      return !this.session_user || !this.timelines
    },

    //stories() {
    //return this.timelines ? this.timelines[this.timelineIndex].stories : null
    //},
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
      console.log(`views/stories/Player.vue::nextStoryTimeline() - timelineIndex: ${this.timelineIndex} `)
    },
    prevStoryTimeline() {
      this.timelineIndex = Math.max(0, this.timelineIndex-1) // can't prev before first timeline
      console.log(`views/stories/Player.vue::prevStoryTimeline() - timelineIndex: ${this.timelineIndex} `)
    },
  },

  created() {
    const response = axios.get( this.$apiRoute('timelines.myFollowedStories')).then ( response => {
      const timelines = response.data.data
      // if specific timeline is provided via the route, navigate directly to it, otherwise start from index 0
      if ( this.$route.params.timeline_id ) {
        this.timelineIndex = timelines.findIndex( t => t.id === this.$route.params.timeline_id )
      }
      this.timelines = timelines // set last (and after timelineIndex is set) to avoid loading issues (%NOTE)
    })
  },

  components: {
    PlayerComponent,
  },

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
