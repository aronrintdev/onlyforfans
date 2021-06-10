<template>
  <div class="container-fluid supercrate-player">
    <section class="row">
      <aside class="col-md-3 tag-debug">
        <h2>My Story Views</h2>
        <hr />
        <div class="mb-3">
          <b-form-checkbox v-model="play">Auto Play</b-form-checkbox>
        </div>
        <div class="mb-3">
          Auto Play Speed
          <VueSlider :data="speedOptions" v-model="speed" hideLabel />
        </div>
      </aside>

      <main class="col-md-9 tag-debug">
        <nav :style="cssNav">
          <div
            v-for="(story, index) in renderedStories"
            :key="`header-${story.id}`"
            class="cursor-pointer"
            @click="goTo(index)"
          >
            <div :ref="`nav-${story.id}`" />
          </div>
        </nav>

        <section v-for="(story, index) in renderedStories" :key="index">
          <div v-if="current == index" :style="cssDisplay" class="display-area bg-blur">
            <div class="bg-blur"></div>
            <div v-touch:swipe.top="handleSwipeUp" class="crate-content">
              <article v-if="story.stype === 'text'" class="h-100 v-wrap">
                <p class="h4 text-center v-box">{{ story.content }}</p>
              </article>
              <article v-else-if="story.stype === 'image'" class="h-100">
                <img :src="story.mediafiles[0].filepath" class="OFF-img-fluid OFF-h-100" />
                <see-more v-if="story.swipe_up_link" :link="story.swipe_up_link"></see-more>
              </article>
            </div>
          </div>
        </section>

        <section class="my-3 d-flex justify-content-between">
          <b-btn @click="doNav('previous')" class="">Previous</b-btn>
          <b-btn @click="doNav('next')" class="">Next</b-btn>
        </section>
      </main>
    </section>
  </div>
</template>

<script>
/**
 * Story Player
 */
import _ from 'lodash'
import SeeMore from './SeeMore.vue'

export default {
  components: {
    seeMore: SeeMore
  },

  props: {
    username: { type: String, default: '' },
    stories: { type: Array, default: () => [] },
    maxStories: { type: Number, default: 15 },
  },

  data: () => ({
    current: 0,
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
    cssNav() {
      return {
        //'--bg-color': this.bgColor,
        //'--height': this.height + 'px',
        '--grid-template-columns': `repeat(${this.renderedStories.length}, 1fr)`,
      }
    },
    cssDisplay() {
      if (this.renderedStories[this.current].mediafiles[0]) {
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
      switch (direction) {
        case 'previous':
          this.goTo(this.current - 1)
          break
        case 'next':
          this.goTo(this.current + 1)
          break
      }
    },
    goTo(index) {
      this.timelineAnimation.pause()

      index =
        index < 0
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
        loop: true,
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
    opacity: 0.4;
    background-image: var(--background-image);

    filter: blur(8px);
    -webkit-filter: blur(8px);

    width: 100%;
    height: 100%;

    /* Center and scale the image nicely */
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
  }
}

nav {
  margin: 1rem 0;
  box-sizing: border-box;
  display: grid;
  grid-column-gap: 1em;
  grid-template-columns: var(--grid-template-columns);
  width: 100%;
  height: 0.7em;

  & > div {
    background: rgba(0, 0, 0, 0.25);
    height: 100%;

    & > div {
      background: black;
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
