<template>
  <div>
    <div>
      <h2>Uploader</h2>
      <input type="file" @change="selectMediafile">
    </div>
    <div>
      <button @click="shareStory()">Share Image</button>
    </div>
  </div>
</template>

<script>

import { eventBus } from '@/eventBus'

export default {
  mounted() {
  },
  props: ['username'],

  data: () => ({
    mediafile: null,
  }),

  methods: {
    // https://dev.to/diogoko/file-upload-using-laravel-and-vue-js-the-right-way-1775
    selectMediafile(event) {
      // `files` is always an array because the file input may be in multiple mode
      const mediafile = event.target.files[0];
      this.mediafile = mediafile; // %FIXME DEBUG
      eventBus.$emit('select-mediafile', mediafile );
    },

    async shareStory(event) {
      eventBus.$emit('share-story', 'dummy'); // 'submit'
    },
  }
}
</script>

<style scoped>
</style>
