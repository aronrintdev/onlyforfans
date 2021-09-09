<template>
  <div class="media-slider">
    <div class="single" v-if="!hasMultipleImages" v-touch:tap="tapHandler">
      <VideoPlayer ref="video_player" :play="playVideo" :source="mediafiles[0]" v-if="mediafiles[0].is_video"></VideoPlayer>
      <div class="wrap">
        <b-img-lazy
          v-if="mediafiles[0].is_image"
          class="d-block photoswipe-thumbnail"
          :src="use_mid && mediafiles[0].has_mid ? mediafiles[0].midFilepath : mediafiles[0].filepath"
        />
      </div>
      <div class="background-preview">
        <b-img-lazy
          v-if="mediafiles[0].is_image"
          class="d-block"
          :src="use_mid && mediafiles[0].has_mid ? mediafiles[0].midFilepath : mediafiles[0].filepath"
        />
        <video v-if="mediafiles[0].is_video">
          <source :src="`${mediafiles[0].filepath}#t=2`" type="video/mp4" />
          <source :src="`${mediafiles[0].filepath}#t=2`" type="video/webm" />
        </video>
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
        <swiper-slide class="slide" v-for="(mf, index) in visualMediafiles" :key="mf.id">
          <VideoPlayer ref="video_player" :play="playVideo" :source="mf" v-if="mf.is_video"></VideoPlayer>
          <div class="wrap">
            <b-img
              v-if="mf.is_image"
              :data-index="index"
              class="d-block swiper-lazy photoswipe-thumbnail"
              :src="use_mid && mf.has_mid ? mf.midFilepath : mf.filepath"
            />
          </div>
          <div class="background-preview" v-if="mf.is_image">
            <b-img
              class="swiper-lazy d-block"
              :src="use_mid && mf.has_mid ? mf.midFilepath : mf.filepath"
            />
          </div>
        </swiper-slide>
        <div class="swiper-button-prev" slot="button-prev" @click="preventEvent()">
          <fa-icon icon="chevron-circle-left" size="2x" color="text-primary" />
        </div>
        <div class="swiper-button-next" slot="button-next" @click="preventEvent()">
          <fa-icon icon="chevron-circle-right" size="2x" color="text-primary" />
        </div>
        <div class="swiper-pagination" slot="pagination"></div>
      </swiper>
      <div v-if="hasMultipleImages" class="mediafile-count text-white position-absolute">
        <fa-icon icon="images" class="d-inline my-auto" />
        {{ visualMediafiles.length }}
      </div>
    </div>
    <div class="audio-preview" v-if="hasMultipleImages && hasAudioFiles" v-touch:tap="tapHandler">
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
import { eventBus } from '@/eventBus'
import VideoPlayer from '@components/videoPlayer'

export default {
  props: {
    //mediafile_count: null, // was post.mediafile_count
    mediafiles: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
    imageIndex: 0,
  },

  components: {
    VideoPlayer,
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
    },
    swiperOptions() {
      return {
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
        initialSlide: this.imageIndex,
      }
    }
  },

  data: () => ({
    tapCount: 0,
    playVideo: false,
  }),

  methods: {
    tapHandler(params) {
      this.tapCount++;
      setTimeout(() => {
        if (this.tapCount == 1) {
          const imagefiles = this.mediafiles.filter(file => file.is_image)
          const videofiles = this.mediafiles.filter(file => file.is_video)
          if (imagefiles.length > 0) {
            const index = $(params.target).data('index') || 0;
            const items = imagefiles.map(file => ({ src: file.filepath }))
            this.$Pswp.open({
              items,
              options: {
                showAnimationDuration: 0,
                hideAnimationDuration: 0,
                bgOpacity: 0.5,
                index,
              },
            });
          }
          if (videofiles.length > 0) {
            this.playVideo = !this.playVideo;
          }
        } else if (this.tapCount == 2) {
          this.$emit('doubleTap');
        }
        this.tapCount = 0;
      }, 200);
    },
    preventEvent() {
      this.tapCount = 0;
    }
  },

  mounted() {
    this.swiper?.on('tap', this.tapHandler);
  },
  created() {},
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

<style lang="scss">
.media-slider {
  .single, .slide {
    position: relative;

    .wrap {
      height: calc(100vh - 300px);
      position: relative;
      z-index: 1;
      max-width: 100vw;

      .photoswipe-thumbnail {
        height: 100%;
        width: auto;
        margin: auto;
        max-width: 100%;
        -o-object-fit: contain;
        object-fit: contain;
        position: relative;
        z-index: 2;
      }

      .video-js.vjs-fluid {
        width: 100%;
        max-width: 100%;
        max-height: 100%;
        height: 100%;
        padding-top: 0;
        background: transparent;
      }
    }

    img {
      position: relative;
      z-index: 1;
    }

    .background-preview {
      display: none;
    }

    .wrap + .background-preview {
      background-color: rgba(0, 0, 0, 0.8);
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      display: block;
      overflow: hidden;

      video, img {
        object-fit: cover;
        opacity: 0.4;
        transform: scale(1.1);
        height: 100%;
      }
    }
  }

  .video-js {
    .vjs-tech {
      pointer-events: none;
    }
  }
}
</style>
