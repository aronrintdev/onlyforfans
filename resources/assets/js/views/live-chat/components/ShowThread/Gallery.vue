<template>
  <div class="d-flex flex-column h-100">
    <div v-if="showTitle" class="d-flex justify-content-between">
      <div class="h2" v-text="$t('title')" />
      <b-btn variant="link" @click="$emit('close')">
        <fa-icon icon="times" />
      </b-btn>
    </div>
    <div class="scroll">
      <Gallery :items="items" :noItemsMessage="$t('noItems')" class="gallery" />
      <div v-observe-visibility="onLastVisible"> </div>
    </div>
  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/ShowThread/Gallery.vue
 */
import { eventBus } from '@/eventBus'
import Vuex from 'vuex'

import Gallery from '@components/media/Gallery'

export default {
  name: 'MessageThreadGallery',

  components: {
    Gallery,
  },

  props: {
    showTitle: { type: Boolean, default: false },
    threadId: { type: String, default: '' },
  },

  computed: {
    ...Vuex.mapState('messaging', [ 'threads' ]),

    items() {
      return this.galleryItems()(this.threadId)
    },

  },

  data: () => ({
    loading: false,
    lastVisible: false,
  }),

  methods: {
    ...Vuex.mapGetters('messaging', [ 'galleryItems', 'galleryMeta' ]),
    ...Vuex.mapActions('messaging', [ 'getGallery' ]),

    onLastVisible(isVisible) {
      this.lastVisible = isVisible
      if (isVisible && this.isNextPage()) {
        this.nextPage()
      }
    },

    page() {
      return this.galleryMeta()(this.threadId).current_page ? this.galleryMeta()(this.threadId).current_page : 0
    },

    totalPages() {
      return this.galleryMeta()(this.threadId).last_page
    },

    total() {
      return this.galleryMeta()(this.threadId).total
    },

    isNextPage() {
      // Gallery not loaded yet
      if (typeof this.galleryMeta()(this.threadId).length === 'undefined') {
        return true
      }
      // No files in gallery
      if (this.total() === 0) {
        return false
      }
      // Current page is last page
      if (this.page() >= this.totalPages()) {
        return false
      }
    },

    nextPage() {
      if (!this.isNextPage() || this.loading) {
        return
      }
      this.loading = true
      const page = typeof this.page() === 'undefined' ? 0 : this.page()
      if (page > 0 && page > this.totalPages()) {
        return
      }
      this.getGallery({ chatthread: this.threadId, page: page + 1, take: 20 })
        .then((data) => {
          this.$forceCompute('items')
        })
        .catch(error => {
          eventBus.$emit('error', { error, message: this.$t('error') })
        })
        .finally(() => {
          this.loading = false
          this.$nextTick(() => {
            if (this.lastVisible && this.isNextPage) {
              this.nextPage()
            }
          })
        })
    },

  },

  watch: {},

  created() {
    if (this.isNextPage()) {
      this.nextPage()
    }
  },
}
</script>

<style lang="scss" scoped>
.scroll {
  height: 100%;
  width: 100%;
  overflow-y: auto;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "error": "An error has occurred while loading this gallery content, please try again later.",
    "noItems": "No media files in this thread",
    "title": "Chat Thread Gallery"
  }
}
</i18n>
