<template>
  <div class="w-100">
    <div v-masonry transition-duration="0" item-selector=".item" class="masonry-container" >
      <div
        v-masonry-tile class="item"
        :class="mf.classList"
        v-for="(mf, index) in files"
        :key="index"
        v-observe-visibility="index === files.length - 1 ? loadMore : false"
        @click="openPost(mf.post, index)"
      >
        <div class="media-wrapper" v-if="mf.is_image" >
          <img
            :src="mf.filepath"
            alt=""
          />
          <div class="icon-images" v-if="mf.mediaCount > 1">
            <fa-icon :icon="['fas', 'images']" class="text-white icon" />
          </div>
        </div>
        <div class="media-wrapper" v-if="mf.is_video" >
          <video>
            <source :src="`${mf.filepath}#t=2`" :type="mf.mimetype" />
          </video>
          <div class="icon-video">
            <fa-icon :icon="['fas', 'play']" class="text-white icon" />
          </div>
        </div>
      </div>
    </div>
    <div class="masonry-container skeleton" v-if="loading">
      <div class="item" v-for="idx in 9" :key="idx">
        <b-skeleton-img animation="fade" aspect="5:5"></b-skeleton-img>
      </div>
    </div>
  </div>
</template>

<script>
import { eventBus } from '@/eventBus'

export default {
  props: {
    mediafiles: {
      type: Array,
      default: [],
    },
    loading: {
      type: Boolean,
      default: true,
    }
  },

  data: () => ({
    files: [],
    currentIndex: 0,
  }),

  mounted() {
    this.getMasonryFiles(this.mediafiles);
  },

  watch: {
    mediafiles(newV, oldV) {
      this.getMasonryFiles(newV);
    }
  },

  methods: {
    getMasonryFiles(mediafiles) {
      const files = mediafiles.map((mediafile, idx) => {
        return {
          ...mediafile,
          classList: this.getClassList(mediafiles, idx),
        }
      })
      files.forEach((file, idx) => {
        if (file.is_video && idx % 3 === 2) {
          const temp = files[idx];
          files[idx] = files[idx - 1];
          files[idx - 1] = temp;
        }
      })
      this.files = files;
    },
    getClassList(mediafiles, index) {
      const currentRowStart = index - index % 3;
      const currentRowMediafiles = mediafiles.slice(currentRowStart, currentRowStart + 3);
      const prevRowMediafiles = mediafiles.slice(currentRowStart - 3 , currentRowStart);
      let videoCount = 0, prevVideoCount = 0;
      currentRowMediafiles.forEach(file => {
        if (file.is_video) {
          videoCount++;
        }
      })
      prevRowMediafiles.forEach(file => {
        if (file.is_video) {
          prevVideoCount++;
        }
      })
      if (prevVideoCount > 0 || videoCount > 1) {
        if (mediafiles[index].is_video) {
          return 'video float-left';
        }
        return 'float-left';
      }
      // Large Video
      if (videoCount === 1) {
        if (mediafiles[currentRowStart].is_video) {
          if (index === currentRowStart) {
            return 'video video-lg float-left';
          }
          return 'float-right';
        }
        if (!mediafiles[currentRowStart].is_video) {
          if (mediafiles[index].is_video) {
            return 'video video-lg float-right';
          }
          return 'float-left';
        }
      }
      return 'float-left';
    },
    loadMore(isVisible) {
      if (isVisible) {
        this.$emit('loadMore');
      }
    },
    openPost(p, index) {
      this.currentIndex = index;
      eventBus.$off('post-modal-actions');
      eventBus.$emit('open-modal', { key: 'show-post', data: { post: p, showArrows: true } });
      eventBus.$on('post-modal-actions', this.moveNextPrevPost);
    },
    async moveNextPrevPost(action) {
      if (action === 'prev' && this.currentIndex > 0) {
        if (this.files.length > this.currentIndex + 1) {
          eventBus.$emit('open-modal', { key: 'show-post', data: { post: this.files[this.currentIndex - 1].post, showArrows: true } })
          this.currentIndex -= 1
        } else {
          const response = await axios.get(route('timelines.publicfeed'), {
            params: {
              page: this.currentIndex,
              take: 1,
            }
          });
          if (response.data.data.length) {
            const newpost = response.data.data[0];
            eventBus.$emit('open-modal', { key: 'show-post', data: { post: newpost, showArrows: true } })
            this.currentIndex -= 1
          }
        }
      }
      if (action === 'next') {
        if (this.files.length > this.currentIndex + 1) {
          eventBus.$emit('open-modal', { key: 'show-post', data: { post: this.files[this.currentIndex + 1].post, showArrows: true } })
          this.currentIndex += 1
        } else {
          const response = await axios.get(route('timelines.publicfeed'), {
            params: {
              page: this.currentIndex + 2,
              take: 1,
            }
          });
          if (response.data.data.length) {
            const newpost = response.data.data[0];
            eventBus.$emit('open-modal', { key: 'show-post', data: { post: newpost, showArrows: true } })
            this.currentIndex += 1
          }
        }
      }
    }
  }
}
</script>

<style lang="scss">
.masonry-container {
  max-width: 1170px;
  margin: auto;
  height: auto !important;

  &::after {
    content: '';
    display: block;
    clear: both;
  }

  & > div {
    position: relative !important;
    top: 0 !important;
    left: 0 !important;
    display: inline-block;
  }

  &.skeleton {
    .item {
      opacity: 0.3;
    }
  }

  .item {
    width: calc(33.3% - 16px);
    margin: 8px;
    cursor: pointer;

    &::after {
      content: "";
      display: block;
      width: 100%;
      height: 100%;
      // background-color: rgba(0, 0, 0, 0.1);
      position: absolute;
      top: 0;
      left: 0;
    }

    &:hover::after {
      background-color: rgba(0, 0, 0, 0.3);
      z-index: 3;
    }

    .media-wrapper {
      width: 100%;
      padding-bottom: 100%;
      height: 0;
    }

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
    }

    &.video {
      &.video-lg {
        width: calc(66.6% - 16px);
      }

      video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
      }
    }
  }

  .icon-video, .icon-images {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 24px;
    height: 24px;
    z-index: 2;

    .icon {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  @media (max-width: 767px) {
    .item {
      width: calc(33.3% - 2px);
      margin: 1px;

      &.video {
        &.video-lg {
          width: calc(66.6% - 2px);
        }
      }
    }
    .icon-video, .icon-images {
      top: 8px;
      right: 8px;
      width: 18px;
      height: 18px;
      z-index: 2;
    }
  }

}
</style>
