<template>
  <div class="swiper-slider" v-if="files.length > 0">
    <div v-if="isDragListVisible">
      <draggable
        class="sort-change-div"
        v-model="files"
        :group="'column.components'"
        handle=".handle"
        ghost-class="ghost"
      >
        <div v-for="(element, index) in files" :key="index" class="drag-element">
          <div class="img-wrapper">
            <img v-if="element.type.indexOf('image/') > -1" :src="element.filepath" alt="" />
            <span
              v-if="!element.selected"
              class="unchecked-circle"
              @click="onSelectMediafile(index, true)"
            />
            <span
              v-if="element.selected"
              class="bg-primary checked-circle"
              @click="onSelectMediafile(index, false)"
            >
              {{element.order}}
            </span>
          </div>
          <div class="handle">
            <fa-icon :icon="['fas', 'grip-horizontal']" class="mr-1 text-secondary" size="lg" />
          </div>
        </div>
      </draggable>
    </div>
    <swiper ref="mySwiper" :options="swiperOptions">
      <swiper-slide class="slide">
        <div v-if="!isDragListVisible">
          <div class="swiper-image-wrapper" v-for="(media, index) in files" :key="index">
            <img v-preview:scope-a class="swiper-lazy" :src="media.filepath || media.src" />
          </div>
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
  name: "PreviewSlider",

  props: {
    mediafiles: { type: Array, default: () => ([]) },
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
    files: [],
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
      return this.$refs.mySwiper && this.$refs.mySwiper.$swiper;
    },
  },

  mounted() {
    if (this.mediafiles) {
      this.files = [...this.mediafiles];
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
  },
}
</script>

<style lang="scss" scoped>
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
