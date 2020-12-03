<template>
  <div>
    <div>
      <h2>Uploader</h2>
      <input type="file" @change="selectFile">
    </div>
    <div>
      <button @click="shareToStory()">Share Image</button>
    </div>
  </div>
</template>

<script>

export default {
  mounted() {
    console.log('Uploader component mounted.')
  },
  props: ['username'],

  data: () => ({
    photo: null,
    description: '',
  }),

  methods: {
    // https://dev.to/diogoko/file-upload-using-laravel-and-vue-js-the-right-way-1775
    selectFile(event) {
      // `files` is always an array because the file input may be in multiple mode
      this.photo = event.target.files[0];
    },
    shareToStory(event) {
      this.$emit('share-to-story', {
        photo: this.photo,
      });
    },
    /*
    async shareToStory(color) {
      console.log(`Setting color: ${color}`);
      const url = `/${this.username}/stories/store`;
      const payload = {
        stype: 'text',
        bgcolor: this.color,
        content: this.contents,
      };
      const response = await axios.post(url, payload);
      console.log('shareToStory()', {response});
    },
    setColor(color) {
      console.log(`Setting color: ${color}`);
      this.color = color;
    },
     */
  }
}
</script>

<style scoped>
</style>
