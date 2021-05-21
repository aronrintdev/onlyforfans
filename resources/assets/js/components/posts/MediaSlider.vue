<template>
  <div class="position-relative">
    <swiper class="media-slider" :options="swiperOptions">
      <swiper-slide class="slide" v-for="(mf, idx) in mediafiles" :key="mf.id">
        <video v-if="mf.is_video" controls="controls" poster="poster.png" class="d-block">
          <source :src="mf.filepath" type="video/webm" />
          <source :src="mf.filepath" type="video/mp4" />
        </video>
        <img
          v-if="mf.is_image"
          class="d-block"
          :src="use_mid && mf.has_mid ? mf.midFilepath : mf.filepath"
          :alt="mf.mfname"
        />
      </swiper-slide>
      <div v-if="isNavVisible" class="swiper-button-prev" slot="button-prev"></div>
      <div v-if="isNavVisible" class="swiper-button-next" slot="button-next"></div>
      <div v-if="isNavVisible" class="swiper-pagination" slot="pagination"></div>
    </swiper>
    <div v-if="isNavVisible" class="mediafile-count text-white position-absolute">
      <b-icon icon="images" font-scale="1" variant="light" class="d-inline my-auto" />
      {{ mediafiles.length }}
    </div>
  </div>
</template>

<script>
import { eventBus } from '@/app'

export default {
  props: {
    //post: null,
    //mediafile_count: null, // was post.mediafile_count
    mediafiles: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
  },

  computed: {
    isNavVisible() {
      return this.mediafiles.length > 1
    },
  },

  data: () => ({
    swiperOptions: {
      lazy: true,
      slidesPerView: 'auto',
      observer: true,
      observeParents: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      pagination: {
        el: '.swiper-pagination',
      },
    },
  }),

  methods: {},

  mounted() {},
  created() {},
  watch: {},
}
</script>

<style scoped>
.mediafile-count {
  background-color: rgba(0, 0, 0, 0.5);
  padding: 0 6px;
  border-radius: 6px;
  bottom: 0.5rem;
  right: 1rem;
  z-index: 1;
}

/* default settings for single feed view - for grid view these
are overriddent in custom.scss */
body .media-slider {
  max-height: calc(100vh - 100px);
}
body .media-slider .slide {
  width: 100%;
  height: 100%;
  flex-direction: column;
  flex-wrap: wrap;
  align-items: flex-middle;
}
body .media-slider .slide video {
  object-fit: contain;
  width: 100%;
}
body .media-slider .slide img {
  object-fit: contain;
  width: 100%;
}
body .media-slider .slide.active {
  display: flex;
}
body .media-slider .slide .embed-responsive {
}
</style>
