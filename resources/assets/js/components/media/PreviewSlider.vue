<template>
  <div class="swiper-slider" v-if="files.length > 0">
    <!-- %FILE: resources/assets/js/components/media/PreviewSlider.vue -->
    <swiper ref="mySwiper" :options="swiperOptions">
      <swiper-slide class="slide">
        <div>
          <div v-observe-visibility="onFirstVisible"> </div>
          <div
            class="swiper-image-wrapper"
            v-for="(media, index) in files"
            :key="index"
          >
            <b-img
              v-if="media.is_image && media.access"
              class="swiper-lazy"
              :src="media.filepath || media.src"
              @click="openPhotoSwipe(index)"
            />
            <b-img
              v-if="media.is_image && !media.access"
              class="swiper-lazy"
              :src="media.blurFilepath"
              @click="openPhotoSwipe(index)"
            />
            <div v-if="media.is_video" class="swiper-lazy video" @click="openPhotoSwipe(index)">
              <video>
                <source :src="media.filepath" :type=" media.mimetype || media.type" />
              </video>
              <fa-icon :icon="['far', 'play-circle']" class="text-white icon-play" />
            </div>
          </div>
          <div v-observe-visibility="onLastVisible"> </div>
        </div>
      </swiper-slide>
    </swiper>
    <transition name="indicator-fade-prev">
      <div v-if="!firstVisible" class="indicator prev" @click="onClickPrev">
        <fa-icon icon="chevron-left" />
      </div>
    </transition>
    <transition name="indicator-fade-next">
      <div v-if="!lastVisible" class="indicator next" @click="onClickNext">
        <fa-icon icon="chevron-right" />
      </div>
    </transition>
    <div class="audio" v-for="(audio, index) in audioFiles" :key="index">
      <vue-plyr>
        <audio controls playsinline>
          <source :src="audio.filepath" type="audio/webm" />
          <source :src="audio.filepath" type="audio/mp3" />
          <source :src="audio.filepath" type="audio/ogg" />
        </audio>
      </vue-plyr>
    </div>
  </div>
</template>

<script>
import _ from 'lodash'
import Vue from 'vue'
import PhotoSwipe from 'photoswipe/dist/photoswipe'
import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default'
import createPreviewDirective from 'vue-photoswipe-directive'

import AudioPlayer from '@components/audioPlayer'
import VideoPlayer from '@components/videoPlayer'

export default {
  comments: true, // %FIXME
  name: 'PreviewSlider',

  props: {
    mediafiles: { type: Array, default: () => ([]) },
  },

  directives: {
    preview: createPreviewDirective({
      showAnimationDuration: 0,
      bgOpacity: 0.75
    }, PhotoSwipe, PhotoSwipeUI)
  },

  data: () => ({
    files: [],
    audioFiles: [],
    swiperOptions: {
      lazy: true,
      slidesPerView: 'auto',
      observer: true,
      freeMode: true,
      freeModeMomentum: true,
      mousewheel: true,
      watchOverflow: true,
      spaceBetween: true,
      watchSlidesVisibility: true,
    },
    isDragListVisible: false,
    applyBtnEnabled: false,
    firstVisible: true,
    lastVisible: false,
  }),

  computed: {
    swiper() {
      return this.$refs.mySwiper && this.$refs.mySwiper.$swiper;
    },
  },

  mounted() {
    if (this.mediafiles) {
      this.files = this.mediafiles.filter(file => file.is_image || file.is_video);
      this.audioFiles = this.mediafiles.filter(file => file.is_audio);
      this.$nextTick(() => {
        this.swiper?.update()
      })
    }
  },

  watch: {
    mediafiles() {
      this.files = [...this.mediafiles];
      this.$nextTick(() => {
        this.swiper?.update()
      })
    },
  },

  methods: {
    onClickPrev() {
      this.$refs.mySwiper.swiperInstance.slideTo(0, 400, false)
    },

    onClickNext() {
      this.$refs.mySwiper.swiperInstance.slideTo(this.files.length + 1, 400, false)
    },

    onFirstVisible(isVisible) {
      this.firstVisible = isVisible
    },

    onLastVisible(isVisible) {
      this.lastVisible = isVisible
    },

    onSelectMediafile(index, status) {
      const temp = [...this.files];
      temp[index].selected = status;
      const sortedTemp = _.orderBy(temp, ['order'], ['asc']);
      let order = 0;
      sortedTemp.forEach(item => {
        if (item.selected) {
          order++;
          const idx = temp.findIndex(it => it.filepath === item.filepath);
          temp[idx].order = order;
        }
      });
      this.files = temp;
      this.applyBtnEnabled = true;
    },

    openPhotoSwipe(index) {
      this.$Pswp.open({
        items: this.files.map(file => {
          if (file.mimetype.indexOf('video/') > -1) {
            return ({
              html: new Vue({
                ...VideoPlayer,
                propsData: {
                  source: file
                }
              }).$mount().$el,
            })
          }
          if (file.is_audio) {
            return ({
              html: new Vue({
                ...AudioPlayer,
                propsData: {
                  source: file
                }
              }).$mount().$el,
            })
          }
          return ({
            src: file.filepath,
          })
        }),
        options: {
          index,
          showAnimationDuration: 0,
          bgOpacity: 0.75
        },
      })
    },

  },
}
</script>

<style lang="scss" scoped>
.swiper-slider {
  position: relative;
  padding: 12px 8px 8px;

  & > div {
    display: flex;
  }
  .slide {
    width: unset;
    flex: 0 0 auto;

    & > div {
      display: flex;
      width: 100%;
    }

    .slide-btn {
      height: 144px;
      opacity: 0.7;
    }
  }

  .swiper-lazy {
    height: 144px;
    border-radius: 10px;
    margin-right: 8px;
    width: auto;
    overflow: hidden;
    object-fit: contain;
    cursor: pointer;

    &.video {
      position: relative;

      &::before {
        content: "";
        display: block;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.2);
        position: absolute;
        top: 0;
        left: 0;
      }

      video {
        height: 100%;
      }

      .icon-play {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 34px;
      }
    }
  }

  audio.swiper-lazy {
    width: 300px;
    outline: none;
  }

  .swiper-image-wrapper {
    position: relative;

    .icon-close {
      position: absolute;
      top: 8px;
      right: 16px;
      border: none;
      padding: 1px 5px;

      svg {
        width: 1em;
      }
    }
  }

  .audio-file-viewer {
    flex-flow: wrap;

    .audio {
      width: 100%;
      margin-top: 10px;
      position: relative;

      audio {
        width: 100%;
      }

      .icon-close {
        position: absolute;
        top: 0;
        right: 10px;
        transform: translate(50%, -50%);
        line-height: 1;
        padding: 3px;

        svg {
          width: 14px;
          height: 14px;
        }
      }
    }
  }
}

.indicator {
  position: absolute;
  top: 41%;
  background-color: var(--light, white);
  border-radius: .5rem;
  padding: .5rem;
  cursor: pointer;

  z-index: 2;
  &.prev {
    left: 1rem;
  }
  &.next {
    right: 1rem;
  }
}

.indicator-fade-prev-enter-active,
.indicator-fade-next-enter-active {
  transition: all .2s ease;
}

.indicator-fade-prev-enter-to,
.indicator-fade-next-enter-to {
  opacity: 75%;
}

.indicator-fade-prev-leave-active,
.indicator-fade-next-leave-active {
  transition: all .2s ease;
}
.indicator-fade-prev-enter,
.indicator-fade-next-enter,
.indicator-fade-prev-leave-to,
.indicator-fade-next-leave-to {
  opacity: 0;
}

.indicator-fade-prev-enter,
.indicator-fade-prev-leave-to {
  transform: translateX(-10px);
}

.indicator-fade-next-enter,
.indicator-fade-next-leave-to {
  transform: translateX(10px);
}

</style>
