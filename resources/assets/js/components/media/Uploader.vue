<template>
  <div>
    <h2>Uploader</h2>
    <input type="file" @change="selectFile">
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
    productId: 0,
  }),

  methods: {
    selectFile(event) {
      // `files` is always an array because the file input may be in multiple mode
      this.photo = event.target.files[0];
    }
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
