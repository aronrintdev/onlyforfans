<template>
  <div class="swiper-slider" v-if="mediafiles.length > 0">
    <div v-if="isDragListVisible">
      <draggable class="sort-change-div" v-model="mediafiles" :group="'column.components'" handle=".handle"
        ghost-class="ghost">
        <div v-for="(element, index) in mediafiles" :key="index" class="drag-element">
          <div class="img-wrapper">
            <img v-if="element.type.indexOf('image/') > -1" :src="element.src" alt="" />
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
          <button :disabled="!applyBtnEnabled" class="btn arrows-btn" @click="applyMediafilesSort">
            <fa-icon :icon="['far', 'chevron-left']" class="mr-1 text-white" size="lg" />
            <fa-icon :icon="['far', 'chevron-right']" class="text-white" size="lg" />
          </button>
        </div>
        <button class="btn confirm-btn" @click="confirmMediafilesSort">
          <fa-icon :icon="['far', 'times']" class="text-white" size="sm" />
        </button>
      </div>
    </div>
    <swiper ref="mySwiper" :options="swiperOptions">
      <swiper-slide class="slide">
        <div v-if="!isDragListVisible">
          <div class="swiper-image-wrapper" v-for="(media, index) in mediafiles" :key="index">
            <img v-preview:scope-a class="swiper-lazy" :src="media.src" />
            <button class="btn btn-primary icon-close" @click="removeMediafile(index)">
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
  </div>
</template>

<script>
import PhotoSwipe from 'photoswipe/dist/photoswipe';
import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default';
import createPreviewDirective from 'vue-photoswipe-directive';
import draggable from 'vuedraggable';

export default {
  name: "UploadMediaPreview",
  props: {
    mediafiles: Array,
  },
  components: {
    draggable,
  },
  directives: {
    preview: createPreviewDirective({
        showAnimationDuration: 0,
        bgOpacity: 0.75
      }, PhotoSwipe, PhotoSwipeUI)
  },
  data: () => ({
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
  }),
  computed: {
    swiper() {
      return this.$refs.mySwiper.$swiper;
    }
  },
  watch: {
    mediafiles() {
      setTimeout(() => {
        this.swiper.update();
      }, 500);
    }
  },
  methods: {
    onSelectMediafile(index, status) {
      const temp = [...this.mediafiles];
      temp[index].selected = status;
      const sortedTemp = _.orderBy(temp, ['order'], ['asc']);
      let order = 0;
      sortedTemp.forEach(item => {
        if (item.selected) { 
          order++;
          const idx = temp.findIndex(it => it.src === item.src);
          temp[idx].order = order;
        }
      });
      this.$emit('change', temp);
      this.applyBtnEnabled = true;
    },
    applyMediafilesSort() {
      const temp = [...this.mediafiles];
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
    removeMediafile(index) {
      const temp = [...this.mediafiles];
      temp.splice(index, 1);
      this.$emit('change', temp);
    },
    openFileUpload() {
      this.$emit('openFileUpload');
    }
  }
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

    svg {
      fill: #00aff0;
      width: 20px;
      height: 20px;
    }
    &:disabled {
      svg {
        fill: rgba(138,150,163,.7);
        pointer-events: none;
      }
    }
    &.arrows-btn {
      background: transparent;
      padding: 0;
      margin: 0;
    }
    &.confirm-btn {
      width: 48px;
      height: 48px;
      border-radius: 1000px;
      border: none;
      background: #00aff0;
      color: #fff;

      svg {
        font-size: 24px;
        width: 1em;
        height: 1em;
        fill: #fff;
      }
    }
  }
}
.swiper-slider {
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
    object-fit: contain;
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
}
</style>
