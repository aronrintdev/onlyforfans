<template>
  <b-card-group columns class="gallery p-3" :style="{ columnCount: columnCount, }">
    <div v-if="items.length === 0" class="h-100 w-100 d-flex align-items-center justify-content-around">
      <div v-text="noItems" />
    </div>

    <Thumbnail v-for="item in items" :key="item.id" :value="item" class="item mb-3" @click="onItemClick" />
  </b-card-group>
</template>

<script>
/**
 * Gallery view for a list of media files
 * resources/assets/js/components/media/Gallery.vue
 */
import Vuex from 'vuex'

import Thumbnail from './Thumbnail'

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
      switch(this.screenSize) {
        case 'xs': return 2
        case 'sm': return 2
        case 'md': return 3
        case 'lg': return 4
        case 'xl': return 6
        default: return 3
      }
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
    onItemClick({ e, value }) {
      this.previewOpen = true
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
