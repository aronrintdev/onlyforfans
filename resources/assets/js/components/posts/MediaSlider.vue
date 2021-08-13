<template>
  <div class="media-slider">
    <div class="single" v-if="!hasMultipleImages">
      <VideoPlayer :source="mediafiles[0]" v-if="mediafiles[0].is_video"></VideoPlayer>
      <b-img-lazy
        v-if="mediafiles[0].is_image"
        class="d-block photoswipe-thumbnail"
        :src="use_mid && mediafiles[0].has_mid ? mediafiles[0].midFilepath : mediafiles[0].filepath"
        v-touch:tap="tapHandler"
      />
      <div class="background-preview" v-if="mediafiles[0].is_image">
        <b-img-lazy
          class="d-block"
          :src="use_mid && mediafiles[0].has_mid ? mediafiles[0].midFilepath : mediafiles[0].filepath"
        />
      </div>
      <vue-plyr v-if="mediafiles[0].is_audio">
        <audio controls playsinline>
          <source :src="mediafiles[0].filepath" type="audio/webm" />
          <source :src="mediafiles[0].filepath" type="audio/mp3" />
          <source :src="mediafiles[0].filepath" type="audio/ogg" />
        </audio>
      </vue-plyr>
    </div>
    <div class="multiple position-relative" v-if="hasMultipleImages">
      <swiper ref="mySwiper" class="media-slider-swiper" :options="swiperOptions">
        <swiper-slide class="slide" v-for="mf in visualMediafiles" :key="mf.id">
          <VideoPlayer :source="mf" v-if="mf.is_video"></VideoPlayer>
          <b-img-lazy
            v-if="mf.is_image"
            class="d-block photoswipe-thumbnail"
            :src="use_mid && mf.has_mid ? mf.midFilepath : mf.filepath"
          />
          <div class="background-preview" v-if="mf.is_image">
            <b-img-lazy
              class="d-block"
              :src="use_mid && mf.has_mid ? mf.midFilepath : mf.filepath"
            />
          </div>
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
        {{ visualMediafiles.length }}
      </div>
    </div>
    <div class="audio-preview" v-if="hasMultipleImages && hasAudioFiles">
      <template v-for="(audiofile, index) in mediafiles">
        <vue-plyr class="mx-2" v-if="audiofile.is_audio" :key="index">
          <audio controls playsinline>
            <source :src="audiofile.filepath" type="audio/webm" />
            <source :src="audiofile.filepath" type="audio/mp3" />
            <source :src="audiofile.filepath" type="audio/ogg" />
          </audio>
        </vue-plyr>
      </template>
    </div>
  </div>
</template>

<script>
import PhotoSwipe from 'photoswipe/dist/photoswipe'
import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default'
import createPreviewDirective from 'vue-photoswipe-directive'
import { eventBus } from '@/eventBus'
import VideoPlayer from '@components/videoPlayer'

export default {
  props: {
    //mediafile_count: null, // was post.mediafile_count
    mediafiles: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
  },

  components: {
    VideoPlayer,
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
    visualMediafiles() {
      return this.mediafiles.filter(file => !file.is_audio)
    },
    hasMultipleImages() {
      return this.mediafiles.filter(file => !file.is_audio).length > 1
    },
    imageScope() {
      return this.mediafiles[0].resource_id
    },
    hasAudioFiles() {
      return this.mediafiles.filter(file => file.is_audio).length > 1
    },
    swiper() {
      return this.$refs.mySwiper && this.$refs.mySwiper.$swiper
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
    tapCount: 0,
  }),

  methods: {
    tapHandler() {
      this.tapCount++;
      setTimeout(() => {
        if (this.tapCount == 1) {
          const imagefiles = this.mediafiles.filter(file => file.is_image)
          const items = imagefiles.map(file => ({ src: file.filepath }))
          this.$Pswp.open({
            items,
            options: {
              showAnimationDuration: 0,
              hideAnimationDuration: 0,
              bgOpacity: 0.5
            }
          });
        }
        this.tapCount = 0;
      }, 500);
    },
    doubleTapHandler() {
      this.$emit('doubleTap');
    },
  },

  mounted() {
    this.swiper?.on('tap', this.tapHandler);
    this.swiper?.on('doubleTap', this.doubleTapHandler);
  },
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
  .background-preview {
    display: none;
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

.audio-preview {
  margin-top: 10px;
}
</style>
