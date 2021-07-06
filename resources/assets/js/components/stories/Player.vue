<template>
  <div v-if="!isLoading" class="container-fluid supercrate-player">

    <b-row>
      <b-col>
        <div class="position-relative">
          <nav :style="cssNav" class="m-0">
            <div v-for="(s, index) in renderedStories" :key="`header-${s.id}`" class="cursor-pointer" @click="goTo(index)">
              <div :ref="`nav-${s.id}`" />
            </div>
          </nav>
          <section v-for="(s, index) in renderedStories" :key="`story-${s.id}`">
            <div v-if="current==index" :style="cssDisplay" class="display-area OFF-bg-blur">
              <h6 class="tag-creator">{{ storyteller }}
                <ul>
                  <li>story index: {{index+1}}/{{renderedStories.length}}</li>
                  <li>current story ID: {{s.id}}</li>
                </ul>
              </h6>
              <div class="bg-blur"></div>
              <div v-touch:swipe.top="handleSwipeUp" class="crate-content">
                <article v-if="s.stype === 'text'" class="h-100 v-wrap">
                  <p class="h4 text-center v-box">{{ s.content }}</p>
                </article>
                <article v-else-if="s.stype === 'image' && s.mediafiles" class="h-100">
                  <img :src="s.mediafiles[0].filepath" class="OFF-img-fluid OFF-h-100" />
                  <see-more v-if="s.swipe_up_link" :link="s.swipe_up_link"></see-more>
                </article>
              </div>
            </div>
          </section>

          <section class="my-3 d-flex justify-content-between">
            <b-btn @click="doNav('previous')" class="">Previous</b-btn>
            <b-btn @click="doNav('next')" class="">Next</b-btn>
          </section>
        </div>
      </b-col>

    </b-row>

    <hr />

    <b-row>
      <b-col class="tag-debug">
        <div>
        </div>
        <div>
          <b-form-checkbox v-model="play">Auto Play</b-form-checkbox>
        </div>
        <div>
          Auto Play Speed
          <VueSlider :data="speedOptions" v-model="speed" hideLabel />
        </div>
      </b-col>
    </b-row>

  </div>
</template>

<script>
import _ from 'lodash'
import SeeMore from './SeeMore.vue'

export default {
  components: {
    seeMore: SeeMore
  },

  props: {
    storyteller: { type: String, default: '' },
    username: { type: String, default: '' }, // session user (?)
    stories: { type: Array, default: () => [] },
    maxStories: { type: Number, default: 15 },
  },

  data: () => ({
    current: 0, // index from 0
    play: true,
    speed: 3000,
    timelineAnimation: null,
    speedOptions: [
      {
        label: 'Very Slow',
        value: 10000,
      },
      {
        label: 'Slow',
        value: 4000,
      },
      {
        label: 'Normal',
        value: 3000,
      },
      {
        label: 'Fast',
        value: 2000,
      },
      {
        label: 'Very Fast',
        value: 1000,
      },
    ],
  }),

  computed: {
    isLoading() {
      return !this.storyteller || !this.stories || !this.username
    },
    cssNav() {
      return {
        //'--bg-color': this.bgColor,
        //'--height': this.height + 'px',
        '--grid-template-columns': `repeat(${this.renderedStories.length}, 1fr)`,
      }
    },
    cssDisplay() {
      //console.log ('renderedStories', { renderedStories: this.renderedStories })
      if (this.renderedStories.length && this.renderedStories[0].mediafiles && this.renderedStories[this.current].mediafiles[0]) {
        return {
          '--background-image': `url(${this.renderedStories[this.current].mediafiles[0].filepath})`,
        }
      }
      return {}
    },
    renderedStories() {
      if (this.stories.length > this.maxStories) {
        return _.slice(this.stories, 0, this.maxStories)
      }
      return this.stories
    },

  },

  methods: {
    doNav(direction) {
      console.log(`doNav() - ${direction} - ${this.current+1}/${this.stories.length}`)
      switch (direction) {
        case 'previous':
          if ( (this.current-1) < 0 ) {
            console.log(`doNav() - emit prev-story-timeline`)
            this.$emit('prev-story-timeline', { foo: 'bar'} )
            this.current = 0
          } else {
            this.goTo(this.current - 1)
          }
          break
        case 'next':
          if ( (this.current+1) >= this.stories.length ) {
            console.log(`doNav() - emit next-story-timeline`)
            this.$emit('next-story-timeline', { foo: 'bar'} )
            this.current = 0
          } else {
            this.goTo(this.current + 1)
          }
          break
      }
    },
    goTo(index) {
      this.timelineAnimation.pause()

      index = index < 0
        ? this.renderedStories.length - 1
        : index >= this.renderedStories.length
        ? 0
        : index
      this.current = index
      this.timelineAnimation.seek(this.getTimelineSeek(index))

      if (this.play) {
        this.timelineAnimation.play()
      }
    },

    getTimelineSeek(index) {
      return (index / this.renderedStories.length) * this.timelineAnimation.duration
    },

    addTimelineElements() {
      this.renderedStories.forEach((story, index) => {
        this.timelineAnimation.add({
          targets: this.$refs[`nav-${story.id}`][0],
          width: '100%',
          changeBegin: (a) => {
            this.current = index
          },
        })
      })
    },

    createTimeline() {
      this.timelineAnimation = this.$anime.timeline({
        autoplay: this.play,
        duration: this.speed,
        easing: 'linear',
        loop: false,
        complete: function() {
          console.log('$anime - complete callback')
        },
      })
      this.addTimelineElements()
    },

    removeTimeline() {
      this.timelineAnimation.pause()
      this.timelineAnimation.restart()
      this.renderedStories.forEach((story, index) => {
        this.timelineAnimation.remove(this.$refs[`nav-${story.id}`][0])
      })
    },

    handleSwipeUp() {
      const link = this.renderedStories[this.current].swipe_up_link
      if (link) {
        this.$emit('open-see-more-link')
      }
    },
  },

  watch: {
    play(value) {
      if (value) {
        this.timelineAnimation.play()
      } else {
        this.timelineAnimation.pause()
      }
    },
    speed() {
      const progress = this.timelineAnimation.currentTime / this.timelineAnimation.duration
      this.removeTimeline()
      this.createTimeline()
      this.timelineAnimation.pause()
      this.timelineAnimation.seek(progress * this.timelineAnimation.duration)
      if (this.play) {
        this.timelineAnimation.play()
      }
    },
  },

  mounted() {
    this.createTimeline()
  },
}
</script>

<style lang="scss" scoped>
.display-area {
  position: relative;
  height: 70vh;

  .tag-creator {
    color: #fff;
    position: absolute;
    top: 1.5rem;
    left: 1rem;
    z-index: 1000;
  }

    & > .crate-content {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;

      p {
        margin: auto;
      }

      img {
        max-width: 100%;
        height: 100%;
        display: block;
        margin: auto;
        object-fit: cover;
      }
    }

    & > .bg-blur {
      opacity: 0.82;
      //background-image: var(--background-image);
      background: #010101;

      //filter: blur(8px);
      //-webkit-filter: blur(8px);

      width: 100%;
      height: 100%;

      /* Center and scale the image nicely */
        //background-position: center;
      //background-repeat: no-repeat;
      //background-size: cover;
    }
}

nav {
  height: 0.3rem;
  position: absolute;
  top: 0.3rem;
  left: 0;
  z-index: 200;
  box-sizing: border-box;
  display: grid;
  grid-column-gap: 0.2rem;
  grid-template-columns: var(--grid-template-columns);
  width: 100%;

  & > div.cursor-pointer {
    background: rgba(255, 255, 255, 0.55);
    height: 100%;

    & > div {
      background: white;
      height: 100%;
      width: 0%;
    }
  }
}

/* Vertical centering, from https://stackoverflow.com/questions/396145/how-to-vertically-center-a-div-for-all-browsers */
  .v-wrap {
    height: 100%;
    text-align: center;
    white-space: nowrap;

    &:before {
      content: '';
      display: inline-block;
      vertical-align: middle;
      width: 0;
      /*might want to tweak this. .25em for extra white space */
        height: 100%;
    }
  }

  .v-box {
    display: inline-block;
    vertical-align: middle;
    white-space: normal;
  }
</style>
