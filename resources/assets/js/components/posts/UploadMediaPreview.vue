<template>
  <div class="swiper-slider">

    <!-- %FILE: resources/assets/js/components/posts/UploadMediaPreview.vue -->

    <div v-if="isDragListVisible">

      <draggable class="sort-change-div" v-model="files" :group="'column.components'" handle=".handle"
        ghost-class="ghost">
        <div v-for="(element, index) in files" :key="index" class="drag-element">
          <div class="img-wrapper">
            <b-img-lazy v-if="element.type.indexOf('image/') > -1" :src="element.filepath || element.src" alt="" />
            <video v-if="element.type.indexOf('video/') > -1">
              <source :src="element.filepath" :type="element.type" />
            </video>
            <span v-if="!element.selected" class="unchecked-circle" @click="onSelectMediafile(index, true)"></span>
            <span v-if="element.selected" class="bg-primary checked-circle"
              @click="onSelectMediafile(index, false)">{{element.order}}</span>
          </div>
          <div class="handle">
            <fa-icon :icon="['fas', 'grip-horizontal']" class="mr-1 text-secondary" size="lg" />
          </div>
        </div>
      </draggable>

      <div class="sort-action-btns">
        <div>
          <button :disabled="!applyBtnEnabled" class="btn btn-secondary btn-sm h-100 mb-3" @click="applyMediafilesSort">
            <fa-icon :icon="['far', 'chevron-left']" class="mr-2 text-white" size="sm" />
            <fa-icon :icon="['far', 'chevron-right']" class="text-white" size="sm" />
          </button>
        </div>
        <button class="btn btn-primary p-2 border-0" @click="confirmMediafilesSort">
          <fa-icon :icon="['far', 'check']" class="text-white" size="lg" />
        </button>
      </div>

    </div>

    <swiper ref="mySwiper" :options="swiperOptions" v-if="files.length"> <!-- vue-awesome-swiper -->
      <swiper-slide class="slide">
        <div v-if="!isDragListVisible">
          <div class="swiper-image-wrapper" v-for="(mf, index) in files" :key="mf.id">
            <b-img @click="openPhotoSwipe(index)" class="swiper-lazy tag-image" :src="mf.filepath || mf.src" v-if="mf.type && mf.type.indexOf('image/') > -1" />
            <div class="swiper-lazy video tag-video" @click="openPhotoSwipe(index)" v-if="mf.type && mf.type.indexOf('video/') > -1">
              <video>
                <source :src="mf.filepath" :type="mf.type" />
              </video>
              <fa-icon :icon="['far', 'play-circle']" class="text-white icon-play" />
            </div>
            <button class="btn btn-primary icon-close" @click="removeMediafile(mf)">
              <fa-icon :icon="['far', 'times']" class="text-white" size="sm" />
            </button>
          </div>
          <button class="btn btn-secondary btn-lg mr-3 slide-btn" @click="isDragListVisible = true">
            <fa-icon :icon="['far', 'chevron-left']" class="mr-1 text-white" size="lg" />
            <fa-icon :icon="['far', 'chevron-right']" class="text-white" size="lg" />
          </button>
          <button class="btn btn-secondary btn-lg slide-btn" @click="openFileUpload">
            <fa-icon :icon="['far', 'plus']"  size="lg" class="mx-1 text-white" />
          </button>
        </div>
      </swiper-slide>
    </swiper>

    <div class="audio-file-viewer" v-if="audioFiles.length">
      <div class="audio" v-for="(audio, index) in audioFiles" :key="index">
        <vue-plyr>
          <audio controls playsinline>
            <source :src="audio.filepath" type="audio/webm" />
            <source :src="audio.filepath" type="audio/mp3" />
            <source :src="audio.filepath" type="audio/ogg" />
          </audio>
        </vue-plyr>
        <button class="btn btn-primary icon-close" @click="removeAudiofile(audio)">
          <fa-icon :icon="['far', 'times']" class="text-white" size="sm" />
        </button>
      </div>
    </div>

  </div>
</template>

<script>
import { Vue } from 'vue-property-decorator';
import draggable from 'vuedraggable';

import VideoPlayer from "@components/videoPlayer";

export default {
  name: "UploadMediaPreview",
  comments: true, // %FIXME

  props: {
    mediafiles: { type: Array, default: () => ([]) },
  },

  components: {
    draggable,
  },

  data: () => ({
    files: [],
    audioFiles: [],
    swiperOptions: {
      lazy: {
        loadPrevNext: true
      },
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
  }),

  computed: {
    swiper() {
      return this.$refs.mySwiper && this.$refs.mySwiper.$swiper;
    },
  },

  mounted() {
    if (this.mediafiles.length > 0) {
      this.files = this.mediafiles.filter(file => file.type && file.type.indexOf('audio/') < 0);
      this.audioFiles = this.mediafiles.filter(file => file.type && file.type.indexOf('audio/') > -1);
      this.$nextTick(() => {
        $('.swiper-lazy').on('load', () => {
          this.$refs.mySwiper.updateSwiper();
        })
      })
    }
  },

  watch: {
    mediafiles(value) {
      this.files = value.filter(file => file.type && file.type.indexOf('audio/') < 0);
      this.audioFiles = value.filter(file => file.type && file.type.indexOf('audio/') > -1);
      this.$nextTick(() => {
        $('.swiper-lazy').on('load', () => {
          this.$refs.mySwiper.updateSwiper();
        })
      })
    },
  },

  methods: {

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

    applyMediafilesSort() {
      const temp = [...this.files];
      const sortedTemp = _.orderBy(temp, ['order'], ['asc']);
      sortedTemp.forEach(item => {
        item.order = undefined;
        item.selected = undefined;
      });
      this.$emit('change', sortedTemp);
      this.applyBtnEnabled = false;
    },

    confirmMediafilesSort() {
      this.applyMediafilesSort();
      this.isDragListVisible = false;
    },

    removeMediafile(mediafile) {
      const idx = this.mediafiles.findIndex(file => file.type === mediafile.type && file.filepath === mediafile.filepath);
      this.$emit('remove', idx);
    },

    openFileUpload() {
      this.$emit('openFileUpload');
    },

    openPhotoSwipe(index) {
      console.log('openPhotoSwipe', {
        index
      })
      this.$Pswp.open({
        items: this.files.map(file => {
          if (file.type.indexOf('video/') > -1) {
            return ({
              html: new Vue({
                ...VideoPlayer,
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
      });
    },
    removeAudiofile(audiofile) {
      const idx = this.mediafiles.findIndex(file => file.type === audiofile.type && file.filepath === audiofile.filepath);
      this.$emit('remove', idx);
    },
  },
}
</script>

<style lang="scss" scoped>
.sort-change-div {
  flex: 1;
  display: flex;
  align-items: center;

  .drag-element {
    width: 96px;
    margin: 0 4px;
    background: rgba(138,150,163,.12);
    border-radius: 6px;
    position: relative;

    &:last-child {
      margin-right: 0;
    }
    .img-wrapper {
      position: relative;

      &::before {
        content: "";
        display: block;
        background: rgba(0, 0, 0, 0.2);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
      }

      img, video {
        margin: 0;
        width: 100%;
        max-width: unset;
        border-radius: 6px 6px 0 0;
        height: 96px;
        object-fit: cover;
      }
      .audio {
        font-size: 11px;
        height: 96px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 8px;
        border-radius: 6px 6px 0 0;
        background: rgb(138 150 163 / 35%);
        border-bottom: solid 1px #fff;
        span {
          display: block;
          width: 100%;
        }
      }
      .unchecked-circle,
      .checked-circle {
        width: 30px;
        height: 30px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border-radius: 50%;
        border: 2px solid #fff;
        cursor: pointer;
      }
      .checked-circle {
        color: #fefefe;
        font-size: 18px;
        line-height: 28px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
      }
    }
    .handle {
      color: rgba(138,150,163,.7);
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      border: none;
      background: none;
      cursor: pointer;
      svg {
        font-size: 24px;
        width: 1em;
        height: 1em;
        display: inline-block;
        fill: currentColor;
      }
    }
  }
}
.sort-action-btns {
  display: flex;
  flex-direction: column;
  align-items: center;

  & > div {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .btn {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}
.swiper-slider {
  padding: 0;

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
</style>
