<template>
  <div class="position-relative">
    <b-carousel
      id="carousel-1"
      v-model="slide"
      :interval="0"
      :controls="isNavVisible"
      indicators
      background="#ababab"
      style="text-shadow: 1px 1px 2px #333;"
      @sliding-start="onSlideStart"
      @sliding-end="onSlideEnd"
    >

      <b-carousel-slide v-for="(mf, idx) in post.mediafiles" :key="mf.id">
        <template #img>
          <!--
          <b-embed v-if="mf.is_video" type="video" controls poster="poster.png" class="d-block">
            <source :src="mf.filepath" type="video/webm">
            <source :src="mf.filepath" type="video/mp4">
          </b-embed>
          -->
          <video v-if="mf.is_video" controls="controls" poster="poster.png" class="d-block">
            <source :src="mf.filepath" type="video/webm">
            <source :src="mf.filepath" type="video/mp4">
          </video>
          <img v-if="mf.is_image"
            class="d-block"
            :src="(use_mid && mf.has_mid) ? mf.midFilepath : mf.filepath"
            :alt="mf.mfname"
          >
        </template>
      </b-carousel-slide>

    </b-carousel>
    <div v-if="post.mediafile_count" class="mediafile-count text-white position-absolute"><b-icon icon="images" font-scale="1" variant="light" class="d-inline my-auto" /> {{ post.mediafile_count }}</div>
  </div>
</template>

<script>
import { eventBus } from '@/app'

export default {

  props: {
    post: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
  },

  computed: {
    isLoading() {
      return !this.post || !this.session_user
    },
    isNavVisible() {
      return this.post.mediafiles.length > 1
    }
  },

  data: () => ({
        slide: 0,
        sliding: null
  }),

  methods: {

    onSlideStart(slide) {
      this.sliding = true
    },

    onSlideEnd(slide) {
      this.sliding = false
    },
  },

  mounted() { },
  created() { },
  watch: { },
  components: { },
}
</script>

<style scoped>

.mediafile-count {
  bottom: 0.5rem;
  right: 1rem;
}

body .carousel.slide .carousel-item video {
  object-fit: contain;
  /*
  object-fit: cover;
  height: 350px;
   */
  width: 100%;
}
body .carousel.slide .carousel-item img {
  object-fit: contain;
  /*
  object-fit: cover;
  height: 350px;
   */
  width: 100%;
}
body .carousel.slide .carousel-item.active {
  display: flex;
}
body .carousel.slide .carousel-item {
  flex-direction: column;
  flex-wrap: wrap;
  align-items: flex-middle;
  /*
  width: 100%;
   */
}
body .carousel.slide .carousel-item .embed-responsive{
}
</style>
