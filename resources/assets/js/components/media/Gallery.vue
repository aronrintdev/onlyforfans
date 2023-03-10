<template>
  <b-card-group :columns="mediaExists" class="gallery p-3" :style="{ columnCount: columnCount }">
    <div v-if="!mediaExists" class="h-100 w-100 d-flex align-items-center">
      <div v-text="noItems" />
    </div>

    <Thumbnail
      v-for="(item, index) in items"
      :key="item.id"
      :value="item"
      class="item mb-3"
      @click="o => onItemClick({ ...o, index })"
    />
  </b-card-group>
</template>

<script>
/**
 * Gallery view for a list of media files
 * resources/assets/js/components/media/Gallery.vue
 */
import Vue from 'vue'
import Vuex from 'vuex'

import Thumbnail from './Thumbnail'
import AudioPlayer from '@components/audioPlayer'
import VideoPlayer from '@components/videoPlayer'


export default {
  name: 'Gallery',

  components: {
    Thumbnail,
  },

  props: {
    items: { type: Array, default: () => ([])},
    noItemsMessage: { type: String, default: ''}
  },

  computed: {
    ...Vuex.mapState(['screenSize']),

    columnCount() {
      if (this.items.length === 0) {
        return 'none'
      }

      switch(this.screenSize) {
        case 'xs': return 2
        case 'sm': return 2
        case 'md': return 3
        case 'lg': return 4
        case 'xl': return 6
        default: return 3
      }
    },

    mediaExists() {
      return this.items.length > 0
    },

    noItems() {
      if (this.noItemsMessage === '') {
        return this.$t('noItemsMessage')
      }
      return this.noItemsMessage
    },
  },

  data: () => ({
    previewOpen: false,
  }),

  methods: {
    onItemClick({ e, value, index }) {
      this.previewOpen = true
      this.$Pswp.open({
        items: this.items.map(file => {
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
    }
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped>
.gallery {
  & .item {
    transition: all 0.5s ease;
    &:hover {
      transform: scale(1.1);
    }
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
