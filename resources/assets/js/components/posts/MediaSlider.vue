<template>
  <div class="media-slider position-relative">
    <div v-if="!hasMultipleImages">
      <video v-if="mediafiles[0].is_video" controls="controls" poster="poster.png" class="d-block">
        <source :src="mediafiles[0].filepath" type="video/webm" />
        <source :src="mediafiles[0].filepath" type="video/mp4" />
      </video>
      <img
        v-preview
        v-if="mediafiles[0].is_image"
        class="d-block"
        :src="use_mid && mediafiles[0].has_mid ? mediafiles[0].midFilepath : mediafiles[0].filepath"
        :alt="mediafiles[0].mfname"
      />
    </div>
    <swiper v-if="hasMultipleImages" class="media-slider-swiper" :options="swiperOptions">
      <swiper-slide class="slide" v-for="(mf, idx) in mediafiles" :key="mf.id">
        <video v-if="mf.is_video" controls="controls" poster="poster.png" class="d-block">
          <source :src="mf.filepath" type="video/webm" />
          <source :src="mf.filepath" type="video/mp4" />
        </video>
        <img
          v-preview:[imageScope]="imageScope"
          v-if="mf.is_image"
          class="d-block"
          :src="use_mid && mf.has_mid ? mf.midFilepath : mf.filepath"
          :alt="mf.mfname"
        />
      </swiper-slide>
      <div class="swiper-button-prev" slot="button-prev">
        <fa-icon icon="chevron-circle-left" size="2x" color="text-primary" />
      </div>
      <div class="swiper-button-next" slot="button-next">
        <fa-icon icon="chevron-circle-right" size="2x" color="text-primary" />
      </div>
      <div class="swiper-pagination" slot="pagination"></div>
    </swiper>
    <div v-if="hasMultipleImages" class="mediafile-count text-white position-absolute">
      <fa-icon icon="images" class="d-inline my-auto" />
      {{ mediafiles.length }}
    </div>
  </div>
</template>

<script>
import PhotoSwipe from 'photoswipe/dist/photoswipe'
import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default'
import createPreviewDirective from 'vue-photoswipe-directive'
import { eventBus } from '@/app'

export default {
  props: {
    //post: null,
    //mediafile_count: null, // was post.mediafile_count
    mediafiles: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
  },

  directives: {
    preview: createPreviewDirective(
      {
        showAnimationDuration: 0,
        hideAnimationDuration: 0,
        bgOpacity: 0.5,
      },
      PhotoSwipe,
      PhotoSwipeUI
    ),
  },

  computed: {
    hasMultipleImages() {
      return this.mediafiles.length > 1
    },
    imageScope() {
      return this.mediafiles[0].resource_id
    }
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

<style lang="scss" scoped>
$media-height: calc(100vh - 300px);

.media-slider {
  img {
    height: $media-height;
    object-fit: cover;
    width: 100%;
  }

  video {
    // TODO: adjust when adding videos
    height: $media-height;
    object-fit: contain;
    width: 100%;
  }
}

.mediafile-count {
  background-color: rgba(0, 0, 0, 0.5);
  padding: 0 6px;
  border-radius: 6px;
  bottom: 0.5rem;
  right: 1rem;
  z-index: 1;
}
</style>
