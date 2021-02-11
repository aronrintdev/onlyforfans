<template>
  <div>
    <div>
      <h2>Uploader</h2>
      <input type="file" @change="selectMediaFile">
    </div>
    <div>
      <button @click="shareStory()">Share Image</button>
    </div>
  </div>
</template>

<script>

import { eventBus } from '../../app';

export default {
  mounted() {
  },
  props: ['username'],

  data: () => ({
    mediaFile: null,
  }),

  methods: {
    // https://dev.to/diogoko/file-upload-using-laravel-and-vue-js-the-right-way-1775
    selectMediaFile(event) {
      // `files` is always an array because the file input may be in multiple mode
      const mediaFile = event.target.files[0];
      this.mediaFile = mediaFile; // %FIXME DEBUG
      eventBus.$emit('select-mediaFile', mediaFile );
    },

    async shareStory(event) {
      eventBus.$emit('share-story', 'dummy'); // 'submit'
    },
  }
}
</script>

<style scoped>
</style>
