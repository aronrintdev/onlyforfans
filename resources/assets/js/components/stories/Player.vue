<template>
  <div v-if="!isLoading" class="container-fluid supercrate-player">

    <!--
    <b-sidebar id="sidebar-settings" title="Story Settings" left shadow width="40rem">
      <div class="px-3 py-2">
        <div>
          <b-form-checkbox v-model="isPlaying">Auto Play</b-form-checkbox>
        </div>
        <ul class="tag-debug">
          <li>slide index: {{slideIndex+1}}/{{slides.length}}</li>
          <li>current slide ID: {{currentSlideId}}</li>
        </ul>
      </div>
    </b-sidebar>
    -->

    <b-row>
      <b-col class="px-0">
        <div class="position-relative">

          <div class="d-none">
            <ul class="tag-debug">
              <li>slide index: {{slideIndex+1}}/{{slides.length}}</li>
              <li>current slide ID: {{currentSlideId}}</li>
            </ul>
          </div>

          <nav :style="cssNav" class="m-0">
            <div v-for="(s, iter) in slides" :key="`header-${s.id}`" class="cursor-pointer" @click="goTo(iter)">
              <div :style="cssCurrentProgress" class="tag-target" :class="{ 'played': (slideIndex>iter), 'playing': (slideIndex===iter) }" :ref="`nav-${s.id}`" />
            </div>
          </nav>

          <section v-for="(s, iter) in slides" :key="`slide-${s.id}`">
            <article v-if="slideIndex===iter" :style="cssDisplay" class="display-area" v-touch:start="touchStartHandler" v-touch:end="touchEndHandler">

              <div class="tag-creator d-flex align-items-center">
                <b-avatar v-if="avatar" :src="avatar.filepath" class="mr-2" size="2.5rem" />
                <div>
                  <h6 class="m-0">{{ storyteller }}</h6>
                  <small class="text-muted m-0"><timeago :datetime="s.created_at" /></small>
                </div>
              </div>

              <!--
              <div v-b-toggle.sidebar-settings class="clickme_to-toggle_settings" role="button"><fa-icon icon="cog" size="2x" class="text-light" /></div>
              -->
              <div class="clickme_to-close_player" role="button"><router-link to="/"><fa-icon icon="times" size="2x" class="text-light" /></router-link></div>

              <div class="nav-arrows w-100 px-1 px-sm-5 d-flex justify-content-between">
                <div @click="doNav('previous')" class="clickme_to-prev_slide" role="button"><fa-icon icon="angle-left"  size="3x" class="text-light" /></div>
                <div @click="doNav('next')"     class="clickme_to-next_slide" role="button"><fa-icon icon="angle-right" size="3x" class="text-light" /></div>
              </div>

              <div class="bg-blur"></div>

              <div v-touch:swipe.top="handleSwipeUp" class="crate-content">
                <article v-if="s.stype==='text'" class="h-100 v-wrap">
                  <p class="h4 text-center v-box text-white w-75">{{ s.content }}</p>
                </article>
                <article v-else-if="s.stype==='image' && s.mediafiles" class="h-100">
                  <img v-if="s.mediafiles[0].is_image" :src="s.mediafiles[0].filepath" />
                  <video v-if="s.mediafiles[0].is_video" autoplay="autoplay" class="OFF-d-block">
                    <source :src="s.mediafiles[0].filepath" type="video/webm" />
                    <source :src="s.mediafiles[0].filepath" type="video/mp4" />
                  </video>
                  <see-more v-if="s.swipe_up_link" :link="s.swipe_up_link"></see-more>
                </article>
              </div>
            </article>
          </section>

        </div>
      </b-col>

    </b-row>

  </div>
</template>

<script>
// A 'slide' is an individual 'story' in the database...
// However 'story' should actually refer to the entire story timeline, composed
// of multiple slides
import _ from 'lodash'
import SeeMore from './SeeMore.vue' // for swipe-up functionality

export default {

  props: {
    storyteller: { type: String, default: '' },
    session_user: { type: Object, default: null },
    timeline: null,
    avatar: null,
    slides: null,
    slideIndexInit: null,
  },

  data: () => ({
    INTERVAL_DELTA: 35, // milliseconds...smaller the number the faster the playback
    slideViewedDuration: 0, // %FIXME

    //slides: null,
    slideIndex: null,

    playerIntervalObj: null, // instace of setInterval

    // slide player controls
    isPlaying: true,
  }),

  computed: {
    isLoading() {
      return !this.timeline || !this.storyteller || !this.slides || !this.session_user
    },

    cssCurrentProgress() {
      return { '--current-progress': `${this.slideViewedDuration}%` }
    },
    cssNav() {
      return { '--grid-template-columns': `repeat(${this.slides.length}, 1fr)` }
    },
    cssDisplay() {
      if (this.slides.length && this.slides[0].mediafiles && this.slides[this.slideIndex].mediafiles[0]) {
        return { '--background-image': `url(${this.slides[this.slideIndex].mediafiles[0].filepath})` }
      }
      return {}
    },

    currentSlideId() {
      return this.slides.length ? this.slides[this.slideIndex].id : null
    }

  },

  methods: {
    async markSlideAsViewed(slide) {
      const payload = {
        viewer_id: this.session_user.id,
        story_id: slide.id,
      }
      console.log('markSlideAsViewed', { payload })
      const response = await axios.post(`/stories/markViewed`, payload)
    },

    doNav(direction) {
      if ( !this.slides || !this.slides.length || this.slideIndex===null ) {
        return
      }
      console.log(`doNav() - ${direction}, index=${this.slideIndex} - ${this.slideIndex+1}/${this.slides.length}`)
      switch (direction) {
        case 'previous':
          if ( (this.slideIndex-1) < 0 ) {
            console.log(`doNav() - emit prev-story-timeline`)
            this.$emit('prev-story-timeline', { foo: 'bar'} )
            this.slideIndex = 0
            this.slideViewedDuration = 0
          } else {
            this.goTo(this.slideIndex - 1)
            console.log(`doNav() - decrement index`)
          }
          break
        case 'next':
          if ( (this.slideIndex+1) >= this.slides.length ) {
            console.log(`doNav() - emit next-story-timeline`)
            this.$emit('next-story-timeline', { foo: 'bar'} )
            this.slideIndex = 0
            this.slideViewedDuration = 0
          } else {
            console.log(`doNav() - increment index`)
            this.goTo(this.slideIndex + 1)
          }
          break
      }
    },

    // sets this.slideIndex
    goTo(index) {

      console.log(`goTo() - index = ${index}`)
      if ( index < 0 ) {
        console.log(`goTo() - min out`)
        this.slideIndex = 0 // min out at first index
        this.slideViewedDuration = 0
      } else if ( index >= this.slides.length ) {
        console.log(`goTo() - max out`)
        this.slideIndex = this.slides.length-1 // max out at last index
        this.slideViewedDuration = 0
      } else {
        console.log(`goTo() - assign this.slideIndex to index = ${index}`)
        this.slideIndex = index
        this.slideViewedDuration = 0
      }
      this.markSlideAsViewed(this.slides[this.slideIndex])

      if (this.isPlaying) {
      }
    },

    handleSwipeUp() {
      const link = this.slides[this.slideIndex].swipe_up_link
      if (link) {
        this.$emit('open-see-more-link')
      }
    },

    startPlayback() {
      if ( this.playerIntervalObj!==null ) {
        clearInterval(this.playerIntervalObj);
      }
      this.playerIntervalObj = setInterval( () => {
        if (this.slideViewedDuration >= 100) {
          this.doNav('next')
        } else {
          this.slideViewedDuration += 1
        }
      }, this.INTERVAL_DELTA)
    },

    stopPlayback() {
      clearInterval(this.playerIntervalObj);
      this.playerIntervalObj = null
    },

    touchStartHandler() {
      this.stopPlayback()
    },
    touchEndHandler() {
      this.startPlayback()
    },

  },

  watch: {
    isPlaying(value) {
      console.log('=== watch isPlaying()')
      if (value) {
        this.startPlayback()
      } else { 
        this.stopPlayback()
      }
    },
  },

  mounted() {
    this.startPlayback()
  },

  created() {
    console.log('=== created()')
    this.slideIndex = this.slideIndexInit
    this.markSlideAsViewed(this.slides[this.slideIndex])
  },

  beforeDestroy() {
    this.stopPlayback()
  },

  components: {
    seeMore: SeeMore
  },

}
</script>

<style lang="scss" scoped>
.display-area {
  position: relative;
  height: 100vh;

  .tag-creator {
    color: #fff;
    position: absolute;
    top: 1.5rem;
    left: 1rem;
    z-index: 200;
  }

  .crate-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    p {
      margin: auto;
    }

    img, video {
      max-width: 100%;
      height: 100%;
      display: block;
      margin: auto;
      object-fit: cover;
    }
  }

  .bg-blur {
    opacity: 0.99;
    //opacity: 0.82;
    //background-image: var(--background-image);
    background: #1f1f1f;

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

#sidebar-settings {
  z-index: 600;
}
.nav-arrows {
  position: absolute;
  top: 45%;
  //bottom: 0;
  left: 0;
  z-index: 500;
  /*
  .clickme_to-next_slide, .clickme_to-prev_slide {
    width: 45%;
    height: 90vh;
  }
    */
}
.clickme_to-toggle_settings {
  position: absolute;
  top: 1.3rem;
  right: 4rem;
  z-index: 200;
}
.clickme_to-close_player {
  position: absolute;
  top: 1.3rem;
  right: 1.5rem;
  z-index: 200;
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

  .cursor-pointer {
    background: rgba(255, 255, 255, 0.55);
    height: 100%;

    .tag-target {
      background: white;
      height: 100%;
      width: 0%;
    }
    .tag-target.played {
      width: 100%;
    }

    .tag-target.playing {
      background: pink;
      width: var(--current-progress);
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
