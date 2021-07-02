<template>
  <div v-if="!isLoading" class="view-stories-player w-100">
    <div>
      <p>DEBUG:</p>
      <ul>
        <li>timeline index: {{timelineIndex+1}}/{{timelines.length}}</li>
        <li>current timeline ID: {{currentTimeline.name}} ({{currentTimeline.id}})</li>
      </ul>
    </div>
    <PlayerComponent
      @next-story-timeline="nextStoryTimeline"
      @prev-story-timeline="prevStoryTimeline"
      :storyteller="storyteller" 
      :username="session_user.username" 
      :stories="stories" />
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
    //...Vuex.mapState(['stories', 'session_user']),
    ...Vuex.mapState(['session_user']),

    isLoading() {
      return !this.session_user || !this.stories || !this.timelines
    },

    stories() {
      return this.timelines ? this.timelines[this.timelineIndex].stories : []
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
      console.log(`nextStoryTimeline() - timelineIndex: ${this.timelineIndex} `)
    },
    prevStoryTimeline() {
      this.timelineIndex = Math.max(0, this.timelineIndex-1) // can't prev before first timeline
      console.log(`prevStoryTimeline() - timelineIndex: ${this.timelineIndex} `)
    },
  },

  watch: {
  },

  created() {
    console.log('views/stories/Player::created()')
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
    PlayerComponent,
  },

}
</script>

<style lang="scss" scoped>

</style>
