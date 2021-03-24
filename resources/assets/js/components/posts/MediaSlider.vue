<template>
  <div>
    <b-carousel
      id="carousel-1"
      v-model="slide"
      :interval="0"
      controls
      indicators
      background="#ababab"
      img-width="1024"
      img-height="480"
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
            class="d-block OFF-img-fluid OFF-w-100"
            :src="(use_mid && mf.has_mid) ? mf.midFilepath : mf.filepath"
            :alt="mf.mfname"
          >
        </template>
      </b-carousel-slide>

    </b-carousel>
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
