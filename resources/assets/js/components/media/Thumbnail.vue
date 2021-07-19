<template>
  <div class="cursor-pointer" @click="onClick">
    <img
      v-if="value.is_image"
      v-preview:scopeA
      :src="value.filepath"
      class="img-thumbnail rounded"
    />
  </div>
</template>

<script>
/**
 * Thumbnail Display for a media file
 * resources/assets/js/components/media/Thumbnail.vue
 */
import Vuex from 'vuex'

import PhotoSwipe from 'photoswipe/dist/photoswipe'
import PhotoSwipeUI from 'photoswipe/dist/photoswipe-ui-default'
import createPreviewDirective from 'vue-photoswipe-directive'

export default {
  name: 'Thumbnail',

  components: {},

    directives: {
    preview: createPreviewDirective({
      showAnimationDuration: 0,
      bgOpacity: 0.75
    }, PhotoSwipe, PhotoSwipeUI)
  },

  props: {
    value: { type: Object, default: () => ({})},
  },

  computed: {},

  data: () => ({}),

  methods: {
    onClick(e) {
      this.$emit('click', { e, value: this.value })
    }
  },

  watch: {},

  created() {},
}
</script>

<style lang="scss" scoped></style>

<i18n lang="json5" scoped>
{
  "en": {}
}
</i18n>
